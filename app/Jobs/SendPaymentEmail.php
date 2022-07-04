<?php

namespace App\Jobs;

use App\BuyerAddress;
use App\Wego\Buy\Payment\Online;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Wego\Province\Util\MemoryProvinceManager;
use Wego\Services\Notification\SmsNotifier;

class SendPaymentEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $location = (new MemoryProvinceManager())->
            getProvinceAndCity($this->order->address->province_id,$this->order->address->city_id)->toJson();
        $orderId = encrypt($this->order->id);
        $url = 'https://api.wegobazaar.com/pay/'.$orderId;
        $url = $this->shorten($url);
        $email = $this->order->user->email;
        Mail::send('email.factor',['order'=>$this->order, 'location'=>$location, 'url'=>$url],function($message) use($email){
            $message->to($email)->subject('رسید خرید شما از شیانچی');
        });
    }

    public function shorten($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"api.yon.ir/?url=".$url);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $content = \GuzzleHttp\json_decode($content,true);
        curl_close($ch);
        $shortenUrl = "http://yon.ir/".$content['output'];
        return $shortenUrl;
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL,"https://www.googleapis.com/urlshortener/v1/url?key=AIzaSyAzrk5CZE2i-HTHuYxea6rdGppZX2o3oWM");
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS,
//            json_encode(["longUrl"=>$url]));
//        curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json"));
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        $server_output = curl_exec ($ch);
//        curl_close ($ch);
//        $output = \GuzzleHttp\json_decode($server_output,true);
//        return $output['id'];
    }
}
