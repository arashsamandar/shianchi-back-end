<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/09/17
 * Time: 17:42
 */

namespace Wego\Transforms;


use App\Color;
use League\Fractal\TransformerAbstract;

class ColorTransformer extends TransformerAbstract
{
    use TransformerHelper;
    protected $fields;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    public function transform(Color $color)
    {
        return $this->transformWithFieldFilter([
            'id' => $color->id,
            'persian_name' => $color->persian_name,
            'name' => $color->name,
            'english_name' => $color->english_name,
            'code' => $color->code
        ], $this->fields);
    }
}