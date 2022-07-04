<?php

namespace App\Http\Controllers;

use App\Bazaar;
use App\Http\Requests\StoreBazaarRequest;
use App\Http\Requests\UpdateBazaarRequest;
use App\User;
use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Request;
use Wego\Helpers\JsonUtil;


class BazaarController extends ApiController
{

    public function index()
    {
        $result = Bazaar::all()->toArray();
        $result = JsonUtil::removeFields($result, [
            '*.created_at', '*.updated_at'
        ]);
        return $result;
    }

    public function store(StoreBazaarRequest $request)
    {
        $bazaar = Bazaar::create($request->all());
        $staff = User::where('email','staff@1.com')->first()->userable;
        $staff->Bazaars()->attach($bazaar->id);
        return $this->respondOk(Lang::get('generalMessage.BazaarCreateSuccessful'));
    }

    public function update(UpdateBazaarRequest $request)
    {
        $id = $request->input('id');

        Bazaar::hasStaff($id);

        Bazaar::hasStore($id);

        Bazaar::findOrFail($id)->update($request->all());

        return $this->respondOk(Lang::get('generalMessage.BazaarUpdateSuccessful'));

    }

    public function delete($id)
    {
        Bazaar::hasStaff($id);

        Bazaar::hasStore($id);

//        $bazaar = Bazaar::findOrFail($id);

//        Bazaar::deleteStoresInsideBazaar($bazaar);

        Bazaar::findOrFail($id)->delete();

        return $this->respondOk(Lang::get('generalMessage.BazaarDeleteSuccessful'));
    }

    public function search(Request $request)
    {
        $name = "%".$request->input('name')."%";

        $result = Bazaar::where('name', 'LIKE', $name)->get()->toArray();

        $result = JsonUtil::removeFields($result,[
           '*.created_at', '*.updated_at'
        ]);

        return $result;
    }

    public function addAllBazaarsToLocalStaff()
    {
        $bazaarIds = Bazaar::all()->pluck('id')->toArray();
        $staff = User::where('email','staff@1.com')->first()->userable;
        $staff->Bazaars()->sync($bazaarIds,false);
        return $this->respondOk();
    }
}
