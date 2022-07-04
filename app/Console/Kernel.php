<?php

namespace App\Console;

use App\Holiday;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductScoreController;
use App\Http\Controllers\TokenController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Wego\Buy\BuyStorageUtil;
use Wego\Product\ProductEditor;
use Wego\ProductViewCount;
use Wego\Services\SiteMap\SiteMapCreator;
use Wego\StoreViewCount;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
        Commands\Crawl::class,
        Commands\NewCrawl::class,
        Commands\GenerateSiteMap::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function(){
            CouponController::setCouponExpirationStatus();
        })->dailyAt("4:30");
        $schedule->call(function(){
            PasswordController::cleanPasswordChangeRequestsTable();
        })->daily();
        $schedule->call(function(){
            BuyStorageUtil::cancelNotProgressableOrder();
        })->dailyAt("00:30");
        $schedule->call(function(){
            StoreViewCount::setUpStoreViewCount();
            ProductViewCount::setUpProductViewCount();
        })->dailyAt("00:05");
        $schedule->call(function(){
            StoreViewCount::calculateAllStoresVisitorCount();
            ProductViewCount::calculateAllProductsVisitorCount();
        })->dailyAt("04:00");
        $schedule->call(function(){
            ProductEditor::setSpecialExpirationStatus();
        })->dailyAt("1:00");
        $schedule->call(function(){
            ProductScoreController::addAverageScoreToAllProducts();
        })->dailyAt("1:30");
        $schedule->call(function(){
            TokenController::deleteExpireTokenFromDB();
        })->dailyAt("03:00");
        $schedule->call(function(){
           SiteMapCreator::build();
        })->dailyAt("2:30");
//        $schedule->call(function(){
//            Holiday::checkToday();
//        })->hourlyAt(5)->between('00:01','15:10');
//        $schedule->call(function(){
//            Holiday::checkNextAvailableDay();
//        })->hourlyAt(2);
        $schedule->call(function(){
            Holiday::checkToday();
        })->hourlyAt(15);
//        $schedule->call(function(){
//            ProductController::setNotUpdateToNotExist();
//        })->twiceDaily(12,21);
        $schedule->call(function(){
            ProductController::setNotUpdateToNotExist();
        })->cron('0 */6 * * *');
    }
}