<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/09/17
 * Time: 17:31
 */

namespace Wego\Transforms;

use App\SpecialCondition;
use League\Fractal\TransformerAbstract;

class SpecialConditionTransformer extends TransformerAbstract
{
    use TransformerHelper;
    protected $fields;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    public function transform(SpecialCondition $specialCondition)
    {

        return $this->transformWithFieldFilter(
            [
                'id' => $specialCondition->id,
                'type' => $specialCondition->type,
                'expiration' => $specialCondition->expiration,
                'product_id' => $specialCondition->product_id,
                'upper_value' => $specialCondition->upper_value,
                'upper_value_type' => $specialCondition->upper_value_type,
                'status' => $specialCondition->status,
                'amount' => $specialCondition->amount,
                'text' => $specialCondition->text,
            ], $this->fields);
    }
}