<?php
/**
 * Created by PhpStorm.
 * User=>wb-2
 * Date=>6/23/16
 * Time=>10:38 AM
 */

namespace Wego\Product;


use App\Brand;
use App\Http\Controllers\SpecificationController;
use App\Product;
use App\SpecialCondition;
use App\Specification;
use App\Value;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Wego\Helpers\JsonUtil;
use Wego\PictureHandler;

class ProductEditor
{
    const GENERAL_UPDATE_ATTRIBUTE = "generalUpdateAttribute";
    protected $productAttrFunctionMap = [
        "english_name" => self::GENERAL_UPDATE_ATTRIBUTE,
        "persian_name" => self::GENERAL_UPDATE_ATTRIBUTE,
        "height" => self::GENERAL_UPDATE_ATTRIBUTE,
        "width" => self::GENERAL_UPDATE_ATTRIBUTE,
        "length" => self::GENERAL_UPDATE_ATTRIBUTE,
        "key_name" => self::GENERAL_UPDATE_ATTRIBUTE,
        "description" => self::GENERAL_UPDATE_ATTRIBUTE,
        "weight" => self::GENERAL_UPDATE_ATTRIBUTE,
//        "wego_coin_need" => self::GENERAL_UPDATE_ATTRIBUTE,
//        "quantity" => self::GENERAL_UPDATE_ATTRIBUTE,
//        "warranty_name" => self::GENERAL_UPDATE_ATTRIBUTE,
//        "warranty_text" => self::GENERAL_UPDATE_ATTRIBUTE,
//        "current_price" => "updatePrice",
    ];
    protected $specialAttrFunctionMap = [
        "type" => self::GENERAL_UPDATE_ATTRIBUTE,
        "amount" => self::GENERAL_UPDATE_ATTRIBUTE,
        "expiration" => "expirationUpdate",
        "text" => self::GENERAL_UPDATE_ATTRIBUTE,
        "upper_value" => self::GENERAL_UPDATE_ATTRIBUTE,
        "upper_value_type" => self::GENERAL_UPDATE_ATTRIBUTE,
    ];
    protected $picTypeNumber = 5;

    public function getJson($productId)
    {
        $productInfo = Product::with(['pictures' => function ($query) {
            $query->orderBy('type', 'asc');
        },
            'values' => function ($query) {
                $query->with('specification');
            },
            'category'
        ])->findOrFail($productId)->toArray();

        //dd($productInfo);
        $productInfo['values'] = $this->getValueStyle($productInfo['values']);
        $breadCrumb = (new ProductParser([]))->prepareBreadcrumb(
            $productInfo['category']['english_path'],
            $productInfo['category']['path']
        );
        $productInfo['category_hierarchy'] = $breadCrumb;
        $productInfo['category_name'] = $productInfo['category']['persian_name'];
        $productInfo['category_unit'] = $productInfo['category']['unit'];

        //TODO: REMOVE ID HAVE SIDE EFFECT?
        $productInfo = JsonUtil::removeFields($productInfo, [
            'second_price', 'created_at', 'updated_at', 'pictures.*.id', 'pictures.*.created_at',
            'pictures.*.updated_at', 'category'
        ]);
        return $productInfo;
    }


    private function getColorsStyle($values)
    {
        $result = [];
        foreach ($values as $value) {
            $result[] = $value['id'];
        }
        return $result;
    }

    private function getValueStyle($values)
    {
        $result = [];
        $result['multi'] = [];
        $result['single'] = [];
        $result['text_field'] = [];
        foreach ($values as $value) {
            $specification = $value['specification'];
            if (Specification::isMultipleValue($specification)) {
                $result['multi'] = $this->getMultiSpecification($result, $value);
            } elseif (Specification::isTextField($specification)) {
                $result['text_field'][] = $this->pruneValue($value);
            } else {
                $result['single'][] = $this->pruneValue($value);
            }
        }
        return $this->reIndexMultiFieldsArray($result);

    }

    private function reIndexMultiFieldsArray($result)
    {
        $multiFieldsReIndex = (array_values($result['multi']));
        unset($result['multi']);
        $result['multi'] = $multiFieldsReIndex;
        return $result;
    }

    private function getMultiSpecification($result, $value)
    {
        $specification = $value['specification'];
        if ($this->resultHasThisSpecification($result, $specification)) {
            $result['multi'][$specification['id']]['value_id'][] = $value['pivot']['value_id'];
        } else {

            $result['multi'][$value['specification']['id']] = [
                'specification_id' => $value['specification_id'],
                'value_id' => [
                    $value['pivot']['value_id']
                ]

            ];
        }
        return $result['multi'];
    }

    private function resultHasThisSpecification($result, $specification)
    {
        return isset($result['multi'][$specification['id']]);
    }

    private function pruneValue($value)
    {
        return [
            'specification_id' => $value['specification_id'],
            'name' => $value['name'],
            'value_id' => $value['pivot']['value_id']
        ];
    }

