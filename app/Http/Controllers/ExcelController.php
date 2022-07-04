<?php

namespace App\Http\Controllers;

use App\Category;
use App\Order;
use App\OrderProduct;
use App\Product;
use App\ProductDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Settings;
use PHPExcel_Writer_Excel2007;
use Wego\Excel\AddProductFromExcel;
use Wego\Excel\GenerateExcel;
use Wego\Helpers\PersianUtil;

class ExcelController extends ApiController
{

    const BITRON = 420;
    const BRILLIANT = 449;
    const BABYLISS = 664;
    const PROWAVE = 666;
    const BOOK = 488;
    const PERFUME = 459;
    const MATEO = 486;
    CONST FELLER = 434;
    const EXTERNAL_HARD = 465;
    const DIAPER = 327;
    const PAAKSHOOMA = 484;

    public function storeExcel(Request $request)
    {
        $fileName = $request->input('input_file_name');
        $generateExcel = new GenerateExcel($fileName);
        $generateExcel->setCategory('BOOK')->export();
        return $this->respondOk('excel created successfully');

    }

    public function storeExcelForAnyCategory(Request $request)
    {
        $fileName = $request->input('input_file_name');
        $categoryId = $request->input('category_id');
        $generateExcel = new GenerateExcel($fileName);
        $generateExcel->setCategoryById($categoryId)->getCategorySpecifications($categoryId)->exportNew();
        return $this->respondOk('excel created successfully');
    }

    public function importProductFromExcel(Request $request) // this is for importing from excell
    {
        //todo after putting the files in the right path on the server you should call this api it will import the products
        //todo after crawling excel files they have names, when you put the excel file and its pics the name
        $generateExcel = new AddProductFromExcel(); // bebin alan axs haa va excel ro bezaram roo server badesh ke download sho ?
        $fileName = $request->input('input_file_name'); // esme oon file exeli ke crawl shode to gozashti ro server
        $storeID = $request->input('store_id'); // id froshgahee ke gharare kalaha baraye oon vared beshan
        $categoryID = $request->input('category_id'); // id groohe kala too site
        $offset = $request->input('offset'); // az 0 shoroo kon , bad yedoone yedoone ba shell script ya dasti call kon ziyadesh kon
        $size = $request->input('size'); // inam bezaar 10
        try {
            $generateExcel->setStoreId($storeID)->setCategoryId($categoryID)
                ->setImportedFileName($fileName)->setOffsetAndSize($offset, $size)->load();
        }catch (\Exception $e) {
            return response($e->getMessage());
        }

        return $this->respondOk('excel successfully add to product table');
    }

    public function setNotExistingProducts(Request $request)
    {
        $excel = $request->file('file');
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify($excel);
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load($excel);
        $objWorksheet = $xl->getActiveSheet();
        $lastRow = $objWorksheet->getHighestDataRow();
        $offset = $request->offset;
        $startRow = ($offset * 500) + 1;
        $endRow = ($offset + 1) * 500;
        if ($startRow > $lastRow) {
            return;
        }
        if ($endRow > $lastRow) {
            $endRow = $lastRow;
        }
//        $englishNames = $objWorksheet->rangeToArray('A2:' . 'A' . $lastRow);
        $ids = $objWorksheet->rangeToArray('A' . $startRow . ':' . 'A' . $endRow);
        foreach ($ids as $detailId) {
            dump($detailId[0]);
            $product = ProductDetail::findOrFail($detailId[0]);
            $product->quantity = 50;
            $product->save();
            Product::where('id', $product->product_id)->elastic()->addToIndex();
        }
        return $this->respondOk("successful " . Carbon::now()->toDateTimeString());
    }

