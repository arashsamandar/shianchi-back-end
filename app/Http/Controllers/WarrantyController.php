<?php

namespace App\Http\Controllers;

use App\Warranty;
use Illuminate\Http\Request;

class WarrantyController extends ApiController
{
    public function store(Request $request)
    {
        Warranty::create(array_except($request->all(),'token'));
        return $this->respondOk();
    }

    public function update(Request $request)
    {
        Warranty::find($request->id)->update(array_except($request->all(),['token','id']));
        return $this->respondOk();
    }

    public function delete($id)
    {
        Warranty::destroy($id);
        return $this->respondOk();
    }

    public function index()
    {
        return Warranty::all();
    }
}
