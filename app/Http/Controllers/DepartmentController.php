<?php

namespace App\Http\Controllers;

use App\Department;
use Illuminate\Http\Request;

use App\Http\Requests;
use Wego\Store\StoreEditor;
use Wego\UserHandle\UserPermission;

class DepartmentController extends Controller
{
    public function index(){
        return Department::getAllDepartments();
    }
    public function deleteDepartmentManagerPicture(Request $request){
        (new StoreEditor())->deleteDepartmentManagerPicture($request);
    }
}
