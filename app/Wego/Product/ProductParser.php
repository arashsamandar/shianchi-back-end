<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 01/05/16
 * Time: 16:46
 */

namespace Wego\Product;

use App\Product;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wego\Helpers\PersianUtil;

class ProductParser
{
    const FOR_BUY_SPECIFICATIONS = 1;
    const GENERAL_SPECIFICATION = 0;
    const IMPORTANT_SPECIFICATIONS = 1;
    protected $productJson = [];

    function __construct($productJson)
    {
        $this->productJson = $productJson;
    }

    public function parse()
    {
        if ($this->productJson['hits']['total'] < 1)
            throw new NotFoundHttpException;
        $product = &$this->productJson['hits']['hits'][0]['_source'];
        $importantProductItems = $this->getProductItems($product);
        $forBuySpecifications = $this->getSpecification($product, self::FOR_BUY_SPECIFICATIONS);
        $generalSpecifications = $this->getSpecification($product, self::GENERAL_SPECIFICATION);
        $importantSpecifications = $this->getSpecification($product, null ,self::IMPORTANT_SPECIFICATIONS);
        $generalSpecifications = $this->appendWightAndDimensions($generalSpecifications, $product);
        $pictures = $this->getProductPictures($product);
        $colors = $this->getColors($product);
        $categories = $this->prepareBreadcrumb($product["category"]["english_path"], $product["category"]["path"]);
        $specialConditions = $this->getSpecialConditions($product);
//        $store = $this->getStore($product);
        $defaultDetail = $this->getDefaultDetail($product);
//        $warranties = $this->getWarranties($product);
        //$phone = $this->getphone($product);
        $score = DB::table('product_score')->where('product_id', '=', $product['id'])->where('score_title_id',1)->select(DB::raw('count(score) as total'))
            ->groupBy('score_title_id')->first();
        return [
            "product" => $importantProductItems,
            "importantSpecifications" => $importantSpecifications,
            "generalSpecifications" => $generalSpecifications,
            "forBuySpecifications" => $forBuySpecifications,
            "pictures" => $pictures,
            "colors" => $colors,
            "breadcrumb" => $categories,
            "specialConditions" => $specialConditions,
            "default_details"=>$defaultDetail,
            "brand" => $product['brand'],
            "score" => ['total_score' => !is_null($score)? $score->total : 1 , 'average_score' =>$product['average_score']]
        ];
    }


    private function catMap($persianPath, $englishPath)
    {
        return [
            "persian_path" => $persianPath,
            "english_path" => $englishPath
        ];
    }

    /**
     * @param $product
     * @param int $isForBuy
     * @return array
     */
    private function getSpecification($product, $isForBuy = 0 , $isImportant = null)
    {
        if (empty($isImportant)) {
            $specifications = array_filter($product["values"], function ($item) use ($isForBuy) {
                return $item['specification']['for_buy'] === $isForBuy;
            });
        } else {
            $specifications = array_filter($product["values"], function ($item) use ($isImportant) {
                return $item['specification']['important'] === $isImportant;
            });
            $importantSpecificationsField = array_map([$this, "getImportantSpecificationsField"], $specifications);
            $collections = collect($importantSpecificationsField)->groupBy('name');
            return $collections->toArray();
        }
        //dd($specifications);
        $importantSpecificationsField = array_map([$this, "getImportantSpecificationsField"], $specifications);
        if (empty($importantSpecificationsField))
            return null;

        $collections = collect($importantSpecificationsField)->groupBy('title')->transform(function ($value, $key) {
            return $value->groupBy('name');
        });

        return $collections->toArray();

    }

    //TODO: WRONGGGG
    private function getImportantSpecificationsField($item)
    {
        return [
            "id" => $item["id"],
            "spec_id" => $item["specification"]["id"],
            "name" => $item["specification"]["name"],
            "value" => $item["name"],
            "title" => $item["specification"]["title"]["title"]
        ];
    }

    private function getProductPictures($product)
    {
        return array_map([$this, "withoutTimeStamps"], $product['pictures']);
    }

    private function withoutTimeStamps($pictures)
    {
        return array_except($pictures, ["created_at", "updated_at"]);
    }

    private function getColors($product)
    {
        return array_map([$this, "getImportantColorsItems"], $product['colors']);
    }

    private function getImportantColorsItems($color)
    {
        return [
            "id" => $color["id"],
            "persian_name" => $color["persian_name"],
            "english_name" => $color["english_name"],
            "code" => $color["code"]
        ];
    }

    private function getImportantStoresItems($store)
    {
        return [
            "id" => $store["id"],
            "persian_name" => $store["user"]["name"],
        ];
    }

    public function prepareBreadcrumb($english_path, $path)
    {
        $fa_cat = explode('_', $path);
        $en_cat = explode('_', $english_path);

        return array_map([$this, 'catMap'], $fa_cat, $en_cat);
    }

    private function getStore($product)
    {
//        $importantStoreItems = array_only($product["store"], ["about_us", "english_name", "bazaar", "information", "user.name", "id", "url"]);
        $importantStoreItems["persian_name"] = $product["store"]["user"]["name"];
        $importantStoreItems["path"] = $this->getStorePictures($product["store"]["pictures"]);
        return $importantStoreItems;
    }

    public static function getStorePictures($storePictures = [])
    {
        $logoPictures = array_first($storePictures);
        if (isset($logoPictures["path"]))
            return $logoPictures["path"];
        return null;
    }

    private function getSpecialConditions($product)
    {
        return array_map([$this, "withoutTimeStamps"], $product["special_conditions"]);
    }

    private function getProductItems($product)
    {
        $productItems = array_only($product, [
            'id', 'english_name', 'persian_name','quantity',
            'description', 'current_price', 'view_count' , 'key_name'
        ]);
        $productItems['url'] = Product::url($productItems['id'],
            $productItems['english_name'],$productItems['persian_name']);
        return $productItems;
    }

    private function getPhone($product)
    {
        return
            PersianUtil::to_persian_num($product['store']['store_phones'][0]['prefix_phone_number']) .
            '-' .
            PersianUtil::to_persian_num($product['store']['store_phones'][0]['phone_number']);
    }

    private function appendWightAndDimensions($generalSpecifications, $product)
    {
        if ($this->productHasDimension($product)) {
            $generalSpecifications["مشخصات ظاهری"]["ارتفاع(سانتی متر)"][] = ['value' => $product["height"]];
            $generalSpecifications["مشخصات ظاهری"]["طول(سانتی متر)"][] = ['value' => $product["length"]];
            $generalSpecifications["مشخصات ظاهری"]["عرض(سانتی متر)"][] = ['value' => $product["width"]];
        }
        $generalSpecifications["مشخصات ظاهری"]["وزن(گرم)"][] = ['value' => $product["weight"]];
        return $generalSpecifications;
    }

    /**
     * @param $product
     * @return bool
     */
    private function productHasDimension($product)
    {
        return ($product["height"] != 0 or $product["length"] != 0 or $product["width"] != 0);
    }

    private function getDefaultDetail($product)
    {
        return $product['default_details'];
    }

    private function getWarranties($product)
    {
        return array_map([$this, "withoutTimeStamps"], $product['warranties']);
    }

}
