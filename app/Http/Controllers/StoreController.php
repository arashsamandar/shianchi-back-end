<?php

namespace App\Http\Controllers;

use App\Category;
use App\DepartmentStore;
use App\Http\Requests\store\InsertStoreRequest;
use App\Http\Requests\store\updateStorePasswordRequest;
use App\Http\Requests\store\UpdateStorePhoneRequest;
use App\Http\Requests\store\UpdateStoreWegoExpirationRequest;
use App\Product;
use App\Store;
use App\StoreTelegramId;
use App\User;
use Dingo\Api\Routing\Helpers;
use Elasticsearch\ClientBuilder;
use Faker\Factory;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon;
use Wego\PictureHandler;
use Wego\Helpers\JsonUtil;
use Wego\Store\StoreEditor;
use Wego\Store\StoreFactory;
use Wego\StoreViewCount;
use Wego\UserHandle\UserPermission;
use Symfony\Component\HttpFoundation\Response as IlluminateResponse;

class StoreController extends ApiController
{
    use Helpers;
    const PAGINATE_NUMBER = 15;

    protected $storeFactory;

    public function __construct(StoreFactory $storeFactory)
    {
        $this->storeFactory = $storeFactory;
    }


    /**
     * Store a newly created store in storage.
     *
     * @param InsertStoreRequest $request
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\NotStaffException
     */
    public function store(InsertStoreRequest $request)
    {
        $staff = $this->auth->user();
        $this->storeFactory->save($request, $staff);
        return $this->respondOk('successfully created', 'message');
    }

    /**
     * get the current singed in store categories
     *
     * @param Request $request
     * @return mixed
     */
    public function getCategories(Request $request)
    {
        if ($request->has('store_id'))
            $storeId = Store::findOrFail($request->input('store_id'))->id;
        else
            $storeId = UserPermission::checkStorePermission()->userable_id;

        $storeCategories = $this->getStoreCategoriesFromDB($storeId);
        return $this->respondArray($this->prettifyCategoriesForStoreResult($storeCategories));
    }

    private function getStoreCategoriesFromDB($storeId, $categoriesField = ['name', 'persian_name' , 'unit' , 'isLeaf' ,'categories.id','english_path','path'])
    {
        $storeCategories = Store::where('id', $storeId)
            ->with(['categories' => function ($query) use ($categoriesField) {
                $query->select($categoriesField);
            }])->select(['id'])->get()->toArray();

        if (empty($storeCategories))
            throw new NotFoundHttpException;
        return $storeCategories;

    }

    private function prettifyCategoriesForStoreResult($storeCategories)
    {
        $removed = JsonUtil::removeFields($storeCategories[0], ['categories.*.pivot.store_id'])['categories'];
        foreach ($removed as $key => $value) {
            $removed[$key]['id'] = $removed[$key]['pivot']['category_id'];
            unset($removed[$key]['pivot']);
        }

        return $removed;
    }


    /**
     * delete the picture of the store
     *
     * @param Request $request
     * @return mixed
     */
    public function deletePicture(Request $request)
    {
        $path = $request->input('path');
        if ($this->isTempFile($path)) {
            UserPermission::checkOrOfPermissions([UserPermission::STAFF, UserPermission::STORE]);
            return $this->respondOk((new PictureHandler())->deleteTempPicture($path));
        } else {
            $user = UserPermission::checkStorePermission();
            $store = $user->getUserableType();
            return $this->pictureOwnerRecognizer($request, $store);
        }
    }

    /**
     * @param $request
     * @param $store
     * @return mixed
     */
    public function pictureOwnerRecognizer($request, $store)
    {
        $path = $request->input('path');
        if ($this->isManagerPicture($store, $path))
            $this->deleteManagerPicture($request);
        elseif ($this->isDepartmentPicture($store, $path))
            (new StoreEditor())->deleteDepartmentManagerPicture($request);
        else
            (new PictureHandler())->deletePicture($path, $store);
        Store::where('id', $store->id)->elastic()->addToIndex();
        return $this->setStatusCode(200)->respondOk("file deleted");
    }

    /**
     * @param $path
     * @return bool
     */
    private function isTempFile($path)
    {
        return (strpos($path, "temp") !== false);
    }

