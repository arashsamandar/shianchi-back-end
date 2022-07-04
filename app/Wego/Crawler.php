<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 10/25/17
 * Time: 12:05 PM
 */

namespace Wego;


use Carbon\Carbon;
use Goutte\Client;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Settings;
use PHPExcel_Writer_Excel2007;
use Wego\Helpers\PersianUtil;
use Wego\ShamsiCalender\Shamsi;

class Crawler
{
    const EXIST = 2;
    const NOT_EXIST = 3;
    const SOON = 1;
    public static function startCrawling($cat,$status=2,$prefix = '',$fileCount = 1){
        set_time_limit(2000);
        try {
            $output = self::sendCurl('https://www.digikala.com/api/Search/Category-' . $cat);
            $subcat = $output['categoryFilter']['SubcategoryModel'][0];
            $urlCodes = [];
            while ($subcat['UrlCode'] != $cat) {
                $subcat = $subcat['Children'][0];
            }
            $children = $subcat['Children'];
            if (!empty($children)) {
                foreach ($children as $child) {
                    $content = self::sendCurl('https://www.digikala.com/api/Search/Category-' . $child['UrlCode']);
                    $categoryFilter = $content['categoryFilter'];
                    if (!empty($categoryFilter)) {
                        $subcat = $categoryFilter['SubcategoryModel'][0];
                        while (strtolower($subcat['UrlCode']) != strtolower($child['UrlCode'])) {
                            $subcat = $subcat['Children'][0];
                        }
                        $children2 = $subcat['Children'];
                        if (!empty($children2)) {
                            foreach ($children2 as $child2) {
                                $content2 = self::sendCurl('https://www.digikala.com/api/Search/Category-' . $child2['UrlCode']);
                                $categoryFilter = $content2['categoryFilter'];
                                if (!empty($categoryFilter)) {
                                    $subcat = $categoryFilter['SubcategoryModel'][0];
                                    while (strtolower($subcat['UrlCode']) != strtolower($child2['UrlCode'])) {
                                        $subcat = $subcat['Children'][0];
                                    }
                                    $children3 = $subcat['Children'];
                                    if (empty($children3)) {
                                        $urlCodes[] = $subcat['UrlCode'];
                                    }
                                }
                            }
                        } else {
                            $urlCodes[] = $subcat['UrlCode'];
                        }
                    }
                }
            } else {
                $urlCodes[] = $subcat['UrlCode'];
            }
            $flag = false;
            foreach ($urlCodes as &$urlCode ) {
                $urlCode = str_replace(' ','%20',$urlCode);
//                self::crawlCategoryAndSetById($urlCode, $status, $xl ,$prefix);
            }
            $urlCodes = array_values(array_unique($urlCodes));
            $fileCount = self::crawlCategories($urlCodes, self::EXIST , $fileCount);
            $fileCount = self::crawlCategories($urlCodes, self::NOT_EXIST,$fileCount);
        } catch(\Exception $e){
            dump('category exception');
        }
        return $fileCount;

    }