    public function checkPriceBaseExistStatus(Request $request)
    {
        $excel = $request->file('file');
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify($excel);
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load($excel);
        $objWorksheet = $xl->getActiveSheet();
        $lastRow = $objWorksheet->getHighestDataRow();
        $englishNames = $objWorksheet->rangeToArray('A2:' . 'A' . $lastRow);
        $englishNames = collect($englishNames)->flatten()->toArray();
        $prices = $objWorksheet->rangeToArray('B2:' . 'B' . $lastRow);
        $prices = collect($prices)->flatten()->toArray();
        $combine = array_combine($englishNames, $prices);
        foreach ($englishNames as $englishName) {
            $englishNameq = str_replace(' ', '', $englishName);
            $product = Product::whereRaw("REPLACE(`english_name`,' ','') = \"{$englishNameq}\"")->where('store_id', 92)
                ->orWhere(function ($query) use ($englishNameq) {
                    $query->whereRaw("REPLACE(`key_name`,' ','') = \"{$englishNameq}\"")->where('store_id', 92);
                })->first();
            if (empty($englishNameq)) {
                $product = null;
            }
            if (!is_null($product)) {
                $product->quantity = 0;
                $product->save();
                Product::where('id', $product->id)->elastic()->get()->addToIndex();
            }
        }
        return $this->respondOk("successful " . Carbon::now()->toDateTimeString());
    }

