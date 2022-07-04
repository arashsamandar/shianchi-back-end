<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 03/10/17
 * Time: 14:15
 */

namespace Wego\Transforms;


use App\Order;
use League\Fractal\TransformerAbstract;

class ByStoreOrderTransformer extends TransformerAbstract
{

    use TransformerHelper;

    protected $validParams = ['status'];
    protected $availableIncludes = [
        'address',
        'products'
    ];

    protected $defaultIncludes = [
        'address',
        'products'
    ];
    private $storeId;

    function __construct($storeId)
    {
        $this->storeId = $storeId;
    }

    public function transform(Order $order)
    {
        $price = $this->getPrice($order);
        return [
            'id' => $order->id,
            'status' => $order->status,
            'delivery_time' => $order->delivery_time,
            'final_products_price' => $price->sum('price'),
            'total_discount' => $price->sum('discount'),
            'created_at' => $order->created_at->toDateTimeString()
        ];
    }

    public function includeAddress(Order $order)
    {
        $address = $order->address;

        return $this->item($address, new AddressTransformer());
    }

    public function includeProducts(Order $order)
    {
        $products = $order->byStoreId($this->storeId);
        return $this->collection($products, new ProductDetailTransformer());
    }

    private function getPrice($order)
    {
        $products = $order->byStoreId($this->storeId);

        return $products->map(function ($item) {
            return collect(['price' => $item->pivot->price, 'discount' => $item->pivot->discount]);
        });

    }
}