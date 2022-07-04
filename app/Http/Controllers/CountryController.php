<?php

namespace App\Http\Controllers;

use App\Country;
use App\Http\Requests;

class CountryController extends Controller
{
    public function index(){
        $countries = new Country();
        return $countries->getCountries();
    }
}
