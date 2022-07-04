<?php

namespace App\Http\Controllers;

use App\BuyerAddress;
use App\Permission;
use App\Repositories\AddressRepository;
use App\Repositories\UserRepository;
use App\User;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;
use Wego\ShamsiCalender\Shamsi;
use Wego\Shipping\Company\Post;
use Wego\Shipping\Company\Tipax;
use Wego\Shipping\Company\Wegobazaar;
use Wego\Shipping\Company\WegoJet;
use Wego\Transforms\UserTransformer;
use Wego\UserHandle\UserPermission;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Zizaco\Entrust\EntrustFacade as Entrust;
use Zizaco\Entrust\EntrustRole;

class AddressController extends ApiController
{
    use Helpers;

    protected $addressRepository, $userRepository;

    function __construct(AddressRepository $addressRepository, UserRepository $userRepository)
    {
        $this->addressRepository = $addressRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getBuyerAddresses(Request $request)
    {
        $user = $this->auth->user();
        $addresses =  $this->userRepository->getAddresses($user->id);
        return $addresses->toArray();
    }

    public function getBuyerAddressesAsAdmin(Request $request)
    {
        $userId = $request['user_id'];
        $addresses =  $this->userRepository->getAddresses($userId);
        return $addresses->toArray();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $this->auth->user();

        $saveAddress = $this->userRepository->saveAddresses($user->id, $request->all());

        return $this->respondArray(["id" => $saveAddress->id]);

    }

    public function storeAsAdmin(Request $request)
    {
        $saveAddress = $this->userRepository->saveAddresses($request->user_id, $request->all());
        return $this->respondArray(["id" => $saveAddress->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = $this->auth->user();
        $this->userRepository->updateAddresses($user->id,$id,$request->all());
        return $this->respondOk('address updated successfully');
    }

    public function updateAsAdmin(Request $request, $id)
    {
        $this->addressRepository->update($request->all(),$id);
        return $this->respondOk('address updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->auth->user();
        $this->userRepository->deleteAddresses($user->id,$id);
        return $this->respondOk('address deleted successfully');
    }

    public function destroyAsAdmin(Request $request,$id)
    {
        $this->userRepository->deleteAddresses($request['user_id'],$id);
        return $this->respondOk('address deleted successfully');
    }

    public function getShippingByAddress()
    {
        $companies = collect([(new Post()), (new WegoJet()), (new Wegobazaar())]);
        $totalPrice = 10000;
        $totalWeight = 1000;
        $address = BuyerAddress::findOrFail(request()->address);

        $res =  $companies->map(function ($company) use ($totalPrice, $totalWeight,$address) {
            return
                collect(
                    $company
                        ->setAddress($address)
                        ->setTotalProductsPrice($totalPrice)
                        ->setTotalWeight($totalWeight)
                        ->get()
                );
        })->reject(function ($item) {
            return $item->isEmpty();
        })->values();

        $final = $res->map(function($rs){
            if($rs['company'] == 'Wegobazaar'){
                $now = Carbon::now();
                $array = [];
                for($i=1;$i<4;$i++){
                    $possibleDate = $now->addDay(1);
                    $time = [];
                    $time[] = $possibleDate->toDateString() . '&10-22';
                    $array[] = ['day' => Shamsi::timeDetail($possibleDate)['weekday'],
                        'time' => $time];
                    $rs['shipping_time'] = $array;
                }
            }
            return $rs;
        })->values();

        return $final;

    }

    public function jsonPars() {
        $myJson = "{'name':'arash','family':'samandar','age':31}";
        echo $myJson;
    }
}
