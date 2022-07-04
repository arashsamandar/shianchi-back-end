<?php

namespace App\Http\Controllers;

use App\Product;
use App\Store;
use App\StoreTelegramId;
use App\TelbotStatus;
use Elasticsearch\ClientBuilder;
use Telegram\Bot\Laravel\Facades\Telegram;
use Wego\Helpers\PersianUtil;
use Wego\Search\ElasticQueryMaker;

class TelegramController extends Controller
{
    public function setTelegramWebhook()
    {
        Telegram::setWebhook(['url' => 'https://api.wegobazaar.com/<token>/webhook']);
    }
    public function index()
    {
        $updates = Telegram::getWebhookUpdates();
        if ($this->checkIfUsernameExists($updates)) {
            $status = $this->checkIfChatIdExists($updates);
            if ($this->updateIsNotCommand($updates)) {
                $this->doSomething($updates, $status);
            } else {
                Telegram::CommandsHandler(true);
            }
        } else {
            if (isset($updates['message'])) {
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'شما مجاز به استفاده از این سرویس نیستید'
                ]);
            }
        }


        return 'ok';
    }

    private function checkIfChatIdExists($updates)
    {
        $status = TelbotStatus::find($updates['message']['chat']['id']);

        if (empty($status->id)) {
            $status = TelbotStatus::create(['id' => $updates['message']['chat']['id']]);
        }
        return $status;
    }

    private function updateIsNotCommand($updates)
    {
        if (strpos($updates['message']['text'], '/') !== false) {
            return false;
        }
        return true;

    }

    private function doSomething($updates, $status)
    {
        $keyboard = [['بازگشت به منوی اصلی']];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $this->checkIfReturnToMainMenuSelected($updates, $status);
        if ($status->status == 1) {
            if ($updates['message']['text'] == 'تغییر موجودی کالا') {
                $status->status = TelbotStatus::INSERTING_PRODUCT_NAME_FOR_QUANTITY;
                $status->save();
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'لطفا نام محصول را برای جستجو وارد کنید',
                    'reply_markup' => $reply_markup
                ]);
            } elseif ($updates['message']['text'] == 'تغییر قیمت کالا') {
                $status->status = TelbotStatus::INSERTING_PRODUCT_NAME_FOR_PRICE;
                $status->save();
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'لطفا نام محصول را برای جستجو وارد کنید',
                    'reply_markup' => $reply_markup
                ]);

            }
        } elseif ($status->status == 2) {
            $productName = $updates['message']['text'];
            $store = StoreTelegramId::where('telegram_username', '=', $updates['message']['from']['username'])->first();
            $request = ['store_id' => $store->store_id, 'keyword' => $productName, 'from' => 0];
            $query = new ElasticQueryMaker($request);
            $client = ClientBuilder::create()->build();
            $products = $client->search($query->addToSource(['quantity'])->fillQuery()->getQuery());
            $response = '';
            if (!empty($products['hits']['hits'])) {
                foreach ($products['hits']['hits'] as $product) {
                    $response .= sprintf('%s - %s  %s - %s' . PHP_EOL, $product['_id'], $product['_source']['persian_name'],
                        PHP_EOL.'قیمت (تومان)‌: '.$product['_source']['current_price'],'موجودی: '.$product['_source']['quantity'].PHP_EOL);
                }
                $status->status = TelbotStatus::INSERTING_THE_ID_OF_PRODUCT_FOR_QUANTITY;
                $status->data = $productName;
                $status->save();
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => $response,
                    'reply_markup' => $reply_markup
                ]);
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'شماره کالای مورد نظر را وارد نمایید',
                    'reply_markup' => $reply_markup
                ]);
            } else {
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'کالایی با مشخصات وارد شده پیدا نشد لطفا دوباره تلاش کنید',
                    'reply_markup' => $reply_markup
                ]);
            }
        } elseif ($status->status == 3) {
            $productId = PersianUtil::to_english_num($updates['message']['text']);
            if (is_numeric($productId)) {
                $productName = $status->data;
                $store = StoreTelegramId::where('telegram_username', '=', $updates['message']['from']['username'])->first();
                $request = ['store_id' => $store->store_id, 'keyword' => $productName, 'from' => 0];
                $query = new ElasticQueryMaker($request);
                $client = ClientBuilder::create()->build();
                $found = false;
                $products = $client->search($query->addToSource(['quantity'])->fillQuery()->getQuery());
                foreach ($products['hits']['hits'] as $product) {
                    if ($product['_id']==$productId){
                        $found=true;
                    }
                }
                if ($found) {
                    $status->status = TelbotStatus::INSERTING_THE_QUANTITY_OF_PRODUCT;
                    $status->data = $productId;
                    $status->save();
                    $product = Product::find($productId);
                    $picture = $product->pictures->where('type', 0)->first();
                    if ($picture !== null) {
                        if (file_exists(public_path($picture->path))) {
                            Telegram::sendPhoto([
                                'chat_id' => $updates['message']['chat']['id'],
                                'photo' => public_path($picture->path),
                                'caption' => $product->persian_name
                            ]);
                        }
                    }
                    Telegram::sendMessage([
                        'chat_id' => $updates['message']['chat']['id'],
                        'text' => 'لطفا موجودی جدید را برای کالای انتخاب شده وارد کنید',
                        'reply_markup' => $reply_markup
                    ]);
                } else {
                    Telegram::sendMessage([
                        'chat_id' => $updates['message']['chat']['id'],
                        'text' => 'لطفا کالای درخواستی را از لیست انتخاب کنید',
                        'reply_markup' => $reply_markup
                    ]);
                }
            } else {
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'مقدار ورودی باید عدد باشد',
                    'reply_markup' => $reply_markup
                ]);
            }
        } elseif ($status->status == 4) {
            $quantity = PersianUtil::to_english_num($updates['message']['text']);
            if (is_numeric($quantity)) {
                $status->status = TelbotStatus::UPDATE_THE_QUANTITY_OF_PRODUCT;
                $status->save();
                $product = Product::find($status->data);
                $product->quantity = $quantity;
                $product->save();
                Product::where('id', $status->data)->elastic()->get()->addToIndex();
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'تغییرات با موفقیت انجام شد بازگشت به منوی اصلی را انتخاب کنید',
                    'reply_markup' => $reply_markup
                ]);
            } else {
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'مقدار ورودی باید عدد باشد',
                    'reply_markup' => $reply_markup
                ]);
            }

        } elseif ($status->status == 6) {
            $productName = $updates['message']['text'];
            $store = StoreTelegramId::where('telegram_username', '=', $updates['message']['from']['username'])->first();
            $request = ['store_id' => $store->store_id, 'keyword' => $productName, 'from' => 0];
            $query = new ElasticQueryMaker($request);
            $client = ClientBuilder::create()->build();
            $products = $client->search($query->addToSource(['quantity'])->fillQuery()->getQuery());
            $response = '';
            if (!empty($products['hits']['hits'])) {
                foreach ($products['hits']['hits'] as $product) {
                    $response .= sprintf('%s - %s  %s - %s' . PHP_EOL, $product['_id'], $product['_source']['persian_name'],
                        PHP_EOL.'قیمت (تومان)‌: '.$product['_source']['current_price'],'موجودی: '.$product['_source']['quantity'].PHP_EOL);
                }
                $status->status = TelbotStatus::INSERTING_THE_ID_OF_PRODUCT_FOR_PRICE;
                $status->data = $productName;
                $status->save();
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => $response,
                    'reply_markup' => $reply_markup
                ]);
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'شماره کالای مورد نظر را وارد نمایید',
                    'reply_markup' => $reply_markup
                ]);
            } else {
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'کالایی با مشخصات وارد شده پیدا نشد لطفا دوباره تلاش کنید',
                    'reply_markup' => $reply_markup
                ]);
            }
        } elseif ($status->status == 7) {
            $productId = PersianUtil::to_english_num($updates['message']['text']);
            if (is_numeric($productId)) {
                $productName = $status->data;
                $store = StoreTelegramId::where('telegram_username', '=', $updates['message']['from']['username'])->first();
                $request = ['store_id' => $store->store_id, 'keyword' => $productName, 'from' => 0];
                $query = new ElasticQueryMaker($request);
                $client = ClientBuilder::create()->build();
                $found = false;
                $products = $client->search($query->addToSource(['quantity'])->fillQuery()->getQuery());
                foreach ($products['hits']['hits'] as $product) {
                    if ($product['_id']==$productId){
                        $found=true;
                    }
                }
                if ($found) {
                    $status->status = TelbotStatus::INSERTING_THE_PRICE_OF_PRODUCT;
                    $status->data = $productId;
                    $status->save();
                    $product = Product::find($productId);
                    $picture = $product->pictures->where('type', 0)->first();
                    if ($picture !== null) {
                        if (file_exists(public_path($picture->path))) {
                            Telegram::sendPhoto([
                                'chat_id' => $updates['message']['chat']['id'],
                                'photo' => public_path($picture->path),
                                'caption' => $product->persian_name
                            ]);
                        }
                    }
                    Telegram::sendMessage([
                        'chat_id' => $updates['message']['chat']['id'],
                        'text' => 'لطفا قیمت جدید کالا را به تومان وارد نمایید',
                        'reply_markup' => $reply_markup
                    ]);
                }
            } else {
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'مقدار ورودی باید عدد باشد',
                    'reply_markup' => $reply_markup
                ]);
            }
        } elseif ($status->status == 8) {
            $price = PersianUtil::to_english_num($updates['message']['text']);
            if (is_numeric($price)) {
                $status->status = TelbotStatus::UPDATE_THE_PRICE_OF_PRODUCT;
                $status->save();
                $product = Product::find($status->data);
                $product->current_price = $price;
                $product->save();
                Product::where('id', $status->data)->elastic()->get()->addToIndex();
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'تغییرات با موفقیت انجام شد بازگشت به منوی اصلی را انتخاب کنید',
                    'reply_markup' => $reply_markup
                ]);
            } else {
                Telegram::sendMessage([
                    'chat_id' => $updates['message']['chat']['id'],
                    'text' => 'مقدار ورودی باید عدد باشد',
                    'reply_markup' => $reply_markup
                ]);
            }

        }
    }

    private function checkIfUsernameExists($updates)
    {
        if (isset($updates['message'])){
            return StoreTelegramId::where('telegram_username', '=', $updates['message']['from']['username'])->exists();
        }
        return false;
    }

    /**
     * @param $updates
     * @param $status
     * @return array
     */
    private function checkIfReturnToMainMenuSelected($updates, $status)
    {
        if (!strcmp($updates['message']['text'], 'بازگشت به منوی اصلی')) {
            $status->status = TelbotStatus::STARTED;
            $status->save();
            $keyboard = [['تغییر موجودی کالا'], ['تغییر قیمت کالا']];

            $reply_markup = Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
            Telegram::sendMessage([
                'chat_id' => $updates['message']['chat']['id'],
                'text' => 'لطفا عملیات مورد نظر را انتخاب کنید',
                'reply_markup' => $reply_markup
            ]);
        }
    }
}
