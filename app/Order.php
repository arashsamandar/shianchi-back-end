<?php

namespace App;

use App\Events\OrderStatusSetToPurchased;
use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Wego\Buy\BuyStorageUtil;

class Order extends Model
{
    use ElasticquentTrait;
    const PURCHASE_START_TIME = "15:00:00";
    const EXPIRED_NOT_PROGRESSABLE_DURATION = 24 * 60 * 60; // 24h 60min 60 sec
    const PAGINATE_SIZE = 10;
    const CREATED = 0;
    const IN_PROGRESS = 1;
    const AVAILABLE = 2;
    const UNAVAILABLE = 3;
    const PURCHASED = 4;
    const DELIVERED = 5;
    const CANCELLED = 6;
    protected $persianStatus = [
        self::CREATED => 'ساخته شده',
        self::IN_PROGRESS => 'درحال پردازش',
        self::AVAILABLE => 'موجود',
        self::UNAVAILABLE => 'نا موجود',
        self::PURCHASED => 'خریداری شده',
        self::DELIVERED => 'تحویل داده شده',
        self::CANCELLED => 'کنسل شده'
    ];
    protected $fillable = [
        'status', 'coupon_id', 'delivery_time', 'address_id', 'progressable',
        'shipping_company', 'shipping_status', 'shipping_price', 'id',
        'final_products_price', 'final_order_price', 'payment_id', 'total_discount' , 'customer_type' , 'ac'
    ];
    protected $attributes = [
        'progressable' => '0',
    ];

    public function products()
    {
        return $this->belongsToMany(ProductDetail::class, 'order_product', 'order_id', 'detail_id')->withTimestamps()->withPivot(
            [
                'gift', 'gift_count', 'quantity', 'price', 'discount', 'status','id'
            ])->withTrashed();
    }
    public function pproducts()
    {
        return $this->belongsToMany(Product::class,'order_product','order_id','product_id')->withTimestamps()->withPivot(
            [
                'gift', 'gift_count', 'quantity', 'price', 'discount', 'status'
            ]);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class)->withPivot(['total_delivery_price', 'total_discount', 'total_product_price']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(BuyerAddress::class,'address_id')->withTrashed();
    }

    public function BazaarStaffMessages()
    {
        return $this->hasMany('App\BazaarStaffMessage');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public static function orderIsPurchased($orderId)
    {
        $order = BuyStorageUtil::getOrderById($orderId);
        if (!strcmp($order['status'], Order::CREATED))
            $order['status'] = Order::IN_PROGRESS;

        $order['progressable'] = true;
        $order['payment_id'] = Payment::ONLINE;
        BuyStorageUtil::updateOrder($order);
    }

    public function audits()
    {
        return $this->belongsToMany(Audit::class . 'order_audit');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function hasGift()
    {
        return (!empty($this->coupon_id));
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $this->convertStatusToNumber($status));
    }

    public function scopeUpdateStatus($query, $status)
    {
        $query->update(['status' => $this->convertStatusToNumber($status)]);
    }

    public function updateProductsStatus($detail_id, $status)
    {
        $this->products()->updateExistingPivot($detail_id, ['status' => (new Product())->convertStatusToNumber($status)]);
        if($status == "Unavailable"){
            $detail = ProductDetail::find($detail_id);
            $detail->quantity = 0;
            $detail->save();
            Product::where('id',$detail->product_id)->elastic()->addToIndex();
        }
        $order = Order::find($this->id);
        $order->setStatus();
    }

    public function setStatus()
    {
        $status = $this->getStatus();
        $this->status = $status;
        $this->save();
    }

    public function convertStatusToNumber($status)
    {
        switch ($status) {
            case "Created" :
                return self::CREATED;
            case "InProgress":
                return self::IN_PROGRESS;
            case "Available" :
                return self::AVAILABLE;
            case "Unavailable" :
                return self::UNAVAILABLE;
            case "Purchased" :
                return self::PURCHASED;
            case "Delivered" :
                return self::DELIVERED;
            case "Cancel" :
                return self::CANCELLED;
            default:
                throw new InvalidArgumentException('status not applied');
        }
    }

    public function byStoreId($storeId)
    {
        return $this->products->filter(function ($item) use ($storeId) {
            return $item->store_id == $storeId;
        });
    }

    public function getPrice()
    {
        return $this->products->map(function ($item) {
            return collect(['price' => $item->pivot->price, 'discount' => $item->pivot->discount]);
        });
    }

    public function getStatus()
    {
        $status = $this->products->map(function ($product) {
            return $product->pivot->status;
        });

        if ($status->contains(Product::PRE_PURCHASE))
            return Order::IN_PROGRESS;
        elseif ($status->contains(Product::UNAVAILABLE))
            return Order::UNAVAILABLE;
        elseif ($status->contains(Product::AVAILABLE))
            return Order::AVAILABLE;
        if ($this->status != Order::PURCHASED) {
            event(new OrderStatusSetToPurchased($this));
        }
        return Order::PURCHASED;
    }


}
