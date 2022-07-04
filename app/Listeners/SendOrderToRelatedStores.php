<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Jobs\SendSms;
use App\Store;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Wego\Services\Notification\SmsNotifier;

class SendOrderToRelatedStores
{
    private $smsNotifier;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(SmsNotifier $smsNotifier)
    {
        $this->smsNotifier = $smsNotifier;
    }

    /**
     * Handle the event.
     *
     * @param  OrderShipped  $event
     * @return void
     */
    public function handle(OrderShipped $event)
    {
        foreach ($event->order->products as $productDetail) {
            $mobileNums = [];
            $categoryId = $productDetail->product->category_id;
            $storeIds = DB::table('category_store')->where('category_id',$categoryId)
                ->pluck('store_id')->toArray();
            $stores = Store::whereIn('id',$storeIds)->where('id','>',114)->get();
            foreach($stores as $store){
                $storeBrands = $store->brands;
                if(!empty($storeBrands) && !empty($productDetail->product->brand_id)){
                    $foundedBrand = $storeBrands->where('id',$productDetail->product->brand_id);
                    if (!empty($foundedBrand)){
                        $mobile = $store->manager_mobiles->first();
                        $mobileNum = $mobile->prefix_phone_number . $mobile->phone_number;
                        $mobileNums[] = $mobileNum;
//                        $this->smsNotifier
//                            ->setMessage("سفارش جدید ویگوبازار:\n".$productDetail->product->persian_name)
//                            ->setReceiver($mobileNum)
//                            ->send();
                    }
                } else {
                    $mobile = $store->manager_mobiles->first();
                    $mobileNum = $mobile->prefix_phone_number . $mobile->phone_number;
                    $mobileNums[] = $mobileNum;
                }
            }

            $date = Carbon::now();
            if (strtotime($date->toTimeString()) >= strtotime("22:00:00")) {
                $job = (new SendSms())->setReceiver($mobileNums)
                    ->setMessage("سفارش جدید ویگوبازار:\n".$productDetail->product->persian_name)
                    ->delay(Carbon::now()->addDay()->setTime(9,0));
                dispatch($job);
            } elseif (strtotime($date->toTimeString()) < strtotime("09:00:00")){
                $job = (new SendSms())->setReceiver($mobileNums)
                    ->setMessage("سفارش جدید ویگوبازار:\n".$productDetail->product->persian_name)
                    ->delay(Carbon::now()->setTime(9,15));
                dispatch($job);
            } else {
                $job = (new SendSms())->setReceiver($mobileNums)
                    ->setMessage("سفارش جدید ویگوبازار:\n".$productDetail->product->persian_name)
                    ->delay(Carbon::now()->addMinutes(2));
                dispatch($job);
            }
        }

    }
}