    public function checkPrice(Request $request)
    {
        $excel = $request->file('file');
        $offset = $request->offset;
        $startRow = ($offset * 300) + 1;
        $endRow = ($offset + 1) * 300;
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify($excel);
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load($excel);
        $objWorksheet = $xl->getActiveSheet();
        $lastRow = $objWorksheet->getHighestDataRow();
        if ($startRow < 1) {
            $startRow = 1;
        }
        if ($endRow > $lastRow) {
            $endRow = $lastRow;
        }
        if ($startRow > $lastRow) {
            return $this->respondOk();
        }
        var_dump($startRow, $endRow);
        $digiIds = $objWorksheet->rangeToArray('A' . $startRow . ':' . 'A' . $endRow);
        $digiIds = collect($digiIds)->flatten()->toArray();
        $prices = $objWorksheet->rangeToArray('B' . $startRow . ':' . 'B' . $endRow);
        $prices = collect($prices)->flatten()->toArray();
        $combine = array_combine($digiIds, $prices);
        foreach ($digiIds as $digiId) {
            if (empty($digiId)) {
                $products = null;
            } else {
                $products = ProductDetail::where('uid', $digiId)->get();
            }
            if (!is_null($products)) {
                $productIds = $products->pluck('product_id')->toArray();
                foreach ($products as $product) {
                    if (empty($combine[$digiId])) {
                        $product->quantity = 0;
                        $product->save();
                    } else {
                        $product->updated_at = Carbon::now();
                        if ($product->quantity == 0) {
                            $product->quantity = 10;
                        }
                        if ($product->product->category->name == 'main-mobile-phones') {
                            $product->current_price = $combine[$digiId] * 1.05;
                            $product->save();
                        } elseif (strpos($product->product->persian_name,'هافنبرگ') !== false) {
                            $product->current_price = $combine[$digiId];
                            $product->save();
                        } elseif ($product->Category == "game-console-accessories") {
                            $product->current_price = $combine[$digiId] - 1000;
                            $product->save();
                        } elseif ($product->product->brand_id == self::BRILLIANT) {
                            $product->current_price = $combine[$digiId] + 50000;
                            $product->save();
                        } elseif($product->product->brand_id == self::BITRON){
                            if ($combine[$digiId] < 200000) {
                                $product->current_price = $combine[$digiId] - 3000;
                            } elseif ($combine[$digiId] < 350000) {
                                $product->current_price = $combine[$digiId] - 5000;
                            } elseif ($combine[$digiId] < 460000) {
                                $product->current_price = $combine[$digiId] - 8000;
                            } elseif ($combine[$digiId] < 700000) {
                                $product->current_price = $combine[$digiId] - 12000;
                            } elseif ($combine[$digiId] < 900000) {
                                $product->current_price = $combine[$digiId] - 18000;
                            } elseif ($combine[$digiId] < 1700000) {
                                $product->current_price = $combine[$digiId] - 25000;
                            } elseif ($combine[$digiId] < 2000000) {
                                $product->current_price = $combine[$digiId] - 35000;
                            } else{
                                $product->current_price = $combine[$digiId] - 43000;
                            }
                            $product->save();
                        } elseif ($product->product->brand_id == self::PAAKSHOOMA) {
                            if ($combine[$digiId] < 600000) {
                                $product->current_price = $combine[$digiId] - 8000;
                            } elseif ($combine[$digiId] < 700000) {
                                $product->current_price = $combine[$digiId] - 13000;
                            } elseif ($combine[$digiId] < 800000) {
                                $product->current_price = $combine[$digiId] - 16000;
                            } elseif ($combine[$digiId] < 1500000) {
                                $product->current_price = $combine[$digiId] - 22000;
                            } elseif ($combine[$digiId] < 3500000) {
                                $product->current_price = $combine[$digiId] - 26000;
                            } elseif ($combine[$digiId] < 5000000) {
                                $product->current_price = $combine[$digiId] - 35000;
                            } else{
                                $product->current_price = $combine[$digiId] - 45000;
                            }
                            $product->save();
                        } elseif ($product->product->brand_id == self::BABYLISS || $product->product->brand_id == self::PROWAVE) {
                            $product->current_price = $combine[$digiId] + 20000;
                            $product->save();
                        } elseif ($product->product->brand_id == self::MATEO || $product->product->brand_id == self::FELLER) {
                            $product->current_price = $combine[$digiId] - 2000;
                            $product->save();
                        } elseif ($product->product->category->id == self::BOOK) {
                            $product->current_price = $combine[$digiId];
                            $product->save();
                        } elseif ($product->product->category->id == self::DIAPER) {
                            $product->current_price = $combine[$digiId] + 10000;
                            $product->save();
                        } elseif ($product->product->category->id == self::EXTERNAL_HARD) {
                            if ($combine[$digiId] < 200000) {
                                $product->current_price = $combine[$digiId] - 3000;
                            } elseif ($combine[$digiId] < 350000) {
                                $product->current_price = $combine[$digiId] - 5000;
                            } elseif ($combine[$digiId] < 500000) {
                                $product->current_price = $combine[$digiId] - 9000;
                            } elseif ($combine[$digiId] < 650000) {
                                $product->current_price = $combine[$digiId] - 13500;
                            } elseif ($combine[$digiId] < 800000) {
                                $product->current_price = $combine[$digiId] - 16000;
                            } elseif ($combine[$digiId] < 950000) {
                                $product->current_price = $combine[$digiId] - 19000;
                            } elseif ($combine[$digiId] < 1200000) {
                                $product->current_price = $combine[$digiId] - 22000;
                            } elseif ($combine[$digiId] < 1500000) {
                                $product->current_price = $combine[$digiId] - 25000;
                            } elseif ($combine[$digiId] < 1800000) {
                                $product->current_price = $combine[$digiId] - 28000;
                            } else{
                                $product->current_price = $combine[$digiId] - 30000;
                            }
                            $product->save();
                        } elseif ($product->product->category->id == self::PERFUME) {
                            $product->current_price = $combine[$digiId] - 2000;
                            $product->save();
                        } elseif ($product->product->category->name == 'memory-card') {
                            $product->current_price = $combine[$digiId] - 2000;
                            $product->save();
                        } elseif (substr($product->product->category->english_path, 0, 6) === "HEALTH") {
                            $product->current_price = $combine[$digiId] * 1.2;
                            $product->save();
                        } elseif (substr($product->product->category->english_path, 0, strlen("HOUSE-AND-HOUSEFURNITURE_ACCESSORIES-HOME_TOOLS_ELECTRIC-TOOL")) === "HOUSE-AND-HOUSEFURNITURE_ACCESSORIES-HOME_TOOLS_ELECTRIC-TOOL") {
                            if ($combine[$digiId] < 50000) {
                                $product->current_price = $combine[$digiId] - 2000;
                            } elseif ($combine[$digiId] < 200000) {
                                $product->current_price = $combine[$digiId] - 5000;
                            } elseif ($combine[$digiId] < 400000) {
                                $product->current_price = $combine[$digiId] - 10000;
                            } elseif ($combine[$digiId] < 800000) {
                                $product->current_price = $combine[$digiId] - 20000;
                            } else{
                                $product->current_price = $combine[$digiId] - 30000;
                            }
                            $product->save();
                        } else {
                            if ($combine[$digiId] < 25000) {
                                $product->current_price = $combine[$digiId] - 1000;
                            } elseif ($combine[$digiId] < 100000) {
                                $product->current_price = $combine[$digiId] - 2000;
                            } elseif ($combine[$digiId] < 200000) {
                                $product->current_price = $combine[$digiId] - 4000;
                            } elseif ($combine[$digiId] < 400000) {
                                $product->current_price = $combine[$digiId] - 5000;
                            } elseif ($combine[$digiId] < 1000000) {
                                $product->current_price = $combine[$digiId] - 8000;
                            } elseif ($combine[$digiId] < 2000000) {
                                $product->current_price = $combine[$digiId] - 12000;
                            } elseif ($combine[$digiId] < 5000000) {
                                $product->current_price = $combine[$digiId] - 18000;
                            } else {
                                $product->current_price = $combine[$digiId] - 25000;
                            }
                            $product->save();
                        }
                    }
                }
                Product::whereIn('id', $productIds)->elastic()->addToIndex();
            }
        }
        return $this->respondOk("successful " . Carbon::now()->toDateTimeString());
    }

