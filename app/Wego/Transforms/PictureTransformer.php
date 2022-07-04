<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/09/17
 * Time: 17:24
 */

namespace Wego\Transforms;


use App\ProductPicture;
use League\Fractal\TransformerAbstract;

class PictureTransformer extends TransformerAbstract
{
    use TransformerHelper;
    protected $fields;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    public function transform(ProductPicture $picture)
    {
        return $this->transformWithFieldFilter([
            'id' => $picture->id,
            'path' => $picture->path,
            'type' => $picture->type,
            'product_id' => $picture->product_id,
        ], $this->fields);
    }
}