<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Wego\Services\SiteMap\SiteMapCreator as SiteMapCreator;

class GettingSiteMap extends Controller
{
    public function saveSiteMaps() {
        SiteMapCreator::build();
    }
}