    public function keepPriceLower(Request $request)
    {
        $excel = $request->file('file');
//        $offset = $request->offset;
        $startRow = 2;
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify($excel);
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load($excel);
        $objWorksheet = $xl->getActiveSheet();
        $lastRow = $objWorksheet->getHighestDataRow();
        dump($startRow, $lastRow);
        $wegoIds = $objWorksheet->rangeToArray('E' . $startRow . ':E' . $lastRow);
        $wegoIds = collect($wegoIds)->flatten()->toArray();
        $prices = $objWorksheet->rangeToArray('D' . $startRow . ':D' . $lastRow);
        $prices = collect($prices)->flatten()->toArray();
        $combine = array_combine($wegoIds, $prices);
        foreach ($wegoIds as $wegoId) {
            $products = ProductDetail::where('product_id', $wegoId)->get();
            if (!is_null($products)) {
                foreach ($products as $product) {
                    if (!empty($combine[$wegoId]) && $combine[$wegoId] < $product->current_price) {
                        $product->current_price = $combine[$wegoId] - 1000;
                        $product->save();
                    }
                }
                Product::where('id', $wegoId)->elastic()->addToIndex();
            }
        }
        return $this->respondOk("successful " . Carbon::now()->toDateTimeString());
    }

    public function checkPriceBaseId(Request $request)
    {
        $excel = $request->file('file');
        $offset = $request->offset;
        $startRow = ($offset * 300) + 1;
        $endRow = ($offset + 1) * 300;
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify($excel);
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load($excel);
        $objWorksheet = $xl->getActiveSheet();
        $lastRow = $objWorksheet->getHighestDataRow();
        if ($startRow < 2) {
            $startRow = 2;
        }
        if ($endRow > $lastRow) {
            $endRow = $lastRow;
        }
        if ($startRow > $lastRow) {
            return $this->respondOk();
        }
        dump($startRow, $endRow);
        $wegoIds = $objWorksheet->rangeToArray('B' . $startRow . ':B' . $endRow);
        $wegoIds = collect($wegoIds)->flatten()->toArray();
        $prices = $objWorksheet->rangeToArray('C' . $startRow . ':C' . $endRow);
        $prices = collect($prices)->flatten()->toArray();
        $combine = array_combine($wegoIds, $prices);
        foreach ($wegoIds as $wegoId) {
            $products = ProductDetail::where('product_id', $wegoId)->get();
            if (!is_null($products)) {
                foreach ($products as $product) {
                    if (empty($combine[$wegoId])) {
                        $product->quantity = 0;
                        $product->save();
                    } else {
//                        if ($product->quantity == 0) {
//                            $product->quantity = 3;
//                        }
                        if ($product->product->category->name == 'main-tablets' || $product->product->category->name == 'laptop' ||
                            $product->product->category->name == 'main-mobile-phones'
                        ) {
                            $product->current_price = $combine[$wegoId] * 1.1;
                            $product->save();
                        } elseif ($product->product->category->name == 'flash-memory' || $product->product->category->name == 'external-hard-disk' || $product->Category == "game-console-accessories") {
                            $product->current_price = $combine[$wegoId] - 1000;
                            $product->save();
                        } elseif ($product->product->category->name == 'memory-card') {
                            $product->current_price = $combine[$wegoId] - 2000;
                            $product->save();
                        } elseif ($product->product->category->name == 'mobile-tablet-accessories-powerbank') {
                            $product->current_price = $combine[$wegoId] < 100000 ? $combine[$wegoId] - 3000 : $combine[$wegoId] - 5000;
                            $product->save();
                        } elseif (substr($product->product->category->english_path, 0, 6) === "HEALTH") {
                            $product->current_price = $combine[$wegoId] < 50000 ? $combine[$wegoId] * .95 : $combine[$wegoId] - 3000;;
                            $product->save();
                        } else {
                            if ($combine[$wegoId] < 25000) {
                                $product->current_price = $combine[$wegoId] - 1000;
                            } elseif ($combine[$wegoId] < 100000) {
                                $product->current_price = $combine[$wegoId] - 5000;
                            } elseif ($combine[$wegoId] < 200000) {
                                $product->current_price = $combine[$wegoId] - 10000;
                            } elseif ($combine[$wegoId] < 400000) {
                                $product->current_price = $combine[$wegoId] - 10000;
                            } elseif ($combine[$wegoId] < 1000000) {
                                $product->current_price = $combine[$wegoId] - 30000;
                            } elseif ($combine[$wegoId] < 2000000) {
                                $product->current_price = $combine[$wegoId] - 40000;
                            } elseif ($combine[$wegoId] < 5000000) {
                                $product->current_price = $combine[$wegoId] - 50000;
                            } else {
                                $product->current_price = $combine[$wegoId] - 60000;
                            }
                            $product->save();
                        }
                    }
                }
                Product::where('id', $wegoId)->elastic()->addToIndex();
            }
        }
        return $this->respondOk("successful " . Carbon::now()->toDateTimeString());
    }

