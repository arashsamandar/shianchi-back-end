<?php

namespace App\Http\Controllers;

use App\Buyer;
use App\BuyerAddress;
use App\Coupon;
use App\GameOrder;
use App\Http\Requests\StoreBuyerRequest;
use App\Http\Requests\UpdateBuyerRequest;
use App\Order;
use App\Repositories\UserRepository;
use App\WegoCoin;
use Dingo\Api\Routing\Helpers;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Mail;
use Kavenegar\Exceptions\ApiException;
use Kavenegar\Exceptions\HttpException;
use Kavenegar\KavenegarApi;
use Laravel\Socialite\One\User;
use Mockery\Exception;
use Wego\Buyer\BuyerEditor;
use Wego\Buyer\BuyerCreator;
use Wego\PictureHandler;
use Illuminate\Support\Facades\Lang;
use Wego\Services\Captcha\CaptchaInterface;
use Wego\Services\Notification\SmsNotifier;
use Wego\UserHandle\UserPermission;

class BuyerController extends ApiController
{
    use Helpers;
    protected $tempPicRequestRule = [
        "pic" => "required|image|max:6000"
    ];
    const NUMBER_OF_REGISTER_FIELD = 8;
    const PAGINATION_SIZE = 10;
    protected $buyerCreator;//TODO : in be dard mikhore?
    protected $buyerValidation;
    protected $captcha;
    protected $userRepository;
    public function __construct(BuyerCreator $buyerCreator,CaptchaInterface $captcha,UserRepository $userRepository)
    {
        $this->buyerCreator = $buyerCreator;
        $this->captcha = $captcha;
        $this->userRepository = $userRepository;
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreBuyerRequest $request
     * @return mixed
     */
    public function store(StoreBuyerRequest $request)
    {
//        $payload = [
//            'response' => $request->input('g-recaptcha-response'),
//            'remote_ip' => $request->ip()
//        ];
//        $this->captcha->setPayload($payload)->verify();
        $token = $this->buyerCreator->create($request->all());

        return $this->respondArray($token);

    }

    public function show($id)
    {
        return $this->respondErrorOrOk($this->buyerCreator->show($id));
    }

    public function changePicture(Request $request)
    {
        $user = $this->auth->user();
        $newPicture[] = [
            'path' => $request->input('new_path')];
        $oldPicture[] = [
            'path' => $request->input('old_path')];
        $buyer = $user->getUserableType();
        (new PictureHandler())->changePicture($newPicture, $oldPicture, $buyer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBuyerRequest $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = $this->auth->user();
        $buyer = $user->getUserableType();
        (new BuyerEditor())->update($request->toArray(), $buyer->id);
        return $this->respondOk('Updated', 'message');
    }

    /**
     * get buyer's information in json format
     * @param Request $request
     * @return string
     */
    public function getJson(Request $request)
    {
        $user = $this->auth->user();
        $buyerEditor = new BuyerEditor;
        if ($user->userable_type === UserPermission::BUYER)
            return $buyerEditor->getJsonInfo($user);
        $order = Order::findOrFail($request->email);
        $user = $order->user;

        if ($user->userable_type === UserPermission::BUYER){
            return $buyerEditor->getJsonInfo($user);
        }
        return $this->setStatusCode(404)->respondWithError(Lang::get('generalMessage.PermissionNotAllowed'));
    }

    public function getJsonAsAdmin(Request $request)
    {
        $buyerEditor = new BuyerEditor();
        $user = $this->userRepository->firstOrFailByField('email',$request->email);
        return $buyerEditor->getJsonInfo($user);
    }

    /**
     * saves user's picture which its name is user's id
     * @param Request $request
     * @return mixed
     *
     */
    public function savePicture(Request $request)
    {
        //TODO JWTAUTH request rules
        $user = $this->auth->user();
        $picture[] = [
            'path' => $request->input('image_path')];
        $buyer = $user->getUserableType();
        (new PictureHandler())->storePicture($picture, $buyer);
        return $this->respondOk("successfully saved");

    }

    public function deletePicture(Request $request)
    {
        //TODO REQUEST RULES
        $user = $this->auth->user();
        if ($this->isTempFile($request['path'])){
            (new PictureHandler())->deleteTempPicture($request['path']);
            return $this->respondOk();
        }
        $buyer = $user->getUserableType();
        $path = $buyer->image_path;
        (new PictureHandler())->deletePicture($path, $buyer);
        return $this->respondOk();
    }


    //TODO jaie dorost
    public function coinByStore(Request $request)
    {
        $column = "store_id";
        $result = $this->getCoinByColumn($column);
        return $this->paginateResult($result, $request);
    }

    //TODO jaie dorost
    public function coinByExpiration(Request $request)
    {
        $column = "expiration";
        $result = $this->getCoinByColumn($column);
        return $this->paginateResult($result, $request);
    }

    //TODO jaie dorost
    //TODO use($user) ziadie
    public function getCoinByColumn($column)
    {
        $user = UserPermission::checkBuyerPermission();

        $coin = WegoCoin::where('user_id', '=', $user->id)->with(['store' => function ($query) use ($user) {
            $query->with(['user' => function ($query) use ($user) {
                $query->select(['users.userable_id', 'users.name']);
            }])->select(['stores.id', 'stores.english_name', 'stores.information', 'stores.url']);
        }])->orderBy($column, 'asc')->get();
        return $coin;

    }

    //TODO jaie dorost
    public function mapOutput($item)
    {
        return [
            "amount" => $item["amount"],
            "detail" => $item["store"]["information"],
            "status" => $item["status"],
            "exp" => $item["expiration"],
            "make" => $item["created_at"],
            "store_id" => $item["store"]["id"],
            "store_english_name" => $item["store"]["english_name"],
            "store_persian_name" => $item["store"]["user"]["name"],
            "store_url" => $item["store"]["url"]
        ];
    }

    public function tempStorePicture(Requests\TemporaryStoreBuyerPictureRequest $request)
    {
        $user = $this->auth->user();
        return $this->respondOk((new PictureHandler($request, $user['userable_id']))
            ->setResizeOption([])
            ->setUsePath('buyer')
            ->save(), 'path');
    }

    private function paginateResult($result, Request $request)
    {
        $prettifyResult = array_map([$this, 'mapOutput'], $result->toArray());

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $collection = collect($prettifyResult);
        $perPage = self::PAGINATION_SIZE;

        $currentPageSearchResults = $collection->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $paginatedSearchResults = new LengthAwarePaginator($currentPageSearchResults, count($collection), $perPage);
        $paginatedSearchResults->setPath($request->url());

        return $paginatedSearchResults->toArray();
    }
    private function isTempFile($path)
    {
        return (strpos($path, "temp") !== false);
    }

    public function sendGroupSmsToBuyers()
    {
        $buyer = BuyerAddress::where('id','>',4666)->Where('mobile_number',"<>","")->get();
        $numbers = [];
        foreach($buyer as $b){
            if($b->id % 10 == 5) {
                $mobileNum = $b->prefix_mobile_number . $b->mobile_number;
                if (substr($mobileNum, 0, 2) === "09") {
                    $numbers[] = $mobileNum;
                }
            }
        }
        $numbers = array_unique($numbers);
        $numbers = array_values($numbers);
        $client = new KavenegarApi(env('SMS_API_KEY'));
        $count = 0;
        foreach(array_chunk($numbers,190) as $chunkNumbers){
            dump($count);
            try {
                $client->Send(env('SMS_NUMBER'), $chunkNumbers,
                    "***?????????? ?????????? ??????????????????***\n".
                    "?????? ???????? ???? ???? ???????????? ????????:\n".
                    "http://yon.ir/Xhe9o\n".
                    "???????????? ???????????? ?????????? ?? ???????? ???? ???????? ??????!");
                $count++;
            } catch(ApiException $ex)
            {
                dd($ex->errorMessage());
            }
            catch(HttpException $ex)
            {
                dd($ex->errorMessage());
            }
        }
        return $this->respondOk();
    }

    public function sendGroupSmsToGameParticipants()
    {
        $buyer = GameOrder::where('type','fifa')->orWhere('type','pesFifa')->get();
        $numbers = [];
        foreach($buyer as $b){
            $mobileNum = $b->mobile_number;
            if(substr($mobileNum,0,2)==="09"){
                $numbers[] = $mobileNum;
            }
        }
        $numbers = array_unique($numbers);
        $numbers = array_values($numbers);
        $client = new KavenegarApi(env('SMS_API_KEY'));
        $count = 0;
        foreach(array_chunk($numbers,190) as $chunkNumbers){
            dump($count);
            try {
                $client->Send(env('SMS_NUMBER'), $chunkNumbers,
                    "?????? ?????? ?????? ???????? ?????????????? FIFA18 ???????? ????.\n".
                    "???? ???? ???????? ?????? ???????????? ?????? ???? ???????? ????????.\n".
                    "?????? ?????? ???? ???????? ??????:\n". "https://goo.gl/nQQcgo\n"."??????????????????");
                $count++;
            } catch(ApiException $ex)
            {
                dd($ex->errorMessage());
            }
            catch(HttpException $ex)
            {
                dd($ex->errorMessage());
            }
        }
        return $this->respondOk();
    }

    public function sendGroupEmailToBuyers()
    {
        $users = Buyer::all();
//        $users = BuyerAddress::where('city_id',1698)->get();
        $emails = [];
        foreach($users as $buyer){
            if($buyer->id % 6 == 3) {
                $emails[] = $buyer->user->email;
            }
        }
        $count=0;
        $images = [];
        $images[] = ['href'=>"yon.ir/N5pJk" , 'src'=>"https://api.wegobazaar.com/stock.jpg"];
        foreach(array_chunk($emails,300) as $chunkemail) {
            dump($count);
            try {
                $description ="***?????????? ?????????? ??????????????????***"."\n".
                    "???????? ?????? ???????????? ???? ???????? ????????\n".
                    "?????? ???????? ???? ???? ???????????? ????????\n".
                    "???????????? ???????????? ?????????? ???????? ???????? ???? ???? ?????? ??????????!\n".
                    "???????????? ?? ???????? ???? ???????? ???????? ???? ?????? ???????? ?? ???? ?????? ?? ???????? ??????:\n" .
                    "yon.ir/N5pJk\n\n";
                $subject = '***?????????? ?????????? ??????????????????***';
                $buttonHref ='yon.ir/N5pJk';
                $buttonText = '???????????? ?? ????????';
                Mail::send('email.general',['subject'=>$subject,'description'=>$description,
                    'images'=>$images, 'buttonHref'=>$buttonHref,
                    'buttonText'=>$buttonText],function($message) use($chunkemail){
                    $message->bcc($chunkemail)->subject('?????????? ?????????? ??????????????????');
                });
                $count++;
            } catch(\Exception $e){
                dd($e->getMessage());
            }
        }
    }

    public function sendSpecificBuyerSmsAndEmail(Request $request)
    {
        $offset = $request->offset;
        $startId = ($offset * 50) -1;
        $endId = ($offset+1) * 50;
        dump($startId,$endId);
        $buyers = Buyer::where('id','>',$startId)->where('id','<',$endId)->get();
        $client = new KavenegarApi(env('SMS_API_KEY'));
        foreach ($buyers as $buyer){
            $addresses = BuyerAddress::where('user_id',$buyer->user->id)->where('mobile_number',"<>","")->get();
            $numbers = [];
            $firstNames = [];
            foreach($addresses as $b){
                $mobileNum = $b->prefix_mobile_number . $b->mobile_number;
                if(substr($mobileNum,0,2)==="09"){
                    $numbers[] = $mobileNum;
                    $firstNames[] = $b->receiver_first_name;
                }
            }
            $combine = array_combine($numbers,$firstNames);
            $numbers = array_unique($numbers);
            $numbers = array_values($numbers);
            foreach($numbers as $number){
                $newId = (new CouponController())->generateRandomString();
                $couponData = [];
                $couponData['id'] = $newId;
                $couponData['type'] = Coupon::COUPON;
                $couponData['amount'] = 10000;
                $couponData['min_purchase'] = 50000;
                $couponData['expiration_time'] = "2018-02-24 00:00:00";
                try {
                    $coupon = Coupon::create($couponData);
                } catch (QueryException $e) {
                    $couponData['id'] = (new CouponController())->generateRandomString();
                    $coupon = Coupon::create($couponData);
                }
                try {
                    $client->Send(env('SMS_NUMBER'), $number, $combine[$number]." ????????\n????????\n?????????? ?????? ???? ?????????? ???? ?????????????????? ???????? ????????.\n???? ?????????? ?????????????? ??????:\n".$coupon->id."\n10???????? ?????????? ?????????? ???????? ?????????????? ?????????? 50???????? ??????????\n???????????? ???? ?????????? ???????? ????????\n???????????????????????????????? ???????? ???????????????????????????? ???????? ??????????\n???????? ????????: https://goo.gl/Lx5o6n");
                } catch(ApiException $ex)
                {
                    continue;
                }
                catch(HttpException $ex)
                {
                    continue;
                }
            }
        }
    }
    public function sendSpecificEmail(Request $request)
    {
        $offset = $request->offset;
        $startId = ($offset * 50) -1;
        $endId = ($offset+1) * 50;
        dump($startId,$endId);
        $buyers = Buyer::where('id','>',$startId)->where('id','<',$endId)->get();
        foreach ($buyers as $buyer){
            $address = BuyerAddress::where('user_id',$buyer->user->id)->where('mobile_number',"<>","")->first();
            if(!is_null($address)){
                $firstName = $address->receiver_first_name;
            } else {
                $firstName = '??????????';
            }
            $newId = (new CouponController())->generateRandomString();
            $couponData = [];
            $couponData['id'] = $newId;
            $couponData['type'] = Coupon::COUPON;
            $couponData['amount'] = 10000;
            $couponData['min_purchase'] = 50000;
            $couponData['expiration_time'] = "2018-02-24 00:00:00";
            try {
                $coupon = Coupon::create($couponData);
            } catch (QueryException $e) {
                $couponData['id'] = (new CouponController())->generateRandomString();
                $coupon = Coupon::create($couponData);
            }
            try {
                $description =$firstName." ????????\n????????\n?????????? ?????? ???? ?????????? ???? ?????????????????? ???????? ????????.\n???? ?????????? ?????????????? ??????:\n".$coupon->id."\n10???????? ?????????? ?????????? ???????? ?????????????? ?????????? 50???????? ??????????\n???????????? ???? ?????????? ???????? ????????\n???????????????????????????????? ???????? ???????????????????????????? ???????? ??????????\n???????? ????????: https://goo.gl/fJZiU2";
                $subject = "???? ???????? ?????????? ??????????????";
                $imageSrc = 'https://api.wegobazaar.com/shayea.jpg';
                $imageHref = 'https://goo.gl/fJZiU2';
                $buttonHref = 'https://goo.gl/fJZiU2';
                $mail = $buyer->user->email;
                Mail::send('email.general',['subject'=>$subject,'description'=>$description,'imageSrc'=>$imageSrc,
                    'imageHref'=>$imageHref, 'buttonHref'=>$buttonHref],function($message) use($mail){
                    $message->to($mail)->subject('???? ???????? ?????????? ?????????????? ???? ?????? ??????????????????');
                });
            } catch(\Exception $ex)
            {
                continue;
            }
        }
    }
}
