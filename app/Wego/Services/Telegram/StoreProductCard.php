<?php
/**
 * Created by PhpStorm.
 * User: hoseinz3
 * Date: 9/9/2017 AD
 * Time: 18:48
 */

namespace App\Wego\Services\Telegram;

use App\Product;
use App\Store;
use Illuminate\Support\Facades\Lang;

class StoreProductCard
{
    protected $product;
    protected $store;
    private $productCard;
    function __construct(Product $product, Store $store)
    {
        $this->product = $product;
        $this->store = $store;
        $this->productCard = new ProductCard();
    }

    public function generate()
    {

        $this->productCard
            ->setCaption($this->getCaption())
            ->setButtonText(Lang::get('generalMessage.TelegramProductCardCaption'))
            ->setButtonUrl($this->getButtonUrl())
            ->setChatId($this->store->telegram_channel_id)
            ->setPhotoPath($this->product->pictures->first()->path)
            ->generate();
    }

    private function getCaption()
    {
        return
            $this->urlify($this->product->persianName) .
            $this->generatePrice() .
            $this->normalize($this->product->category->persian_name) .
            $this->normalize($this->product->brand->persian_name);

    }

    private function urlify($string)
    {
        return str_replace('.', ' ', $string);
    }

    private function generatePrice()
    {
        return "\nقیمت : " . $this->product->current_price . " تومان ";
    }

    private function normalize($string)
    {
        return "\n#" . str_replace(' ', '_', $string);
    }

    private function getButtonUrl()
    {
        return 'http://shiii.ir' .
            Product::url($this->product->id, $this->product->english_name, $this->product->persian_name);
    }

}