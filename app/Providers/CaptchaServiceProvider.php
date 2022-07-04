<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Wego\Services\Captcha\CaptchaInterface;
use Wego\Services\Captcha\GoogleRecaptcha;
use Wego\Services\MakeRequest\Curl;
use Wego\Services\MakeRequest\MakeRequestInterface;


class CaptchaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CaptchaInterface::class,GoogleRecaptcha::class);
        $this->app->bind(MakeRequestInterface::class,Curl::class);
    }
}
