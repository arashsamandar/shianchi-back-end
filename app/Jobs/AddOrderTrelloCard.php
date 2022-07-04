<?php

namespace App\Jobs;

use App\Order;
use App\Payment;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Trello\Client;
use Trello\Model\Card;
use Wego\Helpers\PersianUtil;
use Wego\Province\Util\MemoryProvinceManager;
use Wego\ShamsiCalender\Shamsi;

class AddOrderTrelloCard extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $orderId;
    private $listId;
    private $addition;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        $links = $this->generateLinks($elasticResult);
        $order = Order::find($this->orderId);
        $message = $this->buildTrelloCardName($order);
        $this->addCardToTrello($message);
    }

    /**
     * @param $elasticResult
     * @param $productMessage
     * @return string
     */
    private function buildTrelloCardName($order)
    {
        $productMessage = $this->buildProductsNameSeparatedBySharp($order);
        $message = $this->getOrderCodeMessage($order) . '#' . $this->getBuyerName($order) . '#' .
            $this->getBuyerContactNumber($order) . '#' . $this->getBuyerAddress($order) . '#' .
            $productMessage .$this->getCostDetail($order).'#'.$this->getDeliveryInformation($order);
        if( $order->shipping_company=='WegoJet'){
            if($this->addition == '0') {
                $this->listId = '5c76596dda177e22fe7b6c01';
            } elseif ($this->addition == '1') {
                $this->listId = '5c765e8c061bd61047662617';
            } else {
                $this->listId = '5c6d317815d8663e8dde7b00';
            }
        } else {
            if($this->addition == '0') {
                $this->listId = '5c7654cf7ab98c58103fe1d3';
            } elseif ($this->addition == '1') {
                $this->listId = '5c765e83e481c67d467bdcbf';
            } else {
                $this->listId = '5c6d314e2bea9438cd559326';
            }
        }
        return $message;
    }

    /**
     * @param $elasticResult
     * @return string
     * @internal param $productMessage
     */
    private function buildProductsNameSeparatedBySharp($order)
    {
        $productMessage =null;
        foreach ($order->products as $productDetail) {
            $productMessage .= 'نام فروشگاه : '.$productDetail->store->persian_name.": ".
                $productDetail->product->id . "-" . $productDetail->product->persian_name.'#';
        }
        return $productMessage;
    }

    /**
     * @param $elasticResult
     * @return string
     */
    private function getOrderCodeMessage($order)
    {
        if(is_null($order->ac)){
            if ($order->shipping_company == 'WegoJet'){
                $count = Order::where('id','<=',$order->id)
                    ->where('shipping_company','WegoJet')->count();
            } else {
                $count = Order::where('id','<=',$order->id)
                    ->where('shipping_company','<>','WegoJet')->count();
            }
            $this->addition = '0';
            if($count % 2 == 1){
                $this->addition = '1';
            }
        } else {
            $this->addition = $order->ac;
        }
        return 'کد سفارش: ' . $order->id."-".$this->addition.'#تاریخ ثبت سفارش: '.Shamsi::convert(Carbon::parse($order->created_at)->startOfDay());
    }

    private function getBuyerName($order)
    {
        return $order->address->receiver_first_name . ' ' . $order->address->receiver_last_name;
    }

    /**
     * @param $elasticResult
     * @return mixed
     */
    private function getBuyerContactNumber($order)
    {
        return PersianUtil::to_persian_num($order->address->prefix_mobile_number . $order->address->mobile_number . ' - ' .
            $order->address->prefix_phone_number . $order->address->mobile_number);
    }

    private function getBuyerAddress($order)
    {
        $location = (new MemoryProvinceManager())->getProvinceAndCity($order->address->province_id,$order->address->city_id)->toJson();
        return $location['name'] . '-' . $location['cities']['Title'] . '-' . $order->address->address;
    }

    private function getDeliveryInformation($order)
    {
        if (strpos($order->delivery_time,'&') !== false){
            $deliveryDetails = explode('&',$order->delivery_time);
            $deliveryDate = Carbon::parse($deliveryDetails[0]);
            return 'تحویل ' . Shamsi::timeDetail($deliveryDate)['weekday'] . ' : ' .
            Shamsi::convert($deliveryDate).' ساعت'.$deliveryDetails[1].", شرکت تحویل " .$order->shipping_company;
        }
        else {
            return 'تحویل ' . $order->delivery_time . ", شرکت تحویل " .$order->shipping_company;
        }
    }

    /**
     * @param $message
     */
    private function addCardToTrello($message)
    {
        $client = new Client();
        $client->authenticate('8d581336eb2100cb4b6cb3d9ec657143', '5e89a0c974950df3936d6cdf58d06f518b16b351acf3b211a0fd004e60bfd787', Client::AUTH_URL_CLIENT_ID);
        $card = new Card($client);
        $card
            ->setBoardId('593faea9869fcbdb20dc5273')
            ->setListId($this->listId)
            ->setName($message)
//            ->setDescription($links)
            ->save();
    }

    private function generateLinks($elasticResult)
    {
        $links = null;
        foreach ($elasticResult['stores'] as $store) {
            foreach ($store['products'] as $product) {
                $links .= "https://shianchi.com/product/" . $product['product_id'] . "\n";
            }
        }
        return $links;
    }

    private function getCostDetail($order)
    {
        $costMessage = "مبلغ نهایی پرداختی: " . $order->final_order_price . '#' . 'هزینه ارسال :‌' .
            $order->shipping_price . '# وضعیت پرداخت: ';
        if ($order->payment_id == Payment::ONLINE) {
            if ($order->progressable == 'false') {
                $costMessage .= 'پرداخت آنلاین ناموفق';
            } else {
                $costMessage .= 'پرداخت انلاین';
            }
        } elseif ($order->payment_id == Payment::CASH) {
            $costMessage .= 'پرداخت نقدی در محل';
        } elseif ($order->payment_id == Payment::CARD) {
            $costMessage .= "پرداخت کارتی در محل";
        }
        return $costMessage;
    }
}