    private function changeSpecialExpirationValue(&$specialConditions)
    {
        foreach ($specialConditions as &$condition) {
            $expirationDate = Carbon::parse($condition['expiration']);
            $diffDays = Carbon::now()->setTime(0, 0)->diffInDays($expirationDate, false);
            $condition['expiration'] = $diffDays < 0 ? 0 : $diffDays;
        }
    }

    public function update($updatedInfo, $productId)
    {
        $product = Product::findOrFail($productId);
        DB::transaction(function () use ($product, $updatedInfo) {
            $this->updateProduct($updatedInfo, $product);
            $this->updatePictures($updatedInfo, $product);
            $this->updateValues($updatedInfo, $product);
            $this->updateBrandAndCategory($updatedInfo, $product);
            $this->setProductToPreConfirmed($product);
        });
    }

    private function updateProduct($updatedInfo, $product)
    {
        foreach ($this->productAttrFunctionMap as $attrName => $function) {
            if (strcmp($product->{$attrName}, $updatedInfo[$attrName]))
                $this->$function($product, $attrName, $updatedInfo[$attrName]);
        }
        $product->save();
    }

    private function generalUpdateAttribute($object, $attrName, $newValue)
    {
        $newValue = $this->setValueToNullIfEmpty($newValue);
        $object->{$attrName} = $newValue;
    }

    private function updatePrice($product, $attrName, $newValue)
    {
        $savePrice = new SavePrice();
        $savePrice->save($product, $product->current_price);
        $this->generalUpdateAttribute($product, $attrName, $newValue);
    }

    private function updateBrandAndCategory($updatedInfo, $product)
    {
        $oldCategoryId = $product->category_id;
        $oldBrandId = $product->brand_id;
        $product->category_id = $updatedInfo['category_id'];
        if ($this->brandIsNull($updatedInfo['brand_id'])) {
            $this->setBrandIdToNull($product, $oldBrandId);
        } else {
            $this->updateNewBrandCategoryAttributes($updatedInfo, $product);
        }
        $this->removeCategoryBrandRelationIfNeccessary($oldCategoryId, $oldBrandId);
    }

    private function updatePictures($updatedInfo, Product $product)
    {
        $store = $product->store->user;
        $newPictures = $updatedInfo['pictures'];
        $newPics = $this->findNewPictures($newPictures);
        if (!empty($newPics)) {
            (new PictureHandler())->storePicture($newPics, $product, $store);
        }
    }

    private function findNewPictures($pictures)
    {
        $newPics = [];
        foreach ($pictures as $picture) {
            if (strpos($picture['path'], 'temp') !== false)
                $newPics[] = $picture;
        }
        return $newPics;
    }

    private function getPictureByType($picType, $pictureArray)
    {
        for ($i = 0; $i < count($pictureArray); $i++) {
            if (!strcmp($pictureArray[$i]['type'], $picType))
                return $pictureArray[$i];
        }
        return null;
    }

    public function updateSpecialConditions($updatedInfo, $product)
    {
        $array = [];
        foreach (SpecialCondition::$specialTypes as $specialType) {
            $productCondition = $this->findNewConditionByType($product->special_conditions()->get(), $specialType);
            $newCondition = $this->findOldConditionByType($updatedInfo['special'], $specialType);
            if ($productCondition != null && $newCondition != null) {
                // update condition
                foreach ($this->specialAttrFunctionMap as $attrName => $function) {
                    if (strcmp($productCondition->{$attrName}, $newCondition[$attrName])) {
                        if ($productCondition->status == SpecialCondition::AVAILABLE)
                            $this->$function($productCondition, $attrName, $newCondition[$attrName]);
                        else
                            $array[] = new SpecialCondition($newCondition);
                    }
                }
                $productCondition->save();
            } elseif ($productCondition == null && $newCondition != null) {
                //create new condition
                $array[] = new SpecialCondition($this->changeExpirationStyleIfNoExpirationConstraint($newCondition));
            } elseif ($productCondition != null && $newCondition == null) {
                //delete product condition
                $productCondition->delete();//TODO : inja ham mitoonam ba detach kar konam ke har bar delete seda zade nashe (mesle value)
            }
        }
        if (count($array) > 0)
            $product->special_conditions()->saveMany($array);
    }

    private function changeExpirationStyleIfNoExpirationConstraint($special)
    {
        $special['expiration'] = ProductFactory::expirationChecker($special['expiration']);
        return $special;
    }

    private function expirationUpdate($specialCondition, $attrName, $newValue)
    {
        $newValue = ProductFactory::expirationChecker($newValue);
        $specialCondition->{$attrName} = $newValue;

    }

    private function findOldConditionByType($specials, $specialType)
    {
        if ($specials == null)
            return null;
        foreach ($specials as $special) {
            if (!strcmp($special['type'], $specialType))
                return $special;
        }
    }