    public function setUids(Request $request)
    {
        $excel = $request->file('file');
        $offset = $request->offset;
        $startRow = ($offset * 300) + 1;
        $endRow = ($offset + 1) * 300;
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify($excel);
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load($excel);
        $objWorksheet = $xl->getActiveSheet();
        $lastRow = $objWorksheet->getHighestDataRow();
        if ($startRow < 2) {
            $startRow = 2;
        }
        if ($endRow > $lastRow) {
            $endRow = $lastRow;
        }
        if ($startRow > $lastRow) {
            return $this->respondOk();
        }
        dump($startRow, $endRow);
        $wegoIds = $objWorksheet->rangeToArray('B' . $startRow . ':B' . $endRow);
        $wegoIds = collect($wegoIds)->flatten()->toArray();
        $digiIds = $objWorksheet->rangeToArray('A' . $startRow . ':A' . $endRow);
        $digiIds = collect($digiIds)->flatten()->toArray();
        $combine = array_combine($wegoIds, $digiIds);
        foreach ($wegoIds as $wegoId) {
            $products = ProductDetail::where('product_id', $wegoId)->get();
            if (!is_null($products)) {
                foreach ($products as $product) {
                    $product->uid = $combine[$wegoId];
                    $product->save();
                }
                Product::where('id', $wegoId)->elastic()->addToIndex();
            }
        }
        return $this->respondOk("successful " . Carbon::now()->toDateTimeString());
    }