    private static function sendCurl($path)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $content = json_decode($content,true);
        curl_close($ch);
        return $content;
    }
    public static function crawlCategoryAndSetById($urlCodes,$status,$prefix = '')
    {
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify(public_path('test.xls'));
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load(public_path('test.xls'));
        $objWorksheet = $xl->getActiveSheet();
        foreach($urlCodes as $urlCode) {
            $lastRow = $objWorksheet->getHighestDataRow();
            $digiIds = $objWorksheet->rangeToArray('A2:' . 'A' . $lastRow);
            $digiIds = collect($digiIds)->flatten()->toArray();
            $pageNumber = 10;
            dump($urlCode);
            for ($i = 0; $i < $pageNumber; $i++) {
                try {
                    $result = self::sendCurl('https://www.digikala.com/api/SearchApi/?urlCode=' . $urlCode . '&pageno=' . $i . '&status=' . $status);
                    $hits = $result['hits']['hits'];
                    $total = $result['hits']['total'];
                    $pageNumber = ($total + 47) / 48;
                } catch (\Exception $e) {
                    dump('search page exception');
                    $hits = [];
                }
                foreach ($hits as $product) {
                    try {
                        $lastPrice = 0;
                        $digiId = $product['_source']['Id'];
                        $currentPrice = $product['_source']['MinPrice'] / 10;
                        if ($product['_source']['MinPriceList'] != 0) {
                            $discount = $product['_source']['MinPriceList'] - $product['_source']['MinPrice'];
                            $discount = $discount / 10;
                            $mainPrice = $product['_source']['MinPriceList'] / 10;
                            $percent = $discount / $mainPrice;
                            if ($percent > .3 || $discount > 50000) {
                                $currentPrice = $mainPrice;
                            }
                        }
                        $keys = array_keys($digiIds, $digiId);
                        if (!empty($keys)) {
                            foreach ($keys as $key) {
                                $row = $key + 2;
                                $xl->getActiveSheet()->SetCellValue('C' . $row, $currentPrice);
                            }
                        }
                    } catch (\Exception $e) {
                        dump('product-exception');
                        sleep(10);
                    }
                }
            }
        }
        $objWriter = new PHPExcel_Writer_Excel2007($xl);
//        $folderName = $status == self::EXIST ? $prefix.'exist-crawl' : $prefix.'notexist-crawl';
        $objWriter->save(public_path('test.xls'));
    }

    public static function crawlCategories($urlCodes,$status,$fileCount = 1)
    {
//        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCount = 1;
        foreach($urlCodes as $urlCode) {
            $urlCode = strtolower($urlCode);
            $pageNumber = 10;
            for ($i = 1; $i <= $pageNumber; $i++) {
                try {
                    $result = self::sendCurl('https://www.digikala.com/ajax/search/category-' . $urlCode . '/?has_selling_stock=1&pageno=' . $i . '&sortby=4');
                    if ($pageNumber == 10) {
                        preg_match('/listing__counter.*[۰-۹]+,?[۰-۹]?/u', $result['data']['products'], $rest);
                        if(empty($rest)){
                            $pageNumber = 1 ;
                        } else {
                            $rest[0] = str_replace('listing__counter">', '', $rest[0]);
                            $rest[0] = str_replace(',', '', $rest[0]);
                            $rest[0] = PersianUtil::to_english_num($rest[0]);
                            $pageNumber = ($rest[0] + 35) / 36;
                            $pageNumber = (int)$pageNumber;
                        }
                    }
//                    dump($result);
                    $hits = $result['data']['click_impression'];
                } catch (\Exception $e) {
                    dump('search page exception');
                    dump($e->getMessage());
                    break;
                    $hits = [];
                }
                dump($urlCode . ' page: ' . $i);
                foreach ($hits as $product) {
                    if ($rowCount > 900){
                        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                        $objWriter->save(public_path('exist-crawl/testing'.$fileCount.'.xls'));
                        $objPHPExcel = new PHPExcel();
                        $objPHPExcel->setActiveSheetIndex(0);
                        $rowCount = 1;
                        $fileCount++;
                    }
                    try {
                        $digiId = $product['id'];
                        if($product['status'] == "marketable"){
                            $currentPrice = $product['price'] / 10;
                        } else {
                            $currentPrice = 0;
                        }
//                        if ($product['_source']['MinPriceList'] != 0) {
//                            $discount = $product['_source']['MinPriceList'] - $product['_source']['MinPrice'];
//                            $discount = $discount / 10;
//                            $mainPrice = $product['_source']['MinPriceList'] / 10;
//                            $percent = $discount / $mainPrice;
//                            if ($percent > .3 || $discount > 50000) {
//                                $currentPrice = $mainPrice;
//                            }
//                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $digiId);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $currentPrice);
                        $rowCount++;
                    } catch (\Exception $e) {
                        dump('product-exception');
                        sleep(10);
                    }
                }
            }
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
//        $folderName = $status == self::EXIST ? $prefix.'exist-crawl' : $prefix.'notexist-crawl';
        $objWriter->save(public_path('exist-crawl/testing'.$fileCount.'.xls'));
        $fileCount++;
        return $fileCount;
    }


    public static function crawlCategory($urlCode,$status,$prefix = '')
    {
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'english_name');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'current_price');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'digikala_id');
        $rowCount++;
        $pageNumber = 10;
        for($i=0;$i<$pageNumber;$i++){
            try {
                dd('https://www.digikala.com/api/SearchApi/?urlCode=' . $urlCode . '&pageno=' . $i . '&status=' . $status);
                $result = self::sendCurl('https://www.digikala.com/api/SearchApi/?urlCode=' . $urlCode . '&pageno=' . $i . '&status=' . $status);
                dd($result);
                $hits = $result['hits']['hits'];
                $total = $result['hits']['total'];
                $pageNumber = ($total + 47) / 48;
            } catch(\Exception $e){
                dump('search page exception');
                $hits=[];
            }
            dump($urlCode.' page: '.$i);
            dd($hits);
            foreach ($hits as $product) {
                try {
                    $lastPrice = 0;
//                    if ($status == Crawler::NOT_EXIST) {
//                        $output = self::sendCurl('https://api.digikala.com/Chart/GetChartInfo?productID=' . $product['_id']);
//                        if ($output == "Object reference not set to an instance of an object.") {
//                            $lastPrice = 0;
//                        } else {
//                            $output = \GuzzleHttp\json_decode($output, true);
//                            $cats = $output['Categories'];
//                            $size = sizeof($cats) - 1;
//                            $date = end($cats);
//                            $diff = Carbon::now()->diffInDays(Carbon::parse(Shamsi::convertToGeorgian($date)));
//                            $series = $output['Series'];
//                            foreach ($series as $serie) {
//                                foreach ($serie['data'] as $data) {
//                                    if ($data[0] == $size) {
//                                        $lastPrice = end($data);
//                                    }
//                                }
//                            }
//                            if ($diff > 14) {
//                                $lastPrice = 0;
//                            }
//                        }
//                    }
                    $englishName = trim($product['_source']['EnTitle']);
                    $digiId = $product['_source']['Id'];
                    $currentPrice = $product['_source']['MinPrice'] / 10;
                    if($product['_source']['MinPriceList'] != 0){
                        $discount = $product['_source']['MinPriceList'] - $product['_source']['MinPrice'];
                        $discount = $discount/10;
                        $mainPrice =$product['_source']['MinPriceList']/10;
                        $percent  = $discount / $mainPrice;
                        if($percent >.3 || $discount > 50000){
                            $currentPrice = $mainPrice;
                        }
                    }
                    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $englishName);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $digiId);
                    if ($status == self::EXIST) {
                        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $currentPrice);
                    } else {
                        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $lastPrice);
                    }
                    $rowCount++;
                } catch(\Exception $e){
                    dump('product-exception');
                    sleep(10);
                }
            }
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $folderName = $status == self::EXIST ? $prefix.'exist-crawl' : $prefix.'notexist-crawl';
        $objWriter->save(public_path($folderName.'/'.$urlCode.'.xls'));
    }

    public function crawlBamiloAndZanbilAndDigi()
    {
        $client = new Client();
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $filetype = PHPExcel_IOFactory::identify(public_path('fest.xls'));
        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $xl = $objReader->load(public_path('fest.xls'));
        $objWorksheet = $xl->getActiveSheet();
        $lastRow = $objWorksheet->getHighestDataRow();
        foreach($objWorksheet->getRowIterator() as $rowIndex => $workSheet_row) {
            if ($rowIndex ==1){
                continue;
            };
            if ($rowIndex > $lastRow){
                break;
            }
            $zanbilId= $objWorksheet->getCell("B".$rowIndex)->getValue();
            $bamiloId= $objWorksheet->getCell("C".$rowIndex)->getValue();
            $digikalaId = $objWorksheet->getCell("A".$rowIndex)->getValue();
            $bamiloCrawler = $client->request('GET','http://www.bamilo.com/'. $bamiloId. '.html');
            $bamiloPrice = $bamiloCrawler->filter('.price:nth-child(1) span:nth-child(1)')->each(function($node){
                $price = $node->text();
                return trim($price);
            });
            $bamiloPrice = preg_replace("/[^0-9]{1,4}/", '', $bamiloPrice[0]);
            $bamiloPrice = $bamiloPrice/10;
            $zanbilCrawler = $client->request('GET','https://www.zanbil.ir/product/' . $zanbilId);
            $zanbilPrice = $zanbilCrawler->filter('.current')->each(function($node){
                $price = $node->text();
                return trim($price);
            });
            $zanbilPrice = preg_replace("/[^0-9]{1,4}/", '', $zanbilPrice[0]);
            $zanbilPrice = $zanbilPrice/10;
            $digiName= $objWorksheet->getCell("F".$rowIndex)->getValue();
            $digiResult = self::sendCurl('https://www.digikala.com/api/SearchApi/?q=' . $digiName);
            $hits = $digiResult['hits']['hits'];
            foreach ($hits as $product) {
                $digiId = $product['_source']['Id'];
                if($digiId == $digikalaId){
                    $digiPrice = $product['_source']['MinPrice'] / 10;
                    break;
                }
            }
            if(empty($digiPrice)){
                $digiPrice = 100000000;
            }
            if(empty($bamiloPrice)){
                $bamiloPrice = 100000000;
            }
            if(empty($zanbilPrice)){
                $zanbilPrice = 100000000;
            }
            $min = min($zanbilPrice,$bamiloPrice,$digiPrice);
            if($min == 100000000){
                $min = 0;
            }
            $xl->getActiveSheet()->SetCellValue('D' . $rowIndex, $min);
        }
        $objWriter = new PHPExcel_Writer_Excel2007($xl);
        $objWriter->save(public_path('fest.xls'));
    }

    public static function crawlBooks($urlCodes,$status,$fileCount = 1)
    {
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCount = 1;
        $urls = [
            "https://www.digikala.com/ajax/search/category-book/?price[min]=10&price[max]=4999&sortby=4",
            "https://www.digikala.com/ajax/search/category-book/?price[min]=5000&price[max]=6999&sortby=4",
            "https://www.digikala.com/ajax/search/category-book/?price[min]=7000&price[max]=8999&sortby=4",
            "https://www.digikala.com/ajax/search/category-book/?price[min]=9000&price[max]=11999&sortby=4",
            "https://www.digikala.com/ajax/search/category-book/?price[min]=12000&price[max]=14999&sortby=4",
            "https://www.digikala.com/ajax/search/category-book/?price[min]=15000&price[max]=17999&sortby=4",
            "https://www.digikala.com/ajax/search/category-book/?price[min]=18000&price[max]=23999&sortby=4",
            "https://www.digikala.com/ajax/search/category-book/?price[min]=24000&price[max]=359999&sortby=4",
            "https://www.digikala.com/ajax/search/category-book/?price[min]=36000&price[max]=1000000&sortby=4",
        ];
        foreach($urls as $url) {
            $pageNumber = 10;
            for ($i = 1; $i <= $pageNumber; $i++) {
                try {
                    $result = self::sendCurl($url.'&pageno=' . $i );
                    if ($pageNumber == 10) {
                        preg_match('/listing__counter.*[۰-۹]+,?[۰-۹]?/u', $result['data']['products'], $rest);
                        if(empty($rest)){
                            $pageNumber = 1 ;
                        } else {
                            $rest[0] = str_replace('listing__counter">', '', $rest[0]);
                            $rest[0] = str_replace(',', '', $rest[0]);
                            $rest[0] = PersianUtil::to_english_num($rest[0]);
                            $pageNumber = ($rest[0] + 35) / 36;
                            $pageNumber = (int)$pageNumber;
                        }
                    }
                    $hits = $result['data']['click_impression'];
                } catch (\Exception $e) {
                    dump('search page exception');
                    dump($e->getMessage());
                    break;
                    $hits = [];
                }
                dump('book' . ' page: ' . $i);
                foreach ($hits as $product) {
                    if ($rowCount > 900){
                        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                        $objWriter->save(public_path('exist-crawl/testing'.$fileCount.'.xls'));
                        $objPHPExcel = new PHPExcel();
                        $objPHPExcel->setActiveSheetIndex(0);
                        $rowCount = 1;
                        $fileCount++;
                    }
                    try {
                        $digiId = $product['id'];
                        if($product['status'] == "marketable"){
                            $currentPrice = $product['price'] / 10;
                        } else {
                            $currentPrice = 0;
                        }
//                        if ($product['_source']['MinPriceList'] != 0) {
//                            $discount = $product['_source']['MinPriceList'] - $product['_source']['MinPrice'];
//                            $discount = $discount / 10;
//                            $mainPrice = $product['_source']['MinPriceList'] / 10;
//                            $percent = $discount / $mainPrice;
//                            if ($percent > .3 || $discount > 50000) {
//                                $currentPrice = $mainPrice;
//                            }
//                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $digiId);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $currentPrice);
                        $rowCount++;
                    } catch (\Exception $e) {
                        dump('product-exception');
                        sleep(10);
                    }
                }
            }
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
//        $folderName = $status == self::EXIST ? $prefix.'exist-crawl' : $prefix.'notexist-crawl';
        $objWriter->save(public_path('exist-crawl/testing'.$fileCount.'.xls'));
        $fileCount++;
        return $fileCount;
    }

    // in chikar mikone : kala haye jadid hamrah ba ax va moshakhasat
    public static function crawlNewCategory($urlCodes)
    {
        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
            // Ignores notices and reports all other kinds... and warnings
            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
            //     error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
        }
        try {
            $client = new Client();
            foreach ($urlCodes as $urlCode) {
//            PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->setActiveSheetIndex(0);
                $rowCount = 2;
                $brandNames = [];
                $persianBrandNames = [];
                $mainTitles = [];
                $urlCode = strtolower($urlCode);
                $pageNumber = 10;
                for ($i = 1; $i <= $pageNumber; $i++) {
                    try {
                        // Backup : $categoryCrawler = $client->request('GET', 'https://www.digikala.com/search/category-' . $urlCode);
                        // https://www.digikala.com/search/category-mattress/
                        // https://www.digikala.com/search/category-mattress/?brand[0]=3783&pageno=1&last_filter=brand&last_value=3783&sortby=22
                        $categoryCrawler = $client->request('GET','https://www.digikala.com/search/category-mattress/?brand[0]=3783&last_filter=brand&last_value=3783&sortby=22');
//                        $categoryCrawler = $client->request('GET', 'https://www.digikala.com/search/category-mattress/?brand[0]=3388&last_filter=brand&last_value=3388&sortby=4');
                        $brandName = $categoryCrawler->filter('li:nth-child(1) .js-box-content-item')->each(function ($node) {
                            return $node->extract('data-en')[0];
                        });
                        $persianBrandName = $categoryCrawler->filter('li:nth-child(1) .js-box-content-item')->each(function($node){
                            return trim($node->text());
                        });
                        for ($j = 2; !empty($brandName[0]); $j++) { // CTRL + C ro begiram ?begir halle di
                            $brandNames[] = $brandName[0];
                            $persianBrandNames[] = $persianBrandName[0];
                            $brandName = $categoryCrawler->filter('li:nth-child(' . $j . ') .js-box-content-item')->each(function ($node) {
                                return $node->extract('data-en')[0];
                            });
                            $persianBrandName = $categoryCrawler->filter('li:nth-child(' . $j . ') .js-box-content-item')->each(function ($node) {
                                return trim($node->text());
                            });
                        }
                        // Backup : $result = self::sendCurl('https://www.digikala.com/ajax/search/category-' . $urlCode . '/?pageno=' . $i . '&sortby=4');
                        // https://www.digikala.com/ajax/search/category-mattress/?brand[0]=3388&pageno=1&last_filter=brand&last_value=3388&sortby=4
                        // $result = self::sendCurl('https://www.digikala.com/ajax/search/category-mattress/?brand[0]=3388&pageno=' . $i . '&last_filter=brand&last_value=3388&sortby=4');
                        // https://www.digikala.com/ajax/search/category-mattress/?brand[0]=3783&pageno=1&last_filter=brand&last_value=3783&sortby=22
                        $result = self::sendCurl('https://www.digikala.com/ajax/search/category-mattress/?brand[0]=3783&pageno=' . $i . '&last_filter=brand&last_value=3783&sortby=22');
                        if ($pageNumber == 10) {
                            preg_match('/listing__counter.*[۰-۹]+,?[۰-۹]?/u', $result['data']['products'], $rest);
                            if (empty($rest)) {
                                $pageNumber = 1;
                            } else {
                                $rest[0] = str_replace('listing__counter">', '', $rest[0]);
                                $rest[0] = str_replace(',', '', $rest[0]);
                                $rest[0] = PersianUtil::to_english_num($rest[0]);
                                $pageNumber = ($rest[0] + 35) / 36;
                                $pageNumber = (int)$pageNumber;
                                if ($pageNumber > 99) {
                                    $pageNumber = 99;
                                }
                            }
                        }
                        $hits = $result['data']['click_impression'];
                        foreach ($hits as $hit) {
                            if ($hit['status'] != "stop_production"
//                            && $hit['id']>560420
                            ) {
                                $crawler = $client->request('GET', 'https://www.digikala.com/product/dkp-' . $hit['id']);
                                $specKey = $crawler->filter('section:nth-child(2) li:nth-child(1) .c-params__list-key .block')->each(function ($node) {
                                    return trim($node->text());
                                });
                                $specValue = $crawler->filter('section:nth-child(2) li:nth-child(1) .c-params__list-value .block')->each(function ($node) {
                                    return trim($node->text());
                                });
                                for ($l = 3; !empty($specValue[0]); $l++) {
                                    for ($m = 2; !empty($specValue[0]); $m++) {
                                        $found = array_search($specKey[0], $mainTitles);
                                        $value = $specValue[0];
                                        if ($found === false) {
                                            $mainTitles[] = PersianUtil::toStandardPersianString($specKey[0]);
                                            $titles = sizeof($mainTitles);
                                            $colnum = $titles + 5;
                                        } else {
                                            $colnum = $found + 6;
                                        }
                                        $specKey = $crawler->filter('section:nth-child(' . ($l - 1) . ') li:nth-child(' . $m . ') .c-params__list-key .block')->each(function ($node) {
                                            return trim($node->text());
                                        });
                                        $specValue = $crawler->filter('section:nth-child(' . ($l - 1) . ') li:nth-child(' . $m . ') .c-params__list-value .block')->each(function ($node) {
                                            return trim($node->text());
                                        });
                                        while (empty($specKey[0]) && !empty($specValue[0])) {
                                            $value = $value . " " . $specValue[0];
                                            $m++;
                                            $specKey = $crawler->filter('section:nth-child(' . ($l - 1) . ') li:nth-child(' . $m . ') .c-params__list-key .block')->each(function ($node) {
                                                return trim($node->text());
                                            });
                                            $specValue = $crawler->filter('section:nth-child(' . ($l - 1) . ') li:nth-child(' . $m . ') .c-params__list-value .block')->each(function ($node) {
                                                return trim($node->text());
                                            });
                                        }
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colnum, $rowCount, $value);
                                    }
                                    $specKey = $crawler->filter('section:nth-child(' . $l . ') li:nth-child(1) .c-params__list-key .block')->each(function ($node) {
                                        return trim($node->text());
                                    });
                                    $specValue = $crawler->filter('section:nth-child(' . $l . ') li:nth-child(1) .c-params__list-value .block')->each(function ($node) {
                                        return trim($node->text());
                                    });
                                }
                                $gr = "";
                                $gurantee = $crawler->filter('.js-guarantee-text span:nth-child(1)')->each(function ($node) {
                                    return (trim($node->text()));
                                });
                                for ($g = 2; !empty($gurantee[0]); $g++) {
                                    $gr = $gr . $gurantee[0] . " ";
                                    $gurantee = $crawler->filter('.js-guarantee-text span:nth-child(' . $g . ')')->each(function ($node) {
                                        return (trim($node->text()));
                                    });
                                }
                                $image = $crawler->filter('img')->each(function ($node) {
                                    $img = $node->extract('data-zoom-image');
                                    if (strpos($img[0], "dkstatics-public.digikala.com/digikala-products/")) {
                                        return $img[0];
                                    }
                                    return "";
                                });
                                $image = array_filter($image, function ($value) {
                                    return $value !== '';
                                });
                                $image = array_values($image);
                                $persianName = $crawler->filter('.c-product__title')->each(function ($node) {
                                    return $node->text();
                                });
                                $englishName = $crawler->filter('.c-product__title span')->each(function ($node) {
                                    return $node->text();
                                });
                                if (empty($englishName)) {
                                    $englishName[] = "";
                                }
                                $pName = str_replace($englishName[0], '', $persianName[0]);
                                $pName = trim($pName);
                                $mainPrice = $crawler->filter('del')->each(function ($node) {
                                    return $node->text();
                                });
                                $cPrice = $crawler->filter('.js-price-value')->each(function ($node) {
                                    return $node->text();
                                });
                                if (!empty($mainPrice[0])) {
                                    $mPrice = PersianUtil::to_english_num($mainPrice[0]);
                                    $mPrice = str_replace(',', '', $mPrice);
                                    $currentPrice = trim($mPrice);
                                } elseif (!empty($cPrice)) {
                                    $cPrice = PersianUtil::to_english_num($cPrice[0]);
                                    $cPrice = str_replace(',', '', $cPrice);
                                    $currentPrice = trim($cPrice);
                                } else {
                                    $currentPrice = 0;
                                }
                                $imageId = $hit['id'];
                                //todo image crawling
                                $url = $image[0];
                                if (!file_exists(public_path($urlCode)))
                                    mkdir(public_path($urlCode), 0755, true);
                                $img = public_path($urlCode . '/' . $imageId . ".jpg");
                                if (!file_exists(public_path($urlCode . '/' . $imageId . ".jpg"))) {
                                    file_put_contents($img, file_get_contents($url));
                                }
                                //todo end of image crawling
                                $gr = trim($gr);
                                $brand = "";
                                foreach ($brandNames as $key => $brName) {
                                    if (substr($englishName[0], 0, strlen($brName)) === $brName) {
                                        $brand = $brName . '_' . $persianBrandNames[$key];
                                        break;
                                    }
                                }
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $pName);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $englishName[0]);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $imageId);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $currentPrice);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $brand);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $gr);
                                $rowCount++;
                            }
                        }
                    } catch (\Exception $e) {
                        dump('search page exception');
                        dump($e->getTrace());
                        break;
                    }
                    dump($urlCode . ' page: ' . $i);
