<?php

namespace App\Listeners;

use App\Events\ProductSetToExist;
use App\OutsideOrder;
use App\Product;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kavenegar\KavenegarApi;

class checkForInformSms implements ShouldQueue
{

    /**
     * Handle the event.
     *
     * @param  ProductSetToExist  $event
     * @return void
     */
    public function handle(ProductSetToExist $event)
    {
        $product = Product::find($event->productId);
        $phone_numbers = OutsideOrder::where('created_at', '>', Carbon::now()->subDays(14)->toDateTimeString())
            ->where('name',$product->persian_name)->get()->pluck('phone_number')->toArray();
        $client = new KavenegarApi(env('SMS_API_KEY'));
        $originalUrl = 'http://shiii.ir/product/'.$product->id;
        $url = $this->shorten($originalUrl);
        foreach(array_chunk($phone_numbers,190) as $chunkNumbers){
            $client->Send(env('SMS_NUMBER'), $chunkNumbers,
                "کاربر گرامی کالای درخواستی شما موجود شد. همین حالا می توانید از طریق لینک زیر کالای خود را خریداری نمایید.\n"
                .$url."\n"."ویگوبازار"
            );
        }

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