    public function findWegobazaarId(Request $request)
    {
        $excels = glob(public_path() . "/*/*.xls");
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'digikala_id');
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, 'wegobazaar_id');
        $rowCount++;
        foreach ($excels as $excel) {
            PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
            $filetype = PHPExcel_IOFactory::identify($excel);
            $objReader = PHPExcel_IOFactory::createReader($filetype);
            $xl = $objReader->load($excel);
            $objWorksheet = $xl->getActiveSheet();
            $lastRow = $objWorksheet->getHighestDataRow();
            $englishNames = $objWorksheet->rangeToArray('A2:' . 'A' . $lastRow);
            $englishNames = collect($englishNames)->flatten()->toArray();
            $digiIds = $objWorksheet->rangeToArray('C2:' . 'C' . $lastRow);
            $digiIds = collect($digiIds)->flatten()->toArray();
            $idCombine = array_combine($englishNames, $digiIds);
            $xl->getActiveSheet()->SetCellValue('C' . '1', 'wegobazaar_id');
            foreach ($englishNames as $key => $englishName) {
                $englishNameq = str_replace(' ', '', $englishName);
                $product = Product::whereRaw("REPLACE(`english_name`,' ','') = \"{$englishNameq}\"")->where('store_id', 92)
                    ->orWhere(function ($query) use ($englishNameq) {
                        $query->whereRaw("REPLACE(`key_name`,' ','') = \"{$englishNameq}\"")->where('store_id', 92);
                    })->first();
                if (empty($englishNameq)) {
                    $product = null;
                }
                if (!is_null($product)) {
                    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $idCombine[$englishName]);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $product->id);
                    $rowCount++;
                }
            }
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(public_path('test1.xls'));
    }

    public function findWegobazaarId2(Request $request)
    {
        set_time_limit(2000);
        $excels = glob(public_path() . "/*/*.xls");
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'digikala_id');
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, 'wegobazaar_id');
        $rowCount++;
        foreach ($excels as $excel) {
            $names = [];
            $eNames = [];
            PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
            $filetype = PHPExcel_IOFactory::identify($excel);
            $objReader = PHPExcel_IOFactory::createReader($filetype);
            $xl = $objReader->load($excel);
            $objWorksheet = $xl->getActiveSheet();
            $lastRow = $objWorksheet->getHighestDataRow();
            $englishNames = $objWorksheet->rangeToArray('A2:' . 'A' . $lastRow);
            $englishNames = collect($englishNames)->flatten()->toArray();
            $digiIds = $objWorksheet->rangeToArray('C2:' . 'C' . $lastRow);
            $digiIds = collect($digiIds)->flatten()->toArray();
            $idCombine = array_combine($englishNames, $digiIds);
            $xl->getActiveSheet()->SetCellValue('C' . '1', 'wegobazaar_id');
            foreach ($englishNames as $key => $englishName) {
//                $englishNameq = str_replace(' ', '', $englishName);
//                $product = Product::whereRaw("REPLACE(`english_name`,' ','') = \"{$englishNameq}\"")->where('store_id', 29)
//                    ->orWhere(function ($query) use ($englishNameq) {
//                        $query->whereRaw("REPLACE(`key_name`,' ','') = \"{$englishNameq}\"")->where('store_id', 29);
//                    })->first();
                if (empty($englishNameq)) {
                    $product = null;
                }
                if (!is_null($product)) {
                    $names[] = $product->english_name;
                }
//                if (is_null($product) && !empty($englishName)) {
//                    $eNames[] = $englishName;
//                }
                if (!empty($englishName)) {
                    $eNames[] = $englishName;
                }
            }
            foreach ($eNames as $eName) {
                $products = Product::where('category_id', 209)->get();
                foreach ($products as $prd) {
                    similar_text(str_replace(' ','',str_replace('-', '', strtolower($eName))),str_replace(' ','',str_replace('-', '', strtolower($prd->persian_name))), $percent);
                    if ($percent > 98) {
                        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $idCombine[$eName]);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $eName);
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $prd->id);
                        $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $prd->persian_name);
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $percent);
                        $rowCount++;
                    }
                }
            }
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(public_path('test2.xls'));
    }

    public function setWarrantyAndEnglishName(Request $request)
    {
        $excel = $request->file('file');
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify($excel);
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load($excel);
        $objWorksheet = $xl->getActiveSheet();
        $lastRow = $objWorksheet->getHighestDataRow();
        $englishNames = $objWorksheet->rangeToArray('A2:' . 'A' . $lastRow);
        $englishNames = collect($englishNames)->flatten()->toArray();
        $newNames = $objWorksheet->rangeToArray('B2:' . 'B' . $lastRow);
        $newNames = collect($newNames)->flatten()->toArray();
        $prices = $objWorksheet->rangeToArray('C2:' . 'C' . $lastRow);
        $prices = collect($prices)->flatten()->toArray();
        $warranties = $objWorksheet->rangeToArray('D2:' . 'D' . $lastRow);
        $warranties = collect($warranties)->flatten()->toArray();
        $newNames = array_combine($englishNames, $newNames);
        $warranties = array_combine($englishNames, $warranties);
        $prices = array_combine($englishNames, $prices);


        foreach ($englishNames as $englishName) {
            $englishNameq = str_replace(' ', '', $englishName);
            $product = Product::whereRaw("REPLACE(`english_name`,' ','') = \"{$englishNameq}\"")->where('store_id', 92)
                ->orWhere(function ($query) use ($englishNameq) {
                    $query->whereRaw("REPLACE(`key_name`,' ','') = \"{$englishNameq}\"")->where('store_id', 92);
                })->first();
            if (empty($englishNameq)) {
                $product = null;
            }
            if (!is_null($product)) {
                $product->current_price = $prices[$englishName];
                $product->warranty_name = $warranties[$englishName];
                $product->english_name = $newNames[$englishName];
                $product->save();
                Product::where('id', $product->id)->elastic()->get()->addToIndex();
            }
        }
        return $this->respondOk("successful " . Carbon::now()->toDateTimeString());
    }

    public function findExistingProducts(Request $request)
    {
        set_time_limit(200);
        $excel = $request->file('file');
        $name = $request->name;
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify($excel);
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load($excel);
        $objWorksheet = $xl->getActiveSheet();
        $lastColumn = $objWorksheet->getHighestDataColumn();
        $lastRow = $objWorksheet->getHighestDataRow();
        $duplicate = [];
        for ($row = 2; $row < $lastRow;) {
            $product = null;
            $lastRow = $objWorksheet->getHighestDataRow();
            $value = $objWorksheet->getCell('B' . $row)->getValue();
            $product = Product::where('english_name', $value)->orWhere('english_name', $value)->first();
            if (!is_null($product)) {
                $objWorksheet->removeRow($row, 1);
            } else {
                $row = $row + 1;
            }
        }
        $objWriter = new PHPExcel_Writer_Excel2007($xl);
        $objWriter->save(public_path() . '/healthy/' . $name . '.xls');
        return $this->respondOk("successful " . Carbon::now()->toDateTimeString());
    }

    public function correctWeight(Request $request)
    {
        try {
            $excel = $request->file('file');
//        $offset = $request->offset;
            PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
            $filetype = PHPExcel_IOFactory::identify($excel);
            $objReader = PHPExcel_IOFactory::createReader($filetype);
            $xl = $objReader->load($excel);
            $objWorksheet = $xl->getActiveSheet();
            $lastColumn = $objWorksheet->getHighestDataColumn();
            $lastRow = $objWorksheet->getHighestDataRow();
            foreach ($objWorksheet->getRowIterator() as $rowIndex => $workSheet_row) {
                if ($rowIndex == 1) {
                    continue;
                };
                if ($rowIndex > $lastRow) {
                    break;
                }
                $headings = $objWorksheet->rangeToArray('A1:' . $lastColumn . 1,
                    NULL,
                    TRUE,
                    FALSE);
                $array = $objWorksheet->rangeToArray('A' . $rowIndex . ':' . $lastColumn . $rowIndex);
                $row = (array_combine($headings[0], $array[0]));
                $product = Product::where('id', $row['id'])->first();
                if (!is_null($product)) {
                    $product->weight = $row['weight'];
                    $product->save();
                    Product::where('id', $product->id)->elastic()->addToIndex();
                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function deleteRows($ids)
    {
        set_time_limit(2000);
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify(public_path('test.xls'));
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load(public_path('test.xls'));
        $objWorksheet = $xl->getActiveSheet();
        foreach ($ids as $id) {
            $lastRow = $objWorksheet->getHighestDataRow();
            $wegoIds = $objWorksheet->rangeToArray('B2:' . 'B' . $lastRow);
            $wegoIds = collect($wegoIds)->flatten()->toArray();
            $keys = array_keys($wegoIds, $id);
            if (!empty($keys)) {
                dump($id);
                foreach ($keys as $key) {
                    $row = $key + 2;
                    $xl->getActiveSheet()->removeRow($row);
                }
            }
        }
        $objWriter = new PHPExcel_Writer_Excel2007($xl);
//        $folderName = $status == self::EXIST ? $prefix.'exist-crawl' : $prefix.'notexist-crawl';
        $objWriter->save(public_path('test.xls'));
    }

    public function report()
    {
        $orders = OrderProduct::select(DB::raw('DISTINCT(detail_id)'))->whereNotNull('detail_id')->pluck('detail_id');
        $count = 0;
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'نام کالا');
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, 'کد کالا');
        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, 'گروه کالا');
        $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, 'تعداد سفارش');
        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, 'تعداد فروش');
        $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, 'جمع هزینه دریافتی');
        $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, 'جمع تخفیف داده شده');
        $rowCount++;
        foreach ($orders as $order) {
            $detail = ProductDetail::withTrashed()->find($order);
            $orderProducts = OrderProduct::where('detail_id', $order)->get();
            $orderCount = count($orderProducts);
            $productCount = $orderProducts->sum('quantity');
            $price = $orderProducts->sum(function ($orderProduct) {
                return ($orderProduct->price - $orderProduct->discount);
            });
            $totalDiscount = $orderProducts->sum('discount');
            $name = $detail->product->persian_name;
            $productCode = $detail->product_id;
            $categoryName = $detail->product->category->persian_name;
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $name);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $productCode);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $categoryName);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $orderCount);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $productCount);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $price);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $totalDiscount);
            $rowCount++;
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(public_path('report.xls'));
    }

    public function specificationCorrector(Request $request)
    {
        $excel = $request->file('file');
//        $offset = $request->offset;
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify($excel);
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load($excel);
        $objWorksheet = $xl->getActiveSheet();
        $lastColumn = $objWorksheet->getHighestDataColumn();
        $lastRow = $objWorksheet->getHighestDataRow();
        foreach ($objWorksheet->getRowIterator() as $rowIndex => $workSheet_row) {
            if ($rowIndex == 1) {
                continue;
            };
            if ($rowIndex > $lastRow) {
                break;
            }
            $headings = $objWorksheet->rangeToArray('A1:' . $lastColumn . 1,
                NULL,
                TRUE,
                FALSE);
            $array = $objWorksheet->rangeToArray('A' . $rowIndex . ':' . $lastColumn . $rowIndex);
            $row = (array_combine($headings[0], $array[0]));
            $product = Product::where('english_name', $row['english_name'])->where('store_id', 92)->first();
            if (!is_null($product)) {
                $categorySpec = Category::searchByQuery(['term' => ['id' => $product->category_id]]);
                $specifications = array_column($categorySpec['hits'][0]['_source']['specifications'], 'name');
                foreach ($specifications as $specification) {
                    if (array_key_exists($specification, $row)) {
                        $valueName = preg_replace("/‌/", " ", $row[$specification]);
                        $categorySpecifications = $categorySpec;
                        $specificationName = $specification;
                        $valueName = PersianUtil::toStandardPersianString($valueName);
                        $specification = $this->getSpecification($categorySpecifications, $specificationName);
                        if (count($specification) === 0 || is_null($valueName) || empty(trim($valueName)))
                            return;
                        $specId = $specification['id'];
                        if ($this->isSpecificationTextField($specification)) {


                            $this->request[$this->index]['values']['text_field'][] = ['specification_id' => $specId, 'name' => $valueName];

                            return;
                        } elseif ($this->isSingleValue($specification)) {
                            $value = Value::where('name', trim($valueName))->where('specification_id', $specId)->first();
                            if (is_null($value)) {
                                Value::create(['name' => trim($valueName), 'specification_id' => $specId]);
                                $categoryId = Specification::find($specId)->category->id;
                                Category::where('id', $categoryId)->elastic()->addToIndex();
                                sleep(1);
                                $categorySpecifications = $this->getCategorySpecifications($categorySpecifications['hits'][0]['_source']['id'])->getHits();
                                $specification = $this->getSpecification($categorySpecifications, $specificationName);
                            }
                            $valueIndex = $this->getIndex($specification['values'], $valueName);

                            if ($valueIndex === false)
                                return;
                            $valueId = $specification['values'][$valueIndex]['id'];

                            $this->request[$this->index]['values']['single'][] = ['specification_id' => $specId, 'value_id' => $valueId];
                        } else {
                            if (!empty($valueName)) {
                                $valueIds = [];
                                $valueName = str_replace('¡', '،', $valueName);
                                $values = explode('،', $valueName);
                                foreach ($values as $value) {
                                    $valueId = null;
                                    $val = Value::where('name', trim($value))->where('specification_id', $specId)->first();
                                    if (is_null($val)) {
                                        Value::create(['name' => trim($value), 'specification_id' => $specId]);
                                        $categoryId = Specification::find($specId)->category->id;
                                        Category::where('id', $categoryId)->elastic()->addToIndex();
                                        sleep(1);
                                        $categorySpecifications = $this->getCategorySpecifications($categorySpecifications['hits'][0]['_source']['id'])->getHits();
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
                }
            }
        }
    }
}
