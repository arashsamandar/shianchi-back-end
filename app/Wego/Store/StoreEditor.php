<?php
namespace Wego\Store;

use App\Bazaar;
use App\Events\StoreEdited;
use App\Exceptions\NotPermittedException;
use Symfony\Component\Finder\Exception\AccessDeniedException;
//use Symfony\Component\HttpFoundation\Request;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wego\PictureHandler;
use App\DepartmentStore;
use App\Exceptions\NullException;
use App\Http\Controllers\ApiController;
use App\Store;
use App\StorePhone;
use App\WorkTime;
use Illuminate\Support\Facades\DB;
use Wego\Helpers\JsonUtil;
use Wego\Search\FindById;
use Wego\Search\TermQuery;

class StoreEditor extends ApiController
{
    const GENERAL_UPDATE_ATTRIBUTE = "generalUpdateAttribute";
    protected $store;
    private $jsonFilters = [
        'manager_national_code', 'updated_at', 'pictures.id', 'pictures.store_id',
        'pictures.created_at', 'pictures.updated_at', 'phones.*.id', 'phones.*.store_id',
        'phones.*.created_at', 'phones.*.updated_at', 'user.id', 'user.email', 'user.userable_id', 'user.userable_type',
        'departments.*.id', 'departments.*.created_at', 'departments.*.updated_at', 'departments.*.pivot', 'manager_mobiles.*.id',
        'manager_mobiles.*.prefix_number', 'manager_mobiles.*.store_id', 'manager_mobiles.*.create_at', 'manager_mobiles.*.updated_at'
    ];

    protected $validationRule = [
        'fax_number' => 'numeric|max:12',
        'prefix_phone_number' => 'numeric|max:4',
        'phone_number' => 'numeric|max:8'
    ];
    protected $storeDescriptionFunctMap = [
        "information" => self::GENERAL_UPDATE_ATTRIBUTE,
        "about_us" => self::GENERAL_UPDATE_ATTRIBUTE,
        "persian_name" => 'updateStoreName'
    ];
    protected $storeAttrFuncMap = [
        "shaba_number" => self::GENERAL_UPDATE_ATTRIBUTE,
        "fax_number" => self::GENERAL_UPDATE_ATTRIBUTE,
        //"manager_picture"=>'updateManagerPicture'
        "account_number" => self::GENERAL_UPDATE_ATTRIBUTE,
        "card_number" => self::GENERAL_UPDATE_ATTRIBUTE,
        "card_owner_name" => self::GENERAL_UPDATE_ATTRIBUTE,
        "english_name" => self::GENERAL_UPDATE_ATTRIBUTE,
        "bazaar" => self::GENERAL_UPDATE_ATTRIBUTE
    ];

    protected $departmentAttrFunctMap = [
        "department_manager_first_name" => self::GENERAL_UPDATE_ATTRIBUTE,
        "department_prefix_phone_number" => self::GENERAL_UPDATE_ATTRIBUTE,
        "department_phone_number" => self::GENERAL_UPDATE_ATTRIBUTE,
        "department_manager_last_name" => self::GENERAL_UPDATE_ATTRIBUTE,
        "department_email" => self::GENERAL_UPDATE_ATTRIBUTE,
        "department_manager_picture" => 'updateDepartmentManagerPicture',
    ];

    protected $storePhoneAttrFuncMap = [
        "prefix_phone_number" => self::GENERAL_UPDATE_ATTRIBUTE,
        "phone_number" => self::GENERAL_UPDATE_ATTRIBUTE,
    ];

    protected $storeWegoAttrFuncMap = [
        "wego_expiration" => self::GENERAL_UPDATE_ATTRIBUTE,
    ];

    public function getJsonById($id)
    {
        $store = (new FindById())->setType('stores')->setSearchParameter($id)->search();
        if ($this->isSearchResultEmpty($store))
            return [];
        $store = $store['hits']['hits'][0]['_source'];
        return $this->getJson($store);

    }

    public function getJsonByUrl($url)
    {
        $store = (new TermQuery)
            ->setField('url')
            ->setType('stores')
            ->setSearchParameter($url)
            ->search();

        if ($this->isSearchResultEmpty($store))
            throw new NotFoundHttpException;
        $store = $store['hits']['hits'][0]['_source'];
        //(new StoreViewCountController())->addIpToStoreViewIps($_SERVER['REMOTE_ADDR'],$store['id']);
        return $this->getJson($store);
    }

    private function isSearchResultEmpty($elasticSearchResult)
    {
        return $elasticSearchResult['hits']['total'] == 0;
    }

