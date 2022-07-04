<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 02/10/17
 * Time: 16:37
 */

namespace Wego\Transforms;


use App\StorePhone;
use League\Fractal\TransformerAbstract;

class PhoneTransformer extends TransformerAbstract
{
    use TransformerHelper;

    protected $fields;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    public function transform(StorePhone $phone)
    {
        return $this->transformWithFieldFilter(
            [
                'number' => $phone->prefix_phone_number . $phone->phone_number
            ], $this->fields
        );
    }
}