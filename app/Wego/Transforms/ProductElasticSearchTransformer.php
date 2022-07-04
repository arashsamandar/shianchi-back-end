<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 11/09/17
 * Time: 09:49
 */

namespace Wego\Transforms;


use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class ProductElasticSearchTransformer extends TransformerAbstract
{
    public function transform(Collection $product)
    {
        return [
            'id'=> $product->get('id'),
            'height'=>$product->get('height')
        ];
    }
}