<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 2/19/17
 * Time: 11:14 AM
 */

namespace Wego\Transforms;


use App\WegoCoin;
use League\Fractal\TransformerAbstract;

class WegoCoinTransformer extends TransformerAbstract
{
    public function transform(WegoCoin $wegoCoin){

        return [
            'amount' => $wegoCoin->amount
        ];
    }

}