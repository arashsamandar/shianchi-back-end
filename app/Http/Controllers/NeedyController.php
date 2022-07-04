<?php

namespace App\Http\Controllers;

use App\Needy;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Wego\UserHandle\UserPermission;

class NeedyController extends ApiController
{
    use ValidatesRequests;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DB::table('needies')->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->toArray(),[
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
        ]);
        if($validator->fails())
            return $this->setStatusCode(404)->respondWithError($validator->errors()->all());
        Needy::create($request->toArray());
        return $this->setStatusCode(200)->respondOk('successfully added');
    }

    public function setToHelped($id){
        UserPermission::checkStaffPermission();
        Needy::where('id','=',$id)->update(['is_helped' => 'H']);
        return $this->setStatusCode(200)->respondOk('successful');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        UserPermission::checkStaffPermission();
        Needy::find($id)->delete();
        return $this->setStatusCode(200)->respondOk("successfully deleted");
    }
}
