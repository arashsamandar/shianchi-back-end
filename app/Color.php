<?php

namespace App;

use App\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\ColorIsUsingCanNotUpdateOrDelete;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Color extends Model
{
    use softDeletes;
    protected $fillable = ['english_name', 'persian_name', 'code'];
    protected $hidden = ['created_at','updated_at','deleted_at','name'];

    public function categories()
    {
        $this->belongsToMany(Category::class);
    }

    public static function isUsed($id)
    {
        if (DB::table('color_product')->where('color_id', $id)->first() != null) {
            (new ApiController())->respondWithError('رنگ مورد نظر در حال استفاده می باشد');
        }
    }

}
