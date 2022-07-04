<?php

namespace App\Http\Controllers;

use DB;
use App\Color;
use App\Http\Requests\StoreColorRequest;
use App\Http\Requests\UpdateColorRequest;
use Illuminate\Support\Facades\Lang;
use Wego\Helpers\JsonUtil;



class ColorController extends ApiController
{
    public function index()
    {
        $result = Color::all()->toArray();
        $result = JsonUtil::removeFields($result, [
            '*.name', '*.created_at', '*.updated_at'
        ]);
        return $result;
    }

    public function store(StoreColorRequest $request)
    {
        Color::create($request->all());
        return $this->respondOk(Lang::get('generalMessage.ColorCreateSuccessful'));
    }

    public function update(UpdateColorRequest $request)
    {
        $id = $request->input('id');

        Color::isUsed($id);

        Color::findOrFail($id)->update($request->all());

        return $this->respondOk(Lang::get('generalMessage.ColorUpdateSuccessful'));

    }

    public function delete($id)
    {
        Color::isUsed($id);

        Color::findOrFail($id)->delete();

        return $this->respondOk(Lang::get('generalMessage.ColorDeleteSuccessful'));

    }

}
