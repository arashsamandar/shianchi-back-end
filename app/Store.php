<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Wego\Helpers\JsonUtil;

class Store extends Model
{
    use ElasticquentTrait;
    use softDeletes;
    public static $status =0;


    protected $fillable = [
        'english_name', 'card_number', 'card_owner_name', 'business_license',
        'business_license', 'province_id', 'address', 'bazaar', 'lat', 'long', 'wego_expiration',
        'city_id', 'account_number', 'manager_national_code', 'information',
        'manager_first_name', 'manager_last_name', 'manager_picture', 'color_id', 'account_number',
        'card_number', 'about_us', 'shaba_number', 'fax_number' , 'telegram_channel_id'
    ];

    public function competitor()
    {
        return $this->morphToMany(Competitor::class, 'competitors');
    }

    public function reports()
    {
        return $this->hasMany('App\Report');
    }

    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function phones()
    {
        return $this->hasMany(StorePhone::class);
    }

    public function work_times()
    {
        return $this->hasMany(WorkTime::class);
    }

    public function color()
    {
        return $this->hasOne(Color::class);
    }

    public function bazaar()
    {
        return $this->belongsToMany(Bazaar::class);
    }

    public function menus()
    {
        return $this->hasMany(StoreMenu::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class)
            ->withPivot(
                'department_manager_first_name', 'department_prefix_phone_number',
                'department_phone_number', 'department_manager_last_name',
                'department_email', 'department_manager_picture')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function manager_mobiles()
    {
        return $this->hasMany(ManagerMobile::class);
    }

    public function setPageColorAttribute($value)
    {
        $this->attributes['page_color'] = $value;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function pictures()
    {
        return $this->hasMany(StorePicture::class);
    }

    public function store_phones()
    {
        return $this->hasMany(StorePhone::class);
    }

    public function guarantees()
    {
        return $this->belongsToMany(Guarantee::class)->withPivot(['expiration_time'])->withTimestamps();
    }


    public function shipping_company()
    {
        return $this->belongsToMany(ShippingCompany::class)->withTimestamps();
    }

    public function free_shipping_condition()
    {
        return $this->belongsToMany(FreeShippingConditions::class, 'free_shipping_condition_store', 'store_id', 'free_shipping_condition_id')->withPivot(['upper_value', 'city', 'city_id'])->withTimestamps();
    }

    public function order()
    {
        return $this->belongsToMany(Order::class)->withTimestamps();
    }

    public function wegoCoin()
    {
        return $this->hasMany(WegoCoin::class);
    }


    public static function hasTelegramChanel(Store $store)
    {
        return (!empty($store->telegram_channel_id));
    }
    public function scopeElastic($query)
    {
        $answer = $query->with(['work_times',
            'manager_mobiles', 'departments', 'store_phones', 'categories',
            'user' => function ($query) {
                $query->select('userable_id', 'name', 'email');
            }, 'pictures'])->get();

        $answer = $answer->each(function ($item) {
            $item['location'] = ['lat' => (float)$item['lat'], 'lon' => (float)$item['long']];
        });
        return JsonUtil::removeFields($answer, ['*.lat', '*.long']);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }

    /**
     * @param Product $product
     */
    public static function deleteCategory(Product $product)
    {
        DB::table('category_store')
            ->where('category_id', $product->category_id)
            ->where('store_id', $product->store_id)
            ->delete();
    }


    /**
     * @param $query
     * @param $storeId
     * @return
     */
    public function scopeUpdateElasticSearch($query, $storeId)
    {
        return $query->where('id', $storeId)->elastic()->get()->addToIndex();
    }


}

