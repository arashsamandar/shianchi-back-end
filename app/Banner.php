<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    const SLIDER_BANNER = 'slider';
    const TOP_RIGHT_BANNER = 'top_right';
    const MOBILE_SLIDER_BANNER = 'mobile_slider';
    const MOBILE_TOP_RIGHT_BANNER = 'mobile_top_right';
    const MIDDLE_BANNER = 'middle';

    protected $fillable = ['type','link','alt','path','priority'];

}
