<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 2/8/16
 * Time: 11:00 AM
 */

namespace Wego\Product;

use App\Brand;
use App\Category;
use App\Http\Controllers\SpecificationController;
use App\ProductDetail;
use App\Specification;
use App\Value;
use Illuminate\Http\Request;

use App\SpecialCondition;
use App\Http\Requests;
use App\Product;
use Illuminate\Support\Facades\DB;
use Wego\Helpers\ArrayUtil;
use Wego\Helpers\JsonUtil;
use Wego\PictureHandler;
use Wego\UserHandle\UserPermission;

class ProductFactory
{
    protected $request, $product;

    protected $user;
    protected $preFileName = ['150', '250', 'original'];

    /**
     * @param array|Request $request
     * @param $user
     * @return mixed
     */
    public function handle(array $request, $user)
    {
        $this->request = $request;
        $this->user = $user;
        DB::transaction(function () use ($request, $user) {
            $this->product = $this->saveProduct(array_except($request, ['picture', 'value', 'special', 'color', 'brand_id','quantity','current_price' ,'uid']), $user['userable_id']);

            $picture = self::changePicturesStyle($request['pictures']);
            (new PictureHandler)->storePicture($picture, $this->product, $user);


//            $savePrice = new SavePrice;
//            $savePrice->save($this->product, $request['current_price']);

            $this->saveValues($request['values']);
            $this->attachBrandIdToCategory();

//            if (isset($request['special']))
//                $this->saveSpecial($request['special'], $this->product);
//            if (isset($request['colors'])) {
//                $this->attachColorIdToCategory();
//                $this->saveColor($this->product, $request['colors']);
//            }
        });
        return $this->product;
    }

    public static function changePicturesStyle($pictures)
    {
        foreach ($pictures as $key => $picture) {
            $pictures[$key] = JsonUtil::convertKeys($picture, ['type' => 'pic_type']);
        }
        return $pictures;
    }

    public function saveTextValues($values)
    {
        $valuesToSave = ArrayUtil::addTimeStampToArrays($values);
        $this->insertSpecAndValuesToDB($valuesToSave);

    }


    /**
     * @param $request
     * @param $storeId
     * @return static
     */
    private function saveProduct($request, $storeId)
    {
        $insertArray = $request;
//        $insertArray = $this->setWarrantyAttributesToNullIfEmpty($insertArray);
        if (!isset($insertArray['store_id'])) {
            $insertArray["store_id"] = $storeId;
        }
        $product = Product::create($insertArray);
        if ($this->user->userable_type == UserPermission::STAFF){
            $product->staff()->attach($storeId);
            $product->save();
        }
        return $product;
    }

    /**
     * @param $special
     * @param Product $product
     */
    public function saveSpecial($special, ProductDetail $product)
    {
        $array = [];
        if (count($special) > 3)
            return;
        foreach ($special as $item) {
            $array[] = new SpecialCondition($this->chaneExpirationStyleIfNoExpirationConstraint($item));
        }
        if (count($array) > 0)
            $product->special_conditions()->saveMany($array);
    }

    private function chaneExpirationStyleIfNoExpirationConstraint($special)
    {
        $special['expiration'] = self::expirationChecker($special['expiration']);
        return $special;
    }

    public static function expirationChecker($expiration)
    {
        if ($expiration < 0)
            $expiration = SpecialCondition::NO_EXPIRATION;
        return $expiration;
    }

    /**
     * @param Product $product
     * @param array $color
     */
    private function saveColor(Product $product, $color = [])
    {
        $product->colors()->attach($color);
    }

    private function insertSpecAndValuesToDB($valuesToSave)
    {
        $this->insertValues($valuesToSave);
        $this->updateInElastic($valuesToSave);
    }


    /**
     * @param $valuesToSave
     */
    private function insertValues($valuesToSave)
    {
        foreach ($valuesToSave as $valueToSave) {
            $valueToSave['name'] = trim($valueToSave['name']);
            if (!empty($valueToSave['name'])) {
                $value = Value::create($valueToSave);
                $this->product->values()->attach($value->id);
            }
        }
    }

    private function saveValues($values)
    {
        if (array_key_exists('single', $values))
            $this->saveSingleValues($values['single']);
        if (array_key_exists('multi', $values))
            $this->saveMultiValues($values['multi']);
        if (array_key_exists('text_field', $values))
            $this->SaveTextValues($values['text_field']);
    }

    private function saveSingleValues($single)
    {
        $ids=[];
        foreach (array_keys($single) as $key) {
            $ids[] =$single[$key]['value_id'];
        }
        $this->product->values()->attach($ids);
    }

    private function saveMultiValues($multi)
    {
        $values = [];
        foreach (array_keys($multi) as $key) {
            unset($multi[$key]['specification_id']);
            $values = array_merge($multi[$key]['value_id'], $values);
        }
        $this->product->values()->attach($values);
    }

    private function updateInElastic($valuesToSave)
    {
        $ids = [];
        foreach ($valuesToSave as $value) {
            $specificationId = $value['specification_id'];
            $specification = Specification::find($specificationId);
            $category = $specification->category;
            $ids[] = $category->id;
        }
        $ids = array_values(array_unique($ids));
        Category::whereIn('id',$ids)->elastic()->addToIndex();
    }

    private function attachBrandIdToCategory()
    {
        $brandId = $this->request['brand_id'];
        if (!empty($brandId)) {
            $categoryId = $this->request['category_id'];
            $this->product->brand_id = $brandId;
            $this->product->save();
            Brand::find($brandId)->categories()->sync([$categoryId], false);
        }
    }

    /**
     * @param $insertArray
     * @return mixed
     */
    private function setWarrantyAttributesToNullIfEmpty($insertArray)
    {
        if (empty($insertArray['warranty_name']))
            $insertArray['warranty_name'] = null;
        if (empty($insertArray['warranty_text']))
            $insertArray['warranty_text'] = null;
        return $insertArray;
    }

    private function attachColorIdToCategory()
    {
        $colors = $this->request['colors'];
        $this->product->category->colors()->sync($colors, false);
    }

}