//                foreach ($hits as $product) {
//                    if ($rowCount > 900){
//                        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
//                        $objWriter->save(public_path('exist-crawl/testing'.$fileCount.'.xls'));
//                        $objPHPExcel = new PHPExcel();
//                        $objPHPExcel->setActiveSheetIndex(0);
//                        $rowCount = 1;
//                        $fileCount++;
//                    }
//                    try {
//                        $digiId = $product['id'];
//                        if($product['status'] == "marketable"){
//                            $currentPrice = $product['price'] / 10;
//                        } else {
//                            $currentPrice = 0;
//                        }
////                        if ($product['_source']['MinPriceList'] != 0) {
////                            $discount = $product['_source']['MinPriceList'] - $product['_source']['MinPrice'];
////                            $discount = $discount / 10;
////                            $mainPrice = $product['_source']['MinPriceList'] / 10;
////                            $percent = $discount / $mainPrice;
////                            if ($percent > .3 || $discount > 50000) {
////                                $currentPrice = $mainPrice;
////                            }
////                        }
//                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $digiId);
//                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $currentPrice);
//                        $rowCount++;
//                    } catch (\Exception $e) {
//                        dump('product-exception');
//                        sleep(10);
//                    }
//                }
                }
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'persian_name');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'english_name');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'image_id');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'current_price');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'brand');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'warranty_name');
                for ($index = 0; $index < sizeof($mainTitles); $index++) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index + 6, 1, $mainTitles[$index]);
                }
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
//        $folderName = $status == self::EXIST ? $prefix.'exist-crawl' : $prefix.'notexist-crawl';
                $objWriter->save(public_path('crawled/' . $urlCode . '.xls'));
            }
        } catch (\Exception $e){
            dd($e->getMessage());
        }
    }
}