    private function getJson($store)
    {
        $storePictures = $this->changeStorePicturesStyle($store['pictures']);
        $store = JsonUtil::convertKeys($store, [
            'phones' => 'store_phones',
            'pictures' => 'pictures',
        ]);
        $store['persian_name'] = $store['user']['name'];
        $store['email'] = $store['user']['email'];
        unset($store['user']);
        unset($store['categories']);
        $store['pictures'] = $storePictures;
        $store['bazaar_name'] = Bazaar::where('id', '=', $store['bazaar'])->first()->name;
        return $store;
    }

    public function changeStorePicturesStyle($storePictures)
    {
        $result = [];
        foreach ($storePictures as $picture) {
            $result [] = [
                'type' => $picture['type'],
                'path' => $picture['path'],
            ];
        }
        return $result;
    }

    public function pruneProfileJson($json, $extraFilters = [])
    {
        $json['departments'] = $this->changeDepartmentsStyle($json);
        $json = JsonUtil::removeFields($json, array_merge([], $this->jsonFilters, $extraFilters));
        return $json;
    }

    public function changeDepartmentsStyle($json)
    {
        $departments = $json['departments'];
        foreach ($departments as &$department) {
            $department["department_id"] = $department['pivot']['department_id'];
            $department["department_manager_first_name"] = $department['pivot']["department_manager_first_name"];
            $department["department_prefix_phone_number"] = $department['pivot']["department_prefix_phone_number"];
            $department["department_phone_number"] = $department['pivot']["department_phone_number"];
            $department["department_manager_last_name"] = $department['pivot']["department_manager_last_name"];
            $department["department_email"] = $department['pivot']["department_email"];
            $department["department_manager_picture"] = $department['pivot']["department_manager_picture"];
        }
        return $departments;
    }

    public function updateWegoCoinExpiration($updatedInfo)
    {
        $store = $this->getStore();
        $this->generalCheckAttribute($store, $this->storeWegoAttrFuncMap, $updatedInfo);
        $store->save();
        $this->updateElasticSearch($store);

        return true;
    }

    public function updateDescription($updatedInfo)
    {
        $store = $this->getStore();
        $this->generalCheckAttribute($store, $this->storeDescriptionFunctMap, $updatedInfo);
        $store->save();
        $this->updateElasticSearch($store);

        return true;
    }


    public function update($updatedInfo)
    {
//        $user = JWTAuth::parsetoken()->authenticate();
        $store = Store::find($updatedInfo['id']);
        $this->store = $store;
        $this->updateStore($updatedInfo, $store);
        $this->updateDescription($updatedInfo);
        //$this->updateDepartments($updatedInfo);
        $this->updatePassword($updatedInfo, $store->user);
        //$this->updateWorkTime($updatedInfo);
        $this->updateStorePhones($updatedInfo, $store);
        $store->save();

        $this->updateElasticSearch($store);
        event(new StoreEdited($store));
    }

    private function updateElasticSearch(Store $store)
    {
        $store->elastic()->addToIndex();
    }

    private function updateStore($updatedInfo, &$store)
    {
        foreach ($this->storeAttrFuncMap as $attrName => $function) {
            if (strcmp($store->{$attrName}, $updatedInfo[$attrName])) {
                $this->$function($store, $attrName, $updatedInfo[$attrName]);
            }
        }
    }

    private function getStore()
    {
        return $this->store;
    }

    private function generalCheckAttribute(&$store, $attrArray, $updatedInfo)
    {
        foreach ($attrArray as $attrName => $function) {
            if (strcmp($store->{$attrName}, $updatedInfo[$attrName])) {
                $this->$function($store, $attrName, $updatedInfo[$attrName]);
            }
        }
    }

    public function includeItemsJson($json, $includeItems = [])
    {
        $result = [];
        foreach ($includeItems as $key => $item) {
            $result[$item] = $json[$item];
        }
        return $result;

    }

    private function generalUpdateAttribute($object, $attrName, $newValue)
    {
        $object->{$attrName} = $newValue;
    }

    private function updateManagerPicture($store, $attrName, $tempPicPath)
    {
        if (strcmp($store->manager_picture, $tempPicPath)) {
            $savedPath = (new PictureHandler())->moveAllPictures($tempPicPath, $store, "Store");
            $this->generalUpdateAttribute($store, $attrName, $savedPath);
            $this->updateElasticSearch($store);
        }
    }


    public function updatePictures($updatedInfo)
    {
        $store = $this->getStore();
        $user = $store->user;
        $updatedPictures = $updatedInfo['pictures'];
        $newPics = $this->findNewPictures($updatedPictures);
        if (!empty($newPics)) {
            (new PictureHandler())->storePicture($newPics, $store, $user);
            $this->updateElasticSearch($store);
        }
        return true;
    }

    private function findNewPictures($pictures)
    {
        $newPics = [];
        foreach ($pictures as $picture) {
            if (strpos($picture['path'], 'temp') !== false)
                $newPics[] = $picture;
        }
        return $newPics;
    }

