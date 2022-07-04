<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/09/17
 * Time: 17:40
 */

namespace Wego\Transforms;


use App\Value;
use League\Fractal\TransformerAbstract;

class ValueTransformer extends TransformerAbstract
{

    use TransformerHelper;
    protected $fields;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    public function transform(Value $value)
    {
        return $this->transformWithFieldFilter([
            'id' => $value->id,
            'name' => $value->name,
            'specification_id' => $value->specification_id
        ], $this->fields);
    }
}