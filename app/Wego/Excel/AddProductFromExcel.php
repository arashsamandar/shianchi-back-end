<?php
namespace Wego\Excel;

use App\Brand;
use App\Category;
use App\Http\Controllers\ProductController;
use App\Product;
use App\ProductDetail;
use App\Specification;
use App\User;
use App\Value;
use App\Warranty;
use http\Env\Response;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use PHPExcel_IOFactory;
use PHPExcel_Settings;
use Wego\Helpers\PersianUtil;
use Wego\Product\ProductFactory;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 27/12/16
 * Time: 18:03
 */
class AddProductFromExcel
{

    protected $request;

    protected $index = 0;
    protected $importedFileName;
    protected $storeId;
    protected $categoryId , $offset , $size;

    public function storeImage($imageId)
    {
        //todo put all downloaded pictures on the server in public path in the folder final-digikala and subfolder pic
        $baseFilePath = public_path() . "/".'final-digikala'."/pic/" . trim($imageId) . '.jpg';
        $savePath = "";
        $paths = ["390", "150"];
        $imageSize = ['390' => 390, '150' => 150];
        $originalPictureBasePath = "";
        if (file_exists($baseFilePath)) {
            $outFileBase = '/wego/store/store'.$this->storeId.'/product/temp/';
            $originalPictureBasePath = $outFileBase  . 'original' . '_' . trim($imageId) . '.jpg';
            $img = Image::make($baseFilePath);
            //$img->resizeCanvas(400, 400);
            $originalPicturePath = public_path().$originalPictureBasePath;
            if(!File::exists(public_path($outFileBase)))
                File::makeDirectory(public_path($outFileBase ),0755,true);
            $img->save($originalPicturePath);

            foreach ($paths as $path) {

                $savePath = $outFileBase  . $path . '_' . trim($imageId) . '.jpg';
                $img = Image::make($originalPicturePath);
                $img->resize($imageSize[$path], $imageSize[$path]);
                $img->save(public_path().$savePath);
            }
            $this->request[$this->index]['pictures'][] = ['type' => 0, 'path' => $originalPictureBasePath];
            return true;
        } else {
            return false;
        }
//        $this->request[$this->index]['pictures'][] = ['type' => 0, 'path' => $originalPictureBasePath];
    }

    public function setImportedFileName($name)
    {
        $this->importedFileName = $name;
        return $this;
    }

    public function setCategoryId($categoryId)
    {
        $this->categoryId =$categoryId;
        return $this;
    }

    public function setOffsetAndSize($offset,$size)
    {
        $this->offset = $offset;
        $this->size = $size;
        return $this;
    }

    private function getImportedFilePath()
    {
        //todo put all crawled excels (with adjusted column names) in the final-digikala folder
        //todo : in fact you should have a folder in final-digikala named "pic" and excel files also must be in final-digikala folder
        // OK
        return public_path().'/'.'final-digikala'.'/'.$this->importedFileName.'.xls';
    }
    public function getCategorySpecifications($categoryPath)
    {
        return Category::searchByQuery(['term' => ['id' => $categoryPath]]);
    }

    public function getCategorySpecificationsByPath($categoryPath)
    {
        return Category::searchByQuery(['term' => ['path' => $categoryPath]]);
    }

    public function getIndex($items, $searchField, $key = 'name')
    {
        $searchCollection = array_column($items, $key);
        return array_search($searchField, $searchCollection);
    }

    public function isSpecificationTextField($specification)
    {
        return $specification['is_text_field'] == 1;
    }

    public function getSpecification($categorySpecifications, $specificationName)
    {
        $specifications = $categorySpecifications['hits'][0]['_source']['specifications'];

        $specIndex = $this->getIndex($specifications, $specificationName);
        if ($specIndex === false)
            return [];
        return $specifications[$specIndex];
    }

    public function add($valueName, $categorySpecifications, $specificationName)
    {
        $valueName = preg_replace("/‌/", " ",$valueName);
        $valueName = PersianUtil::toStandardPersianString($valueName);
        $specification = $this->getSpecification($categorySpecifications, $specificationName);
        if (count($specification) === 0 || is_null($valueName) || empty(trim($valueName)))
            return;
        $specId = $specification['id'];
        if ($this->isSpecificationTextField($specification)) {


            $this->request[$this->index]['values']['text_field'][] = ['specification_id' => $specId, 'name' => $valueName];

            return;
        } elseif ($this->isSingleValue($specification)) {
            $value=Value::where('name',trim($valueName))->where('specification_id',$specId)->first();
            if (is_null($value)){
                Value::create(['name'=>trim($valueName) , 'specification_id'=>$specId]);
                $categoryId = Specification::find($specId)->category->id;
                Category::where('id',$categoryId)->elastic()->addToIndex();
                sleep(1);
                $categorySpecifications=$this->getCategorySpecifications($categorySpecifications['hits'][0]['_source']['id'])->getHits();
                $specification = $this->getSpecification($categorySpecifications, $specificationName);
            }
            $valueIndex = $this->getIndex($specification['values'], $valueName);

            if ($valueIndex === false)
                return;
            $valueId = $specification['values'][$valueIndex]['id'];

            $this->request[$this->index]['values']['single'][] = ['specification_id' => $specId, 'value_id' => $valueId];
        } else {
            if (!empty($valueName)){
                $valueIds = [];
                $valueName = str_replace('¡','،',$valueName);
                $values = explode('،',$valueName);
                foreach ($values as $value){
                    $valueId=null;
                    $val=Value::where('name',trim($value))->where('specification_id',$specId)->first();
                    if (is_null($val)){
                        Value::create(['name'=>trim($value) , 'specification_id'=>$specId]);
                        $categoryId = Specification::find($specId)->category->id;
                        Category::where('id',$categoryId)->elastic()->addToIndex();
                        sleep(1);
                        $categorySpecifications=$this->getCategorySpecifications($categorySpecifications['hits'][0]['_source']['id'])->getHits();
                        $specification = $this->getSpecification($categorySpecifications, $specificationName);
                    }
                    $valueIndex = $this->getIndex($specification['values'], trim($value));
                    if ($valueIndex !== false) {
                        $valueId = $specification['values'][$valueIndex]['id'];
                        $valueIds[] = $valueId;
                    }
                }
                $valueIds = array_unique($valueIds);
                if (empty($valueIds))
                    return;
                $this->request[$this->index]['values']['multi'][] = ['specification_id' => $specId, 'value_id' => $valueIds];
            }
        }
    }

    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }
    public function load()
    {
//        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify($this->getImportedFilePath());
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load($this->getImportedFilePath());
        $objWorksheet = $xl->getActiveSheet();
        $lastColumn = $objWorksheet->getHighestDataColumn();
        $lastRow = $objWorksheet->getHighestDataRow();
        foreach($objWorksheet->getRowIterator() as $rowIndex => $workSheet_row) {
            // Convert the cell data from this row to an array of values
            //    just as though you'd retrieved it using fgetcsv()
            if ($rowIndex ==1){
                continue;
            };
            if ($rowIndex < $this->offset*$this->size){
                continue;
            }
            if ($rowIndex >= ($this->offset+1)*$this->size){
                break;
            }
            if ($rowIndex > $lastRow){
                break;
            }
            $headings = $objWorksheet->rangeToArray('A1:' . $lastColumn . 1,
                NULL,
                TRUE,
                FALSE);
            $array = $objWorksheet->rangeToArray('A'.$rowIndex.':'.$lastColumn.$rowIndex);
            $row = (array_combine($headings[0],$array[0]));
            if(is_null($row['image_id']))
                    return;

            if(!$this->storeImage($row['image_id'])){
                continue;
            }
            if (array_key_exists('category_id',$row)){
                $categorySpec = $this->getCategorySpecificationsByPath($row['category_id'])->getHits();
            } else {
                $categorySpec = $this->getCategorySpecifications($this->categoryId)->getHits();
            }
            dd($categorySpec); // bebin oon aval ke man too URL zadam ino eshtebahee ham hamin error ro daad , taa gozashtamesh tooye postman dorost sho .
            // alan nemidonam cheshe !!!
            $this->addCategoryId($categorySpec);
            $attributes = ['weight', 'current_price', 'quantity', 'persian_name', 'english_name', 'key_name', 'length',
                'width', 'height',  'description', 'brand', 'colors', 'wego_coin_need', 'warranty_name', 'warranty_text','وزن'];
            $specifications = array_column($categorySpec['hits'][0]['_source']['specifications'], 'name');
//            $nullAttributes = ['special' => []];
//            $row['current_price'] =  $row['current_price'] ;
//            foreach ($nullAttributes as $key => $value) {
//                $this->setNullAttributes($key, $value);
//            }
            if(empty($row['english_name'])){
                $row['english_name'] = "";
            }
            foreach ($specifications as $specification){
                if (array_key_exists($specification,$row)) {
                    $this->add($row[$specification], $categorySpec, $specification);
                }
            }
            foreach ($attributes as $attribute) {
                if (array_key_exists($attribute,$row)) {
                    $this->addAttribute($attribute, $row[$attribute]);
                }
            }
            $this->request[$this->index]['uid'] = trim($row['image_id']);
            if (!isset($this->request[$this->index]['values']['single']))
                $this->request[$this->index]['values']['single'] = [];
            if (!isset($this->request[$this->index]['values']['multi']))
                $this->request[$this->index]['values']['multi'] = [];
            if (!isset($this->request[$this->index]['values']['text_field']))
                $this->request[$this->index]['values']['text_field'] = [];
            if (!isset($this->request[$this->index]['quantity']))
                $this->request[$this->index]['quantity'] = empty($this->request[$this->index]['current_price'])? 0: 10 ;
            if (!isset($this->request[$this->index]['weight']))
                $this->request[$this->index]['weight'] =600;
            $pr = Product::where('persian_name',PersianUtil::toStandardPersianString($row['persian_name']))->get()->first();
            if(!is_null($pr)){
                unset($this->request[$this->index]);
                dump('duplicate found');
                $this->index--;
            }
            $this->index++;
        }
//        Excel::selectSheetsByIndex(0)->load($this->getImportedFilePath() , function ($reader) {
//
//            $reader->each(function ($row) {
//                if(is_null($row['image_id']))
//                    return;
//
//                $this->storeImage($row['image_id']);
//                $categorySpec = $this->getCategorySpecifications($this->categoryId)->getHits();
//                $this->addCategoryId($categorySpec);
//                $attributes = ['weight', 'current_price', 'quantity', 'persian_name', 'english_name', 'key_name', 'length',
//                    'width', 'height',  'description', 'brand', 'colors', 'wego_coin_need', 'warranty_name', 'warranty_text'];
//                $specifications = array_column($categorySpec['hits'][0]['_source']['specifications'], 'name');
//                $nullAttributes = ['special' => []];
//                //$row['current_price'] = $row['current_price'] / 10;
//                foreach ($nullAttributes as $key => $value) {
//                    $this->setNullAttributes($key, $value);
//                }
//
//                foreach ($specifications as $specification) {
//                    $this->add($row[$specification], $categorySpec, $specification);
//                }
//                foreach ($attributes as $attribute) {
//                    if (array_key_exists($attribute,$row->toArray())) {
//                        $this->addAttribute($attribute, $row[$attribute]);
//                    }
//                }
//                if (!isset($this->request[$this->index]['values']['single']))
//                    $this->request[$this->index]['values']['single'] = [];
//                if (!isset($this->request[$this->index]['values']['multi']))
//                    $this->request[$this->index]['values']['multi'] = [];
//                if (!isset($this->request[$this->index]['values']['text_field']))
//                    $this->request[$this->index]['values']['text_field'] = [];
//                $this->index++;
//            });
//        });
        $user = User::where('userable_id', $this->storeId)
            ->where('userable_type', 'App\Store')->first();

        $productFactory = new ProductFactory();
        for ($i = 0; $i < count($this->request); ++$i) {
            if (!empty(trim($this->request[$i]['warranty_name'])) && trim($this->request[$i]['warranty_name']) != "سرویس ویژه دیجی کالا: ۷ روز تضمین تعویض کالا") {
                $warrantyName = PersianUtil::toStandardPersianString(trim($this->request[$i]['warranty_name']));
                $warranty = Warranty::firstOrCreate(['warranty_name' => $warrantyName]);
                $warrantyId = $warranty->id;
            } else {
                $warrantyId = 1;
            }
            return response("nashode");
            $product = $productFactory->handle($this->request[$i], $user);
            ProductDetail::create(['product_id'=>$product->id,'warranty_id'=>$warrantyId,'store_id'=>$this->storeId,
                'quantity'=>$this->request[$i]['quantity'], 'current_price'=> $this->request[$i]['current_price'],
                'uid'=>$this->request[$i]['uid']]);
            (new Product())->setToConfirmed($product->id);

        }
    }

    private function setNullAttributes($key, $value)
    {
        $this->request[$this->index][$key] = $value;
    }

    private function addCategoryId($categorySpec)
    {
        $this->request[$this->index]['category_id'] = $categorySpec['hits'][0]['_id'];
    }

    private function addAttribute($attribute, $attributeValue)
    {
        if($attribute === 'brand')
        {
            $attribute ='brand_id';
            $attributeValue = self::getBrand($attributeValue);
        } elseif($attribute === 'colors') {
            $tmp = [];
            array_push($tmp, self::getColor($attributeValue));
            $attributeValue = $tmp;
        } elseif ($attribute === "length" || $attribute === 'width' || $attribute === 'height'){
            if(empty($attributeValue)){
                $attributeValue = 0;
            }
        } elseif (trim($attribute)=='وزن'){
            $attribute = 'weight';
        }
        $this->request[$this->index][$attribute] = $attributeValue;
    }

    private static function getBrand($attributeValue)
    {
        if (!empty($attributeValue)) {
            $brandNameAndId = explode('_', $attributeValue);
            $brand = Brand::firstOrCreate(['persian_name' => $brandNameAndId[1], 'english_name' => $brandNameAndId[0]]);
            return $brand->id;
        } else{
            return null;
        }
    }

    private static function getColor($attributeValue)
    {
        $colorNameAndId = explode('_', $attributeValue);
        return $colorNameAndId[0];
    }

    private function isSingleValue($specification)
    {
        return $specification['multi_value'] == 0;
    }


}