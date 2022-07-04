<?php

namespace App\Providers;
use Symfony\Component\HttpFoundation\File\File;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Wego\UserHandle\RestValidators;
use Wego\DimensionsValidator;
use Wego\validator\CardValidator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        \Validator::resolver(
            function($translator, $data, $rules, $messages)
        {
            return new RestValidators($translator, $data, $rules, $messages);
        });
        Validator::extend('alpha_spaces', function($attribute, $value)
        {
            return preg_match('/^[\pL\s]+$/u', $value);
        });


        Validator::extend('card','Wego\validator\CardValidator@validate');
        Validator::extend('special','Wego\validator\SpecialValidator@validate');
//        Validator::extend('dimensions',function($attributes,$value,$parameter,$validator){
//
//            $array =[];
//            list($width,$height) = getimagesize($value);
//            foreach ($parameter as $param) {
//                list($key,$val) = explode('=',$param);
//                $array[$key] = $val;
//            }
//
//            if(array_key_exists('max_width',$array))
//            {
//                if($width > $array['max_width'] )
//                    return false;
//            }if(array_key_exists('min_width',$array))
//            {
//                if($width < $array['min_width'] )
//                    return false;
//            }if(array_key_exists('max_height',$array))
//            {
//                if($height > $array['max_height'] )
//                    return false;
//            }if(array_key_exists('min_height',$array))
//            {
//                if($height < $array['min_height'] )
//                    return false;
//            }
//            return true;
//        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