    /**
     * @param $store
     * @param $path
     * @return bool
     */
    private function isDepartmentPicture($store, $path)
    {
        $departmentStore = DepartmentStore::where('store_id', '=', $store->id)
            ->where('department_manager_picture', '=', $path)->first();
        return ($departmentStore !== null);
    }

    private function isManagerPicture($store, $path)
    {
        return (!strcmp($path, $store->manager_picture));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getProfileJson(Request $request)
    {
        $user = $this->auth->user();
        $storeId = $request->has('store_id') ? $request->input('store_id') : $user->userable_id;
        $storeEditor = new StoreEditor();
        $result = $storeEditor->pruneProfileJson($storeEditor->getJsonById($storeId));
        return $result;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPageJson(Request $request)
    {
        $url = $request->input('store_url');
        $store = Store::where('url', '=', $url)->first();
        StoreViewCount::addIpToStoreViewIps($request->ip(), $store->id);
        $storeEditor = new StoreEditor();
        return $storeEditor->pruneProfileJson($storeEditor->getJsonByUrl($url), ['shaba_number', 'account_number', 'card_number', 'card_owner_name']);
    }

    public function getSearchSummaryJson(Request $request)
    {
        $storeId = $request->input('store_id');
        $storeEditor = new StoreEditor();
        $result = $storeEditor->includeItemsJson($storeEditor->getJsonById($storeId), [
            'url', 'pictures', 'information', 'persian_name'
        ]);
        return $result;
    }

    /**
     * @param UpdateStorePhoneRequest $request
     * @return mixed
     */
    public function update(UpdateStorePhoneRequest $request)
    {
        (new StoreEditor())->update($request->toArray());
        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondOk('Store Updated');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateDescription(Request $request)
    {
        if ((new StoreEditor())->updateDescription($request->toArray()))
            return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondOk('description is updated');
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError("something happened");
    }

    /**
     * @param UpdateStorePhoneRequest $request
     * @return mixed
     */
    public function updateDepartments(UpdateStorePhoneRequest $request)
    {
        if ((new StoreEditor())->updateDepartments($request->toArray()))
            return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondOk("departments are updated");
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError("something happened");
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updatePictures(Request $request)
    {
        if ((new StoreEditor())->updatePictures($request->toArray()))
            return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondOk("pictures are updated");
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError("something happened");
    }

    public function storeTelegramUsername(Request $request)
    {
        $user = UserPermission::checkStorePermission();
        $username = $request->input('username');
        $attributes = ['store_id'=>$user->userable_id,'telegram_username'=>$username];
        StoreTelegramId::create($attributes);
        return $this->respondOk();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateGuarantee(Request $request)
    {
        if ((new StoreEditor())->updateGuarantee($request->toArray()))
            return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondOk("guaranties are updated");
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError("something happened");
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateWorkTime(Request $request)
    {
        if ((new StoreEditor())->updateWorkTime($request->toArray()))
            return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondOk("work hours are updated");
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError("something happened");
    }

    /**
     * @param updateStorePasswordRequest $request
     * @return mixed
     */
    public function updatePassword(updateStorePasswordRequest $request)
    {
        $user = $this->auth->user();
        return (new StoreEditor())->updatePassword($request->toArray(), $user);
    }

    /**
     *
     */
    public function deleteManagerPicture(Request $request)
    {
        $user = $this->auth->user();
        $store = Store::find($user->userable_id);
        (new StoreEditor())->deleteManagerPicture($store);
    }

    /**
     * @param UpdateStoreWegoExpirationRequest $request
     * @return mixed
     */
    public function updateWegoCoinExpiration(UpdateStoreWegoExpirationRequest $request)
    {
        if ((new StoreEditor())->updateWegoCoinExpiration($request->toArray()))
            return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondOk("wego expiration is changed");
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError("something happened");
    }

    public function searchStore(Request $request)
    {
        $name = $request->input('name');
        $name = '%' . $name . '%';
        $store = Store::join('users',function($join){
            $join->on('users.userable_id', '=', 'stores.id')
                ->where('users.userable_type', UserPermission::STORE);
        })->where('stores.english_name','like',$name)
                ->orWhere('users.name','like',$name)->select('users.name','stores.english_name','stores.id')->get();
        return $store->toArray();
    }

    public function findNearbyStores(Request $request)
    {
        $distance = $request->input('distance');
        $lat = $request->input('lat');
        $lon = $request->input('lon');
        $params = [
            'index' => 'wego_1',
            'type' => 'stores',
            'body' => [
                'query' => [
                    "filtered" => [
                        "query" => [
                            "match_all" => []
                        ],
                        "filter" => [
                            "geo_distance" => [
                                "distance" => $distance,
                                "location" => [
                                    "lat" => $lat,
                                    "lon" => $lon

                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
        $client = ClientBuilder::create()->build();
        return $client->search($params);

    }

    public function findStoresInBounding(Request $request)
    {
        $topLeft = $request->input('top_left');
        $bottomRight = $request->input('bottom_right');
        $topLeft_lat = $topLeft['lat'];
        $topLeft_lon = $topLeft['lon'];
        $bottomRight_lat = $bottomRight['lat'];
        $bottomRight_lon = $bottomRight['lon'];
        $params = [
            'index' => 'wego_1',
            'type' => 'stores',
            'body' => [
                'query' => [
                    "filtered" => [
                        "query" => [
                            "match_all" => []
                        ],
                        "filter" => [
                            "geo_bounding_box" => [
                                "location" => [
                                    "top_left" => [
                                        'lat' => $topLeft_lat,
                                        'lon' => $topLeft_lon
                                    ],
                                    "bottom_right" => [
                                        'lat' => $bottomRight_lat,
                                        'lon' => $bottomRight_lon
                                    ]

                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
        $client = ClientBuilder::create()->build();
        return $client->search($params);


    }

    public function insertTestingStores()
    {
        $exampleStore= [
            "persian_name"=> "کالای بهداشتی امیر",
            "english_name"=> "amir health store",
            "email"=>"",
            "password"=> "123",
            "about_us"=> "درباره ما",
            "password_confirmation"=> "123",
            "business_license"=> "125151561",
            "province_id"=> "3",
            "city_id"=> "1439",
            "bazaar"=> "1",
            "address"=> "asdfasdfasdfsadfasdf asdfasdf asdfasdf",
            "shaba_number"=> "165165165165",
            "fax_number"=> "0212121221",
            "information"=> "اطلاعات",
            "account_number"=> "15165165165",
            "card_number"=> "1651-6516-6515-5615",
            "card_owner_name"=> "ahmad illoo",
            "manager_first_name"=> "علی",
            "manager_last_name"=> "حسینی",
            "manager_national_code"=> "1111111111",
            "wego_expiration"=> 3650,
            "location"=>[
                "lat"=> 35.90443496731149,
                "long"=> 51.32636522143548,
            ],
            "manager_mobile"=> [
                [
                    "prefix_phone_number"=> "0222",
                    "phone_number"=> "2555555",
                    "id"=> 0
                ]
            ],
            "departments"=> [
                [
                    "department_prefix_phone_number" => "021",
                    "department_phone_number" => "21212211",
                    "department_email" => "ali@gmail.com",
                    "department_manager_first_name" => "علی",
                    "department_manager_last_name" => "حسینی",
                    "department_manager_picture" => "#",
                    "department_id" => "2"
                ],
            ],
            "phone"=> [
                [
                    "prefix_phone_number"=> "651",
                    "phone_number"=> "56651651",
                    "id"=> 0
                ]
            ],
            "manager_picture"=> "$",
            "work_time"=> [
                [
                    "day"=> "شنبه",
                    "opening_time"=> 8,
                    "closing_time"=> 17
                ],
                [
                    "day"=> "یکشنبه",
                    "opening_time"=> 8,
                    "closing_time"=> 17
                ],
                [
                    "day"=> "دوشنبه",
                    "opening_time"=> 8,
                    "closing_time"=> 17
                ],
                [
                    "day"=> "سه شنبه",
                    "opening_time"=> 8,
                    "closing_time"=> 17
                ],
                [
                    "day"=> "چهارشنبه",
                    "opening_time"=> 8,
                    "closing_time"=> 17
                ],
                [
                    "day"=> "پنج شنبه",
                    "opening_time"=> 8,
                    "closing_time"=> 17
                ],
                [
                    "day"=> "جمعه",
                    "opening_time"=> 8,
                    "closing_time"=> 17
                ]
            ],
            "pictures"=> [
                [
                    "type"=> "inside",
                    "path"=> ""
                ],
                [
                    "type"=> "cover",
                    "path"=> ""
                ],
                [
                    "type"=> "thumbnail",
                    "path"=> ""
                ]
            ]
        ];
        $request = [];
        for ($i=1;$i<2;$i++){
            $faker=Factory::create();
            $exampleStore['email'] = 'amirbehdashti@store.com';
            $exampleStore['password'] = '9876543210';
            $exampleStore['address'] = $faker->address;
            $request[] = $exampleStore;
        }
        $storeFactory =new StoreFactory();
        $staff = User::where('email','staff@1.com')->first();
        foreach($request as $store){
            $req= Request::create(null,null,$store);
            $storeFactory->save($req,$staff);
        }
        return $request;
    }

    public function checkStoreEditingValidation(Request $request)
    {
        $user = $this->auth->user();
        if (Product::where('id',$request->product_id)->where('store_id',$user->userable_id)->exists()){
            return [
                'isValid' => true
            ];
        } else {
            return [
                'isValid' => false
            ];
        }
    }

    public function addStoreTelegramChannel(Request $request)
    {
        $channelId = $request->input('channel');
        $storeId = $request->input('store_id');
        Store::find($storeId)->update(['telegram_channel_id'=>$channelId]);
        return $this->respondOk();
    }


    public function copyProductsToAnotherStore(Request $request)
    {
        $productIds = $request->product_ids;
        $store_id = $request->store_id;
        $products = Product::whereIn('id', $productIds)->get();
        $newIds = [];
        foreach ($products as $product) {
            $copyProduct = $product->replicate();
            $copyProduct->store_id = $store_id;
            $copyProduct->save();
            $newIds[] = $copyProduct->id;
            foreach ($product->pictures as $picture){
                $copyProduct->pictures()->save($picture);
            }
            foreach ($product->colors as $color){
                $copyProduct->colors()->attach($color);
            }
            foreach ($product->values as $value){
                $copyProduct->values()->attach($value);
            }
            $copyProduct->push();
        }
        Product::whereIn('id',$newIds)->elastic()->get()->addToIndex();
        return $newIds;
    }
    public function pictureReplicating(Request $request){
        $productIds = $request->product_ids;
        $store_id = $request->store_id;
        $products = Product::whereIn('id', $productIds)->get();
        foreach ($products as $product) {
            $copiedProduct = Product::where('english_name',$product->english_name)->where('store_id',$store_id)->first();
            foreach($copiedProduct->pictures as $picture){
                $copyPicture = $picture->replicate();
                $copyPicture->product_id = $product->id;
                $copyPicture->save();
            }
        }
        Product::whereIn('id', $productIds)->elastic()->get()->addToIndex();
        return $this->respondOk();
    }

    public function changeStoreProductsPrice(Request $request)
    {
        $factor = $request->factor;
        $factor = (1+($factor/100));
        if(!empty($request->category_id) && !empty($request->brand_id)){
            $products = Product::where('category_id', $request->category_id)
                ->where('brand_id', $request->brand_id)->get();
        } elseif(!empty($request->category_id)) {
            $products = Product::where('category_id', $request->category_id)->get();
        } elseif(!empty($request->brand_id)) {
            $products = Product::where('brand_id', $request->brand_id)->get();
        } else {
            $products = Product::where('store_id', $request->store_id)->get();
        }
        foreach($products as $product){
            foreach($product->product_details as $detail) {
                $detail->current_price = $detail->current_price * $factor;
                $detail->current_price = ceil($detail->current_price / 1000) * 1000;
                $detail->save();
            }
            Product::where('id', $product->id)->elastic()->addToIndex();
        }
        return $this->respondOk();
    }

    public function changeStoreProductsPriceByCatName(Request $request)
    {
        $factor = $request->factor;
        $factor = (1+($factor/100));
        $categoryIds = Category::where('english_path','like','%'.strtoupper($request->category).'%')
            ->where('isLeaf',1)->pluck('id')->toArray();
        if ($request->has('brand_id')) {
            $products = Product::whereIn('category_id',$categoryIds)
                ->where('brand_id',$request->brand_id)->get();
        } else {
            $products = Product::whereIn('category_id',$categoryIds)->get();
        }
        foreach($products as $product){
            foreach($product->product_details as $detail) {
                $detail->current_price = $detail->current_price * $factor;
                $detail->current_price = ceil($detail->current_price / 1000) * 1000;
                $detail->save();
            }
        }
        if ($request->has('brand_id')) {
            Product::whereIn('category_id',$categoryIds)
                ->where('brand_id',$request->brand_id)->elastic()->addToIndex();
        } else {
            Product::whereIn('category_id',$categoryIds)->elastic()->addToIndex();
        }
        return $this->respondOk();
    }

    public function changeStoreProductsPriceByBrandAndCat(Request $request)
    {
        $storeId = $request->store_id;
        $factor = $request->factor;
        $categoryId = $request->category_id;
        $brandId = $request->brand_id;
        $factor = (1+($factor/100));
        $products = Product::where('store_id',$storeId)->where('category_id',$categoryId)
            ->where('brand_id',$brandId)->get();
        foreach($products as $product){
            $product->current_price = $product->current_price * $factor;
            $product->current_price = ceil($product->current_price/1000)*1000;
            $product->save();
        }
        Product::where('store_id',$storeId)->where('category_id',$categoryId)
            ->where('brand_id',$brandId)->elastic()->get()->addToIndex();;
        return $this->respondOk();
    }

    public function changeStoreProductsPriceByCategory(Request $request)
    {
        $storeId = $request->store_id;
        $categoryId = $request->category_id;
        $products = Product::where('store_id',$storeId)->where('category_id',$categoryId)->get();
        foreach($products as $product){
            $product->current_price = $product->current_price + 400000;
            $product->save();
        }
        Product::where('store_id',$storeId)->where('category_id',$categoryId)->elastic()->get()->addToIndex();
    }


    public function deleteStore($id)
    {
        $store = Store::find($id);
        $store->delete();
        $client = ClientBuilder::create()->build();
        $param = [
            'type' => 'stores', 'index' => 'wego_1',
            'body' => ['query' => ['term' => ['_id' => $id]]]
        ];
        $client->deleteByQuery($param);
        return $this->respondOk();
    }

    public function addCategoryToStore(Request $request)
    {
        $storeId= $request->store_id;
        $categoryId= $request->category_id;
        $store = Store::find($storeId);
        $store->categories()->sync([$categoryId], false);
        return $this->respondOk();
    }

    public function addCategoriesToStore()
    {
        $storeId = request()->store_id;
        $store = Store::find($storeId);
        $categoryIds = request()->categoryIds;
        if (!is_array($categoryIds)){
            $categoryIds = Category::where('english_path','like',$categoryIds."%")->pluck('id')->toArray();
        }
        $brandIds = request()->brandIds;
        $store->categories()->sync($categoryIds, false);
        $store->brands()->sync($brandIds, false);
        return $this->respondOk();
    }

    public function getStoreCategoryBrand()
    {
        $user = $this->auth->user();
        $storeId = request()->store_id;
//        $store = Store::find($storeId);
        $store = $user->userable;
        $storeBrands = $store->brands->pluck('id')->toArray();
        $storeCats = $store->categories->pluck('id')->toArray();
        $result = [];
        $result['category'] = $storeCats;
        $result['brands'] = $storeBrands;
        return $result;
    }

    public function sendNotifTest()
    {
        $reg_key = request()->reg_key;
        $user = $this->auth->user();
        $store = $user->userable;
        $store->telegram_channel_id = $reg_key;
        $store->save();

    }
    function sendNotification($device_ids_array,$title,$message)
    {
        $json_data = array();
        $json_data["registration_ids"] = $device_ids_array;
        $json_data["notification"] = array();
        $json_data["notification"]["body"] = $message;
        $json_data["notification"]["title"] = $title;
        $json_data["notification"]["sound"] = "default";

        $data = json_encode($json_data);
        //FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key = 'AAAANapZAkk:APA91bGQDlVtD_7LNhmdpz_-a1HjAJCoixDNi7pzNlJizv2E_KRUfURWN6f_UdbKtjQNXmLS4I7JwyqK4hjXHmv1NZgTS2b35F7A5FdV3dvF6xxetZeLtyvNeoMbZxzoVynCmkzACmFh';
        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$server_key
        );
        //CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        if ($result === FALSE)
        {
            dd('Oops! FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
    }
}
