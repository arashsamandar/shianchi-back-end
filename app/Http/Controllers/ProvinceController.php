<?php

namespace App\Http\Controllers;

use App\Bazaar;
use App\Repositories\BazaarRepository;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Log;
use Wego\Province\ProvinceFactory;
use Wego\Province\Util\MemoryProvinceManager;
use Wego\UserHandle\UserPermission;

class ProvinceController extends Controller
{
    function __construct(BazaarRepository $bazaarRepository){

    }
    public function getBazaars(Request $request){
        $provinceId = $request->input('province_id');
        $cityId = $request->input('city_id');
        $bazaars = Bazaar::where('province_id',(int)$provinceId)->where('city_id',(int)$cityId)->select('id','name')->get();
        return $bazaars->toArray();
    }

    public function store(Requests\ProvinceRequest $request)
    {
        return (new ProvinceFactory())->setProvinceId($request->id)->factory(new MemoryProvinceManager());
    }
}
