<?php

namespace App\Http\Controllers;

use App\Criticism;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;


use Illuminate\Support\Facades\Validator;
use Tymon;
use Wego\Helpers\JsonUtil;

class CriticismController extends ApiController
{
    use Helpers;
    protected static $types = [
        ['key' => 0,'value' => 'روش خرید'],
        ['key' => 1,'value' => 'فروش'],
        ['key' => 2,'value' => 'پوشش‌دهی بازار'],
        ['key' => 3,'value' => 'مالی'],
        ['key' => 4,'value' => 'مدیریت'],
        ['key' => 5,'value' => 'عملکرد سایت'],
        ['key' => 6,'value' => 'سایر'],
    ];

    /**
     * return a list of the read Criticisms.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //UserPermission::checkStaffPermission();
        $criticisms = Criticism::where('is_read', '=', 'N')->with(['user'=>function($query){
            $query->select(['id','email','name']);
        }])->get()->toArray();
        return $this->changeTypeIdToTypeNameAndRemoveUnUsedField($criticisms);
    }

    private function changeTypeIdToTypeNameAndRemoveUnUsedField($criticism=[])
    {
        foreach ($criticism as $key=>$value) {
            $criticism[$key]['type'] = self::$types[$criticism[$key]['type']]['value'];
        }
        return JsonUtil::removeFields($criticism,['*.user_id','*.updated_at','*.user.id']);
    }

    /**
     * return a list of Criticisms by type
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function getCriticismByType(Request $request)
    {
        return Criticism::where('is_read', '=', 'N')->where('type', '=', $request->input('type'))->get();
    }

    /**
     * get types of Criticism
     * @return array
     */
    public function getTypes()
    {
        return self::$types;
    }

    public function unknownCriticism (Requests\StoreCriticismRequest $request)
    {
        Criticism::create($request->all());
        return $this->respondOk("unknown criticism successfully added");
    }

    public function userCriticism(Requests\StoreCriticismRequest $request)
    {
        $validator = Validator::make($request->all(),[
            "type" => "required",
            "body" => "required"
        ]);
        if($validator->fails()){
            return $this->setStatusCode(404)->respondWithError($validator->errors());
        }
        $user = $this->auth->user();
        $user->criticism()->create($request->all());
        return $this->respondOk("user criticism successfully added");
    }

    /**
     * set a specific Criticism's is_read to R
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\NotStaffException
     */
    public function setCriticismToRead($id)
    {
        Criticism::where('id', $id)->update(['is_read' => 'R']);
        return $this->respondOk('successful');
    }

    /**
     * Remove the specified Criticism from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Criticism::where('id', '=', $id)->delete();
        return $this->respondOk('deleted successfully');
    }
}