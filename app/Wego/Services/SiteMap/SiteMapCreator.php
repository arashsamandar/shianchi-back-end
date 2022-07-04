<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 2/12/17
 * Time: 11:52 AM
 */

namespace Wego\Services\SiteMap;


use App\Category;
use App\Http\Controllers\SearchController;
use App\Product;
use App\Store;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SiteMapCreator
{
    public static function build()
    {
        $sitemap = App::make("sitemap");

        // get all products from db (or wherever you store them)
        self::createProductSiteMap($sitemap);
        self::createCategorySiteMap($sitemap);
        self::createStaticSiteMap($sitemap);

        // generate new sitemapindex that will contain all generated sitemaps above
        $sitemap->store('sitemapindex','sitemap');
    }

    /**
     * @param $sitemap
     * @return array
     */
    private static function createProductSiteMap($sitemap)
    {
        $products = Product::where('confirmation_status',Product::CONFIRMED)->orderBy('created_at', 'desc')->get();

        // counters
        $counter = 0;
        $sitemapCounter = 0;

        // add every product to multiple sitemaps with one sitemapindex
        foreach ($products as $p) {
            if ($counter == 50000) {
                // generate new sitemap file
                $sitemap->store('xml', 'productSitemap-' . $sitemapCounter);
                // add the file to the sitemaps array
                $sitemap->addSitemap(secure_url('productSitemap-' . $sitemapCounter . '.xml'));
                // reset items array (clear memory)
                $sitemap->model->resetItems();
                // reset the counter
                $counter = 0;
                // count generated sitemap
                $sitemapCounter++;
            }
            // add product to items array
            $sitemap->add('http://shianchi.com'.Product::url($p->id,$p->english_name,$p->persian_name),null,'1.00','weekly');
            // count number of elements
            $counter++;
        }

        // you need to check for unused items
        if (!empty($sitemap->model->getItems())) {
            // generate sitemap with last items
            $sitemap->store('xml', 'productSitemap-' . $sitemapCounter);
            // add sitemap to sitemaps array
            $sitemap->addSitemap('http://shianchi.com/productSitemap-' . $sitemapCounter . '.xml');
            // reset items array
            $sitemap->model->resetItems();
            return array($counter, $sitemapCounter);
        }
        return array($counter, $sitemapCounter);
    }

    /**
     * @param $sitemap
     */
    private static function createStoreSiteMap($sitemap)
    {
        $stores = Store::orderBy('created_at', 'desc')->get();

        // counters
        $counter = 0;
        $sitemapCounter = 0;

        // add every product to multiple sitemaps with one sitemapindex
        foreach ($stores as $s) {
            if ($counter == 50000) {
                // generate new sitemap file
                $sitemap->store('xml', 'storeSitemap-' . $sitemapCounter);
                // add the file to the sitemaps array
                $sitemap->addSitemap('http://shianchi.com/storeSitemap-' . $sitemapCounter . '.xml');
                // reset items array (clear memory)
                $sitemap->model->resetItems();
                // reset the counter
                $counter = 0;
                // count generated sitemap
                $sitemapCounter++;
            }
            // add product to items array
            $sitemap->add('http://api.shianchi.com/store/' . $s->url, null, '1.00', 'weekly');
            // count number of elements
            $counter++;
        }

        // you need to check for unused items
        if (!empty($sitemap->model->getItems())) {
            // generate sitemap with last items
            $sitemap->store('xml', 'storeSitemap-' . $sitemapCounter);
            // add sitemap to sitemaps array
            $sitemap->addSitemap(secure_url('storeSitemap-' . $sitemapCounter . '.xml'));
            // reset items array
            $sitemap->model->resetItems();
        }
    }

    private static function createStaticSiteMap($sitemap)
    {
        $sitemap->add('http://shianchi.com/','2017-05-20T23:59:59+04:30','0.9' ,'monthly');
        $sitemap->add('http://shianchi.com/contact-us','2017-05-20T23:59:59+04:30','0.9' ,'monthly');
        $sitemap->add('http://shianchi.com/about-us','2017-05-20T23:59:59+04:30','0.9' ,'monthly');
        $sitemap->add('http://shianchi.com/back-guarantee','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
        $sitemap->add('http://shianchi.com/privacy','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
        $sitemap->add('http://shianchi.com/back-guarantee','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
        $sitemap->add('http://shianchi.com/delivery-method','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
        $sitemap->add('http://shianchi.com/payment-method','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
        $sitemap->add('http://shianchi.com/report','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
        $sitemap->add('http://shianchi.com/register','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
//        $sitemap->add('http://shiii.ir/better-sale','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
//        $sitemap->add('http://shiii.ir/cooperation','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
//        $sitemap->add('http://shiii.ir/jobs','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
//        $sitemap->add('http://shiii.ir/privacy','2017-05-20T23:59:59+04:30','0.8' ,'monthly');
        $sitemap->store('xml', 'staticSitemap');
        // add the file to the sitemaps array
        $sitemap->addSitemap('http://api.shianchi.com/staticSitemap.xml');
        $sitemap->model->resetItems();
    }

    private static function createCategorySiteMap($sitemap)
    {
        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
// Ignores notices and reports all other kinds... and warnings
            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
// error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
        }
        $categories = Category::orderBy('created_at', 'desc')->get();
        $counter = 0;
        $sitemapCounter = 0;

        // add every product to multiple sitemaps with one sitemapindex
        foreach ($categories as $category) {
            if ($counter == 50000) {
                // generate new sitemap file
                $sitemap->store('xml', 'categorySitemap-' . $sitemapCounter);
                // add the file to the sitemaps array
                $sitemap->addSitemap(secure_url('categorySitemap-' . $sitemapCounter . '.xml'));
                // reset items array (clear memory)
                $sitemap->model->resetItems();
                // reset the counter
                $counter = 0;
                // count generated sitemap
                $sitemapCounter++;
            }
            // add product to items array
            //search/book?category=book
            $sitemap->add('http://api.shianchi/search/'.$category->name . '?category='.$category->name,null,'1.00','weekly');
            // count number of elements
            $counter++;
        }

        // you need to check for unused items
        if (!empty($sitemap->model->getItems())) {
            // generate sitemap with last items
            $sitemap->store('xml', 'categorySitemap-' . $sitemapCounter);
            // add sitemap to sitemaps array
            $sitemap->addSitemap('http://api.shianchi.com/categorySitemap-' . $sitemapCounter . '.xml');
            // reset items array
            $sitemap->model->resetItems();
            return array($counter, $sitemapCounter);
        }
        return array($counter, $sitemapCounter);
    }


}