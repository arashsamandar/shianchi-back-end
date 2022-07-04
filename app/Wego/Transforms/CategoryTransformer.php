<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 10/09/17
 * Time: 16:55
 */

namespace Wego\Transforms;


use App\Category;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    use TransformerHelper;

    protected $fields;

    function __construct($fields = null)
    {
        $this->fields = $fields;
    }

    public function transform(Category $category)
    {
        return $this->transformWithFieldFilter([
            'id' => $category->id,
            'name' => $category->name,
            'persian_name' => $category->persian_name,
            'path' => $category->path,
            'english_path' => $category->english_path,
            'isLeaf' => $category->isLeaf,
            'unit' => $category->unit,
            'menu_id' => $category->menu_id
        ], $this->fields);
    }
}