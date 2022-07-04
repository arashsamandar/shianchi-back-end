<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 2/19/17
 * Time: 11:09 AM
 */

namespace Wego\Transforms;


use App\BuyerAddress;
use League\Fractal\TransformerAbstract;
use Wego\Province\Util\MemoryProvinceManager;

class AddressTransformer extends TransformerAbstract
{

    public function transform(BuyerAddress $address)
    {
        $location = (new MemoryProvinceManager())->getProvinceAndCity($address->province_id,$address->city_id)->toJson();
        return [
            "id" => $address->id,
            "address" => $address->address,
            'city' => $location['cities']['Title'],
            'province' => $location['name'],
            "postal_code" => $address->postal_code,
            "phone" => $address->prefix_phone_number.$address->phone_number,
            "mobile" => $address->prefix_mobile_number.$address->mobile_number,
            "receiver_first_name" => $address->receiver_first_name,
            "receiver_last_name" => $address->receiver_last_name,
            "national_code" => $address->national_code
        ];
    }
}