    private function findPictureByType($pictures, $type)
    {
        foreach ($pictures as $picture) {
            if (!strcmp($picture['type'], $type))
                return $picture->path;
        }
        return null;
    }

    private function updateStorePhones($updatedInfo, $store)
    {
        $oldPhones = StorePhone::where('store_id', $store->id)->orderBy('type', "asc")->get();
        $newPhones = $updatedInfo['phone'];
        $newPhonesCommand = [];
        for ($i = 0; $i < min(max(count($oldPhones), count($newPhones)), 10); $i++) {
            $typeNum = $i + 1;
            if ($i < count($oldPhones) && $i < count($newPhones)) {
                //update = jofteshoon phone ba in index daran
                $isChanged = false;
                foreach ($this->storePhoneAttrFuncMap as $attrName => $function) {
                    if (strcmp($oldPhones[$i]->{$attrName}, $newPhones[$i][$attrName])) {
                        $this->$function($oldPhones[$i], $attrName, $newPhones[$i][$attrName]);
                        $isChanged = true;
                    }
                }
                if ($isChanged == true) {
                    $oldPhones[$i]['type'] = $typeNum;
                    $oldPhones[$i]->save();
                }
            } elseif ($i >= count($oldPhones) && $i < count($newPhones)) {
                //create = tedade old kamtare;
                $newPhonesCommand [] = new StorePhone([
                    'type' => $typeNum,
                    'prefix_phone_number' => $newPhones[$i]['prefix_phone_number'],
                    'phone_number' => $newPhones[$i]['phone_number'],
                ]);
            } elseif ($i < count($oldPhones) && $i >= count($newPhones)) {
                //delete = delete kardan shomarehaie ghadimie old
                $oldPhones[$i]->delete();
            }
        }
        $store->phones()->saveMany($newPhonesCommand);
        $this->updateElasticSearch($store);

    }

    public function updateGuarantee($updatedInfo)
    {
        $store = $this->getStore();
        $oldGuarantees = DB::table('guarantee_store')->where('store_id', '=', $store->id)->get();
        $newGuarantees = $updatedInfo['guaranties'];
        $gArray = null;
        foreach ($oldGuarantees as $oldGuarantee) {
            $newGuarantee = $this->findGuarantyByType($newGuarantees, $oldGuarantee->guarantee_id);
            if ($newGuarantee == null)
                DB::table('guarantee_store')->where('id', '=', $oldGuarantee->id)->delete();
        }
        foreach ($newGuarantees as $newGuarantee) {
            $oldGuarantee = $this->findGuarantyById($oldGuarantees, $newGuarantee['type']);
            if ($oldGuarantee == null) {
                //create
                if (strcmp($newGuarantee['day'], '0')) {
                    $gArray[$newGuarantee['type']] = ["expiration_time" => ($newGuarantee["day"])];
                }
            } elseif (strcmp($oldGuarantee->expiration_time, $newGuarantee['day'])) {
                DB::table('guarantee_store')
                    ->where('id', '=', $oldGuarantee->id)->update(['expiration_time' => $newGuarantee['day']]);
            }
        }
        $store->guarantees()->attach($gArray);

        $this->updateElasticSearch($store);

        return true;
    }

    private function findGuarantyById($array, $id)
    {
        foreach ($array as $element) {
            if (!strcmp($element->guarantee_id, $id))
                return $element;
        }
        return null;
    }

    private function findGuarantyByType($array, $type)
    {
        foreach ($array as $element) {
            if (!strcmp($element['type'], $type))
                return $element;
        }
        return null;
    }

    public function updateWorkTime($updatedInfo)
    {
        $store = $this->getStore();
        $newWorkTimes = $updatedInfo['work_time'];
        $oldWorkTimes = WorkTime::where('store_id', '=', $store->id)->get();
        foreach ($newWorkTimes as $newWorkTime) {
            $oldWorkTime = $this->findDayByDay($oldWorkTimes, $newWorkTime['day']);
            if ($this->isWorkTimeIsNew($newWorkTime, $oldWorkTime)) {
                $this->updateWorkTimeDB($newWorkTime, $oldWorkTime);
            }
        }
        $this->updateElasticSearch($store);

        return true;
    }

    private function isWorkTimeIsNew($newWorkTime, $oldWorkTime)
    {
        if (is_null($oldWorkTime)) {
            return true;
        }
        return
            strcmp($oldWorkTime->opening_time, $newWorkTime['opening_time']) ||
            strcmp($oldWorkTime->closing_time, $newWorkTime['closing_time']) ||
            strcmp($oldWorkTime->is_closed, $newWorkTime['is_closed']);
    }

    private function findDayByDay($oldWorkTimes, $day)
    {
        foreach ($oldWorkTimes as $oldWorkTime) {
            if (!strcmp($oldWorkTime->day, $day))
                return $oldWorkTime;
        }
        return null;
    }

