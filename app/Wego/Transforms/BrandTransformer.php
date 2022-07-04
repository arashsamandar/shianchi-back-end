<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/09/17
 * Time: 17:47
 */

namespace Wego\Transforms;


use App\Brand;
use League\Fractal\TransformerAbstract;

class BrandTransformer extends TransformerAbstract
{
    use TransformerHelper;


    protected $fields;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    public function transform(Brand $brand)
    {
        return $this->transformWithFieldFilter(
            [
                'english_name' => $brand->english_name,
                'persian_name' => $brand->persian_name
            ], $this->fields);
    }
}