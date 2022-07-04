<?php

namespace App\Listeners;

use App\BuyerAddress;
use App\Events\OrderShipped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Wego\Services\Notification\SmsNotifier;

class SendShipmentSMSToBuyer // was implementing the ShouldQueue , Thus it was a Job ( meaning it was going to Queue Stack and waiting our proccess )
{
    private $smsNotifier;

    public function __construct(SmsNotifier $smsNotifier)
    {
        $this->smsNotifier = $smsNotifier;
    }

    public function handle(OrderShipped $event) // this is the function you have to change , change it so you can send your sms's .
    {
        $addressId = $event->order->address_id;
        $buyerAddress = BuyerAddress::find($addressId);
        $mobileNum = $buyerAddress->prefix_mobile_number . $buyerAddress->mobile_number;
//        $this->smsNotifier
//            ->setMessage("شیانچی\nدرخواست شما با کد پیگیری ".$event->order->id." ثبت شد.\n".
//            "نتیجه درخواست در اولین فرصت به شما اعلام خواهد شد.\n"."www.shianchi.com\nبا تشکر")
//            ->setReceiver($mobileNum)
//            ->send();
        $this->smsNotifier->sendOrder($mobileNum,$event->order->id);
    }
}
