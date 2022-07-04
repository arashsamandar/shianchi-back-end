<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/09/17
 * Time: 15:55
 */

namespace Wego\Transforms;


use App\Store;
use League\Fractal\TransformerAbstract;

class StoreTransformer extends TransformerAbstract
{
    use TransformerHelper;

    protected $fields;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    protected $availableIncludes = [
        'categories',
        'phones'
    ];

    protected $defaultIncludes = [
        'phones'
    ];

    public function includeCategories(Store $store)
    {
        $categories = $store->categories;

        return $this->collection($categories,new CategoryTransformer($this->getFields('categories.',$this->fields)));
    }

    public function transform(Store $store)
    {
        return $this->transformWithFieldFilter(
            [
                "url" => $store->url,
                "english_name" => $store->english_name,
                "persian_name" => $store->user->name,
                "lat" => $store->lat,
                "long" => $store->long,
                "wego_expiration" => $store->wego_expiration,
                "address" => $store->address,
                "province_id" => $store->province_id,
                "city" => $store->city,
                "city_id" => $store->city_id,
                "business_license" => $store->business_license,
                "bazaar" => $store->bazaar,
                "information" => $store->information,
                "shaba_number" => $store->shaba_number,
                "fax_number" => $store->fax_number,
                "about_us" => $store->about_us,
                "manager_national_code" => $store->manager_national_code,
                "manager_first_name" => $store->manager_first_name,
                "manager_last_name" => $store->manager_last_name,
                "manager_picture" => $store->manager_picture,
                "account_number" => $store->account_number,
                "card_number" => $store->card_number,
                "card_owner_name" => $store->card_owner_name
            ], $this->fields
        );
    }

    public function includePhones(Store $store)
    {
        $phones = $store->phones;
        return $this->collection($phones, new PhoneTransformer());
    }

}