    private function findDayByDayInArray($newWorkTimes, $day)
    {
        foreach ($newWorkTimes as $newWorkTime) {
            if (!strcmp($newWorkTime['day'], $day))
                return $newWorkTime;
        }
        return null;
    }

    public function updatePassword($updatedInfo, $user)
    {
        if (!empty($updatedInfo['password'])) {
            $user->password = bcrypt($updatedInfo['password']);
            $user->save();
        }
    }

    public function updateDepartments($updatedInfo)
    {
        $store = $this->getStore();
        $this->deleteNotMentionedDepartments($updatedInfo['departments'], $store->departments);
        $createDepartmentCommand = [];
        foreach ($updatedInfo['departments'] as $newDepartment) {
            $oldDepartment = $this->findDepartmentByType($newDepartment['department_id'], $store->departments);
            if ($oldDepartment == null) {
                //create new departments
                $newDepartment['store_id'] = $store->id;
                $newDepartment['department_manager_picture'] = (new PictureHandler())->moveAllPictures($newDepartment['department_manager_picture'], $store, "Store");
                unset($newDepartment['title']);
                $createDepartmentCommand [] = $newDepartment;
            } else if ($oldDepartment != null) {
                //check for update
                $departmentStore = DepartmentStore::where('store_id', '=', $store->id)
                    ->where('department_id', '=', $oldDepartment->pivot->department_id)->first();
                foreach ($this->departmentAttrFunctMap as $attrName => $function) {
                    if (strcmp($departmentStore->{$attrName}, $newDepartment[$attrName])) {
                        $this->$function($departmentStore, $attrName, $newDepartment[$attrName]);//update
                    }
                }
                $departmentStore->save();
            }
        }
        $store->departments()->attach($createDepartmentCommand);
        $this->updateElasticSearch($store);

        return true;
    }

    private function updateDepartmentManagerPicture($departmentStore, $attrName, $tempPicPath)
    {
        if (strcmp($departmentStore->department_manager_picture, $tempPicPath)) {
            $store = Store::find($departmentStore->store_id);
            $savedPath = (new PictureHandler())->moveAllPictures($tempPicPath, $store, "Store");
            $this->generalUpdateAttribute($departmentStore, $attrName, $savedPath);
        }
    }

    private function deleteNotMentionedDepartments($updatedDepartments, $oldDepartments)
    {
        foreach ($oldDepartments as $old) {
            $match = $this->findDepartmentByTypeInArray($old->pivot->department_id, $updatedDepartments);
            if ($match == null)
                $old->pivot->delete();
        }
    }

    private function findDepartmentByType($type, $departments)
    {
        foreach ($departments as $department) {
            if (!strcmp($department->pivot->department_id, $type)) {
                return $department;
            }
        }
        return null;
    }

    public function deleteDepartmentManagerPicture(Request $request)
    {
        $store = $this->getStore();
        $path = $request->input('path');
        if (strpos($path, "temp") !== false) {
            (new PictureHandler())->deleteTempPicture($path);
        } else {
            $departmentStore = DepartmentStore::where('store_id', '=', $store->id)
                ->where('department_manager_picture', '=', $path)->first();
            if ($departmentStore->id) {
                (new PictureHandler())->deletePicturesFromFile($store, $path);
                $this->generalUpdateAttribute($departmentStore, 'department_manager_picture', null);
                $departmentStore->save();
            } else
                throw new AccessDeniedHttpException;
        }
    }

    private function findDepartmentByTypeInArray($type, $departmentsArray)
    {
        foreach ($departmentsArray as $department) {
            if (!strcmp($department['department_id'], $type)) {
                return $department;
            }
        }
        return null;
    }

    public function deleteManagerPicture($store)
    {
        $path = $store->manager_picture;
        (new PictureHandler())->deletePicturesFromFile($store, $path);
        $store->update(['manager_picture' => null]);

    }

    private function updateWorkTimeDB($newWorkTime, $oldWorkTime)
    {
        $newWorkTime = WorkTimeHandler::changeWorkTimeStyle($newWorkTime);
        if (is_null($oldWorkTime)) {
            $oldWorkTime = new WorkTime();
            $oldWorkTime->store_id = $this->getStore()->id;
        }
        $oldWorkTime->opening_time = $newWorkTime['opening_time'];
        $oldWorkTime->closing_time = $newWorkTime['closing_time'];
        $oldWorkTime->is_closed = $newWorkTime['is_closed'];
        $oldWorkTime->save();
    }

    private function updateStoreName($store, $attrName, $newVal)
    {
        if (strcmp($store->user->name, $newVal)) {
            $store->user()->update(['name' => $newVal]);
        }
    }

}