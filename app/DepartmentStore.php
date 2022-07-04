<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepartmentStore extends Model
{
    protected $table = "department_store";
    protected $guarded = ["id","store_id"];
}