    private function findNewConditionByType($specials, $specialType)
    {
        if ($specials == null)
            return null;
        foreach ($specials as $special) {
            if (!strcmp($special['type'], $specialType) and $special['status'] == SpecialCondition::AVAILABLE) {
                return $special;
            }
        }
    }

    private function updateColors($updatedInfo, $product)
    {
        if (array_key_exists('colors', $updatedInfo)) {
            $product->colors()->sync($updatedInfo['colors']);
        }
    }

    private function updateValues($updatedInfo, $product)
    {
        $this->syncValues($updatedInfo['values'], $product);

    }

    private function setProductToPreConfirmed(Product $product)
    {
        $product->update(['confirmation_status' => Product::PRE_CONFIRMATION]);
    }

    public static function setSpecialExpirationStatus()
    {
        SpecialCondition::where('expiration', '<', Carbon::now())
            ->update(['status' => SpecialCondition::EXPIRED]);
    }

    private function syncValues($values, $product)
    {
        $valueIds = [];
        $singleValueIds = array_key_exists('single', $values) ? $this->getSingleValueIds($values['single']) : [];
        $multiValueIds = array_key_exists('multi', $values) ? $this->getMultiValueIds($values['multi']) : [];
        $textValueIds = array_key_exists('text_field', $values) ? $this->getTextValueIds($values['text_field'], $product) : [];
        $valueIds = array_merge($singleValueIds, $multiValueIds, $textValueIds);
        $product->values()->sync($valueIds);
        SpecificationController::updateElastic($product->category_id);
    }

    private function getSingleValueIds($single)
    {
        $ids = [];
        foreach ($single as $updatedSingleValue) {
            $ids[] = $updatedSingleValue['value_id'];
        }
        return $ids;

    }

    private function getMultiValueIds($multi)
    {
        $values = [];
        foreach (array_keys($multi) as $key) {
            $values = array_merge($multi[$key]['value_id'], $values);
        }
        return $values;
    }

    private function getTextValueIds($text_field, $product)
    {
        $ids = [];
        foreach ($text_field as $updatedTextValue) {
            if ($this->textValueEntered($updatedTextValue)) {
                $ids = $this->updateOrCreateTheValue($updatedTextValue, $ids);
            } else {
                $this->deleteValueIfHasBeenRemoved($product, $updatedTextValue);
            }
        }
        return $ids;
    }

    private function handleCategoryBrandRelation($product, $brand_id)
    {

    }


    /**
     * @param $brandId
     * @return bool
     */
    private function brandIsNull($brandId)
    {
        return empty($brandId);
    }

    /**
     * @param $product
     * @param $oldBrandId
     */
    private function setBrandIdToNull($product, $oldBrandId)
    {
        $product->brand()->dissociate($oldBrandId);
        $product->save();
    }

    /**
     * @param $updatedInfo
     * @param $product
     */
    private function updateNewBrandCategoryAttributes($updatedInfo, $product)
    {
        $product->brand_id = $updatedInfo['brand_id'];
        $product->save();
        Brand::find($updatedInfo['brand_id'])->categories()->sync([$updatedInfo['category_id']], false);
    }

    /**
     * @param $oldCategoryId
     * @param $oldBrandId
     */
    private function removeCategoryBrandRelationIfNeccessary($oldCategoryId, $oldBrandId)
    {
        $count = Product::where('category_id', '=', $oldCategoryId)->where('brand_id', '=', $oldBrandId)->count();
        if ($count == 0 and !empty($oldBrandId)) {
            Brand::find($oldBrandId)->categories()->detach($oldCategoryId);
        }
    }

    /**
     * @param $newValue
     * @return null
     */
    private function setValueToNullIfEmpty($newValue)
    {
        if (!strcmp($newValue, ''))
            $newValue = null;
        return $newValue;
    }

    /**
     * @param $product
     * @param $updatedTextValue
     * @return mixed
     */
    private function deleteValueIfHasBeenRemoved($product, $updatedTextValue)
    {
        if (isset($updatedTextValue['value_id'])) {
            Value::find($updatedTextValue['value_id'])->delete();
        } else {
            $deletedValue = $product->values->where('specification_id', '=', $updatedTextValue['specification_id'])->first();
            if (!is_null($deletedValue)) {
                $deletedValue->delete();
            }
        }
    }

    /**
     * @param $updatedTextValue
     * @param $ids
     * @return array
     */
    private function updateOrCreateTheValue($updatedTextValue, $ids)
    {
        if (isset($updatedTextValue['value_id'])) {
            Value::find($updatedTextValue['value_id'])->update(['name' => $updatedTextValue['name']]);
            $ids[] = $updatedTextValue['value_id'];
        } else {
            $newValue = Value::create($updatedTextValue);
            $ids[] = $newValue->id;
        }
        return $ids;
    }

    /**
     * @param $updatedTextValue
     * @return bool
     */
    private function textValueEntered($updatedTextValue)
    {
        return !empty($updatedTextValue['name']);
    }
}