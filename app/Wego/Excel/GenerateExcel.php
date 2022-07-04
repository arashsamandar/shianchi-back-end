<?php
namespace Wego\Excel;

use App\Brand;
use App\Category;
use App\Color;
use App\Wego\Excel\Excel;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CategoryController;
use App\Repositories\Category\CategoryRepositoryEloquent;
use App\Repositories\TitleRepositoryEloquent;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_IOFactory;
use PHPExcel_Settings;
use PHPExcel_Writer_Excel2007;
use Carbon\Carbon;


/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 30/12/16
 * Time: 14:47
 */
class GenerateExcel extends Excel
{
    const CATEGORY_PATH_CELL = 'N';
    const TYPE_BASE_ON_CATEGORY_CELL = 'O';

    const KIND = 'نوع';

    protected $category;
    protected $categpryPersianPath;
    protected $categorySpec;
    protected $validData = [[]];



    public function export()
    {
        $leafCategoriesWithKindSpecification = $this->transform($this->getCategoryWithKindSpecification());

        $this->addConfigSheet($leafCategoriesWithKindSpecification);
        $this->addComboBoxColumnToSheet();

        $this->save();

    }

    public function exportNew()
    {

        $leafWithKind = $this->transform($this->getCategoryWithKindSpecification());

        $leafWithKind = array_merge($leafWithKind, $this->transformColor($this->getAllColors()));

        $leafWithKind = array_merge($leafWithKind, $this->transformBrand($this->getAllBrands()));

        $leafWithKind = array_merge($leafWithKind, $this->transformSpec($this->categorySpec['hits'][0]['_source']['specifications']));

        $this->addConfigSheet($leafWithKind);

        $this->fillSheet();

        $this->addComboBoxColumnToSheetNew();

        $this->save();

    }



    /**
     * @param mixed $categoryId
     * @return GenerateExcel
     */
    public function setCategoryById($categoryId)
    {
        $this->category = Category::findOrFail($categoryId)['english_path'];
        return $this;
    }

    /**
     * @param mixed $category
     * @return GenerateExcel
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @param mixed $categoryId
     * @return GenerateExcel
     */
    public function getCategorySpecifications($categoryId)
    {
        $this->categorySpec = Category::searchByQuery(['term' => ['id' => $categoryId]])->getHits();
        return $this;
    }


    /**
     * @return string
     */
    private function getOutputFilePath()
    {
        return public_path() . '/' . Carbon::now() . '___' . $this->getCategoryPersianName() . '.xlsx';
    }

    private function fillSheet()
    {
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objPHPExcel->getActiveSheet()->setRightToLeft(true);

        $coordinate = 'A';
        $index = 1;

        $specifications = ['category_id', 'image_id', 'persian_name', 'english_name', 'key_name', 'brand_id', 'colors' ,
            'weight','current_price', 'quantity', 'length', 'width', 'height',  'description',
            'wego_coin_need', 'warranty_name', 'warranty_text'];
        $specifications = array_merge($specifications, array_column($this->categorySpec['hits'][0]['_source']['specifications'], 'name'));

        foreach ($specifications as $specification) {
            $this->setCellValue($coordinate . $index, $specification);
            ++$coordinate;
        }

    }

    private function addConfigSheet($leafCategoriesWithKindSpecification)
    {
        $this->addSheet();
        $this->validData = [[]];
        $indexValidData = 0;

        $coordinate = 'A';
        $index = 1;

        foreach ($leafCategoriesWithKindSpecification as $categorySpecification) {
            $this->setCellValue($coordinate . $index, $categorySpecification['path']);
            ++$index;
            foreach ($categorySpecification['values'] as $val) {
                $this->setCellValue($coordinate . $index, $val);
                ++$index;
            }

            $this->addRangeName($coordinate . '2:' . $coordinate . ($index - 1), $categorySpecification['path']);
            $this->addRangeName($coordinate . '2:' . $coordinate . ($index - 1), $coordinate);

            $this->validData[$indexValidData][0] = $categorySpecification['path'];
            $this->validData[$indexValidData][1] = $coordinate;

            $coordinate++;
            $index = 1;
            $indexValidData++;
        }
        $this->addRangeName('A1:' . $coordinate . '1', 'categories');
    }

    private function addComboBoxColumnToSheet()
    {
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $rowCount = $this->objPHPExcel->getActiveSheet()->getHighestRow();
        for ($i = 2; $i <= $rowCount; ++$i) {
            $this->addDataValidationToCell(self::CATEGORY_PATH_CELL . $i, "=categories");
            $this->addDataValidationToCell(self::TYPE_BASE_ON_CATEGORY_CELL . $i, '=INDIRECT($' . self::CATEGORY_PATH_CELL . '$' . $i . ')');
        }
    }

    private function addComboBoxColumnToSheetNew()
    {
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $specs = $this->categorySpec['hits'][0]['_source']['specifications'];


        for ($j = 2; $j <= 50; ++$j) {
            $this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,$j)->setValue($this->getCategoryPersianPath());
        }

        $sizeColumn=$this->objPHPExcel->getActiveSheet()->getHighestDataColumn(0);

        for($i=0, $coordinate = 'A';  $coordinate<=$sizeColumn; ++$i, ++$coordinate){
            if($this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i,1)->getValue()
                == "brand_id") {
                for ($j = 2; $j <= 50; ++$j) {
                    $this->addDataValidationToCell($coordinate . $j, "=brands");
                }
            }
        }

        for($i=0, $coordinate = 'A';  $coordinate<=$sizeColumn; ++$i, ++$coordinate){
            if($this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i,1)->getValue()
                == "colors") {
                for ($j = 2; $j <= 50; ++$j) {
                    $this->addDataValidationToCell($coordinate . $j, "=colors");
                }
            }
        }

        foreach ($specs as $spec) {
            if ($spec['is_text_field'] == 0 && $spec['multi_value'] == 0) {
                for($i=0, $coordinate = 'A';  $coordinate<=$sizeColumn; ++$i, ++$coordinate){
                    if($this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow($i,1)->getValue()
                        == $spec['name']) {
                        $indexSpecName = self::findIndexSpecName($spec['name']);
                        for ($j = 2; $j <= 100; ++$j) {
                            $this->addDataValidationToCell($coordinate . $j, "=".$this->validData[$indexSpecName][1]);
                        }
                    }
                }
            }
        }
    }

    private function save()
    {
        $objWriter = new PHPExcel_Writer_Excel2007($this->objPHPExcel);
        $objWriter->save($this->getOutputFilePath());
    }

    public static function splitFile($file, $splitSize)
    {
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $PHPExcel = PHPExcel_IOFactory::load(public_path() . "/" .'final-digikala/'. $file . '.xls');
        $row = $PHPExcel->getActiveSheet()->getHighestRow();
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCnt = 1;
        $rowCount = 1;
        $fileName = 1;
        $alpha = 0;
        $highestCol = $PHPExcel->getActiveSheet()->getHighestColumn();
        while ($rowCnt < $row) {
            while ($alpha <= PHPExcel_Cell::columnIndexFromString($highestCol)) {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($alpha) . $rowCount, $PHPExcel->getActiveSheet()->getCell(PHPExcel_Cell::stringFromColumnIndex($alpha) . $rowCnt)->getValue());
                $alpha++;
            }
            $alpha = 0;
            $rowCnt++;
            if (($rowCnt % $splitSize) == 0) {
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(public_path() . '/' . $file . '-part' . $fileName . '.xls');
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->setActiveSheetIndex(0);
                $fileName++;
                $rowCount = 1;
                while ($alpha <= $PHPExcel->getActiveSheet()->getHighestColumn()) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($alpha) . $rowCount, $PHPExcel->getActiveSheet()->getCell(PHPExcel_Cell::stringFromColumnIndex($alpha) . $rowCount)->getValue());
                    $alpha++;
                }
                $alpha = 0;
            }
            $rowCount++;
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(public_path() . '/' . $file . '-part' . $fileName . '.xls');
    }

    private function getAllColors()
    {
        return Color::all();
    }

    private function getAllBrands()
    {
        return Brand::all();
    }

    private function getCategoryPersianPath()
    {
        return $this->categorySpec['hits'][0]['_source']['path'];
    }

    private function getCategoryPersianName()
    {
        return $this->categorySpec['hits'][0]['_source']['persian_name'];
    }

    private function getCategoryWithKindSpecification()
    {
        return Category::where('english_path', 'like', '%' . $this->category . '%')
            ->where('isLeaf', 1)
            ->with(['specifications' => function ($query) {
                $query
                    ->where('specifications.name', self::KIND)
                    ->select('specifications.name', 'specifications.id', 'specifications.category_id')
                    ->with(['values' => function ($query) {
                        $query->select('values.name', 'values.id', 'values.specification_id');
                    }]);
            }])->select('categories.id', 'categories.path')->get();
    }

    private function transform($category)
    {
        return $category->map(function ($item) {
            return collect(['path' => $item->path, 'values' => $item->specifications->flatMap(function ($item) {
                return collect($item->values->map(function ($item) {
                    return $item->name;
                }));
            })]);

        })->toArray();
    }

    private function transformSpec($specs)
    {
        $collect = collect([]);
        $index = 0;

        foreach ($specs as $spec)
        {
            if($spec['is_text_field'] == 0 && $spec['multi_value'] == 0)
            {
                $tmp = [];
                foreach ($spec['values'] as $key => $value)
                {
                    array_push($tmp, $value['name']);
                }
                $collect->put($index ,collect(['path' => $spec['name'], 'values' => $tmp]));
                $index++;
            }
        }

        return $collect->toArray();
    }

    private function transformColor($color)
    {
        $collect = collect( ['path' => 'colors', 'values' => $color->map(function ($item) {
            return $item->id.'_'.$item->persian_name;
        })])->toArray();

        return [0 => $collect];
    }

    private function transformBrand($brand)
    {
        $collect = collect([ 'path' => 'brands', 'values' => $brand->map(function ($item){
            return $item->id.'_'.$item->persian_name;
        })])->toArray();

        return [0 => $collect];
    }

    private function findIndexSpecName($specName)
    {
        return array_search($specName, array_column($this->validData, 0));
    }
}