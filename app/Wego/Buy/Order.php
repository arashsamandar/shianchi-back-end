<?php

namespace Wego\Buy;

use App\BuyerAddress;
use App\Events\DeliveryTimeChanged;
use App\Payment;
use Carbon\Carbon;
use Wego\DeliveryTimeCalculator;
use Wego\Shipping\RequestedProducts;
use App\Order as OrderModel;

class Order
{
    private $requestedProducts;
    private $quantities;
    private $orderPayload;
    private $shippingDetail;
    private $orderProductContent;
    private $deliveryTime;
    private $user;
    private $couponId;
    private $coupon;
    private $id;
    private $deliveryTimeChanged = false;

    function __construct($quantities, $orderPayload)
    {
        $this->orderPayload = $orderPayload;
        $this->quantities = $quantities;
        $this->requestedProducts = new RequestedProducts($this->quantities);
        if($this->orderPayload->get("shipping_company") == 'WegoJet'){
//            $this->deliveryTime = (new DeliveryTimeCalculator())->calculateWegojetDeliveryTime();
            $this->deliveryTime =  $this->orderPayload->get('delivery_time');
        } else {
            $this->deliveryTime =  $this->orderPayload->get('delivery_time');
        }
    }

    public function generate()
    {
        $this->shippingDetail = $this->getShippingDetail();

        $this->orderProductContent = $this->requestedProducts->getOrderProductContent();
//        if ($this->couponId == 'wegobyepayiz'){
//            $filtered = $this->orderProductContent->filter(function($item){
//                $itemArray = $item->toArray();
//                return $itemArray['discount'] > 0;
//            });
//            if (!$filtered->isEmpty()){
//                $this->couponId = 'chapandargheychi';
//            }
//        }

        $this->coupon = (new Coupon($this->couponId, $this->orderProductContent->sum('price')))->get();
        if (empty($this->id)) {
            $order = $this->user->order()->create($this->getContent());
        } else {
            $this->user->order()->where('id',$this->id)->update(array_except($this->getContent(),['id','ac']));
            $order = $this->user->order()->where('id',$this->id)->first();
        }
        $order->products()->attach($this->orderProductContent->toArray());

        return $order;
    }

    public function getShippingDetail()
    {
        $shippingCompany = $this->getShippingCompanyClass();
        $shippingCompany = new $shippingCompany();
        $address = BuyerAddress::findOrFail($this->orderPayload->get('address'));

        $shippingDetail =
            $shippingCompany
                ->setAddress($address)
                ->setTotalWeight($this->requestedProducts->getTotalWeight())
                ->setTotalProductsPrice($this->requestedProducts->getTotalPrice())
                ->get();

        return collect(['price' => $shippingDetail['real_price'], 'status' => $shippingDetail['status']]);
    }

    private function getShippingCompanyClass()
    {
        return "Wego\\Shipping\\Company\\" . $this->orderPayload->get('shipping_company');
    }

    public function getContent()
    {
        if ($this->orderPayload->get('shipping_company') == 'WegoJet'){
            $count = OrderModel::where('created_at','<',Carbon::now())
                ->where('shipping_company','WegoJet')->count();
        } else {
            $count = OrderModel::where('created_at','<',Carbon::now())
                ->where('shipping_company','<>','WegoJet')->count();
        }
        $addition = '1';
//        if($count % 3 == 2){
//            $addition = '2';
//        } elseif($count % 3 == 1){
//            $addition = '0';
//        }
        if($count % 2 == 1){
            $addition = '0';
        }
        return
            [
                'id' => empty($this->id) ? time() : $this->id,
                'status' => $this->getStatus(),
                'coupon_id' => $this->coupon->get('id'),
                'delivery_time' => $this->deliveryTime,
                'address_id' => $this->orderPayload->get('address'),
                'shipping_company' => $this->orderPayload->get('shipping_company'),
                'shipping_status' => $this->shippingDetail->get('status'),
                'shipping_price' => $this->shippingDetail->get('price'),
                'final_products_price' => $this->orderProductContent->sum('price'),
                'final_order_price' => $this->getFinalPrice(),
                'total_discount' => $this->getTotalDiscount(),
                'payment_id' => $this->orderPayload->get('payment_id'),
                'customer_type'=> $this->orderPayload->get('customer_type'),
                'progressable'=> Payment::isProgressable($this->orderPayload->get('payment_id')),
                'description' => $this->orderPayload->get('description'),
                'ac'=>$addition
            ];
    }

    private function getFinalPrice()
    {
        return $this->shippingDetail->get('price') + $this->orderProductContent->sum('price') - $this->coupon->get('amount');
    }

    public function getTotalDiscount()
    {
        return $this->orderProductContent->sum('discount') + $this->coupon->get('amount');
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setCouponId($couponId)
    {
        $this->couponId = $couponId;
        return $this;
    }

    public function getStatus()
    {
        if($this->deliveryTimeChanged){
            $order = OrderModel::find($this->id);
            return $order->status;
        }
        return OrderModel::IN_PROGRESS;
        $result = DeliveryTimeCalculator::differenceFromNow(OrderModel::PURCHASE_START_TIME);
        if (!$this->purchaseTimeStarted($result) && Payment::isProgressable($this->orderPayload->get('payment_id')))
            return OrderModel::IN_PROGRESS;
        return OrderModel::CREATED;
    }

    private function purchaseTimeStarted($result)
    {
        if ($result < 0)
            return true;
        return false;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function checkDeliveryTimeChange($oldDeliveryTime)
    {
        if($this->deliveryTime != $oldDeliveryTime){
            $this->deliveryTimeChanged = true;
            event(new DeliveryTimeChanged($this->deliveryTime,$this->id));
        }
        return $this;
    }

}