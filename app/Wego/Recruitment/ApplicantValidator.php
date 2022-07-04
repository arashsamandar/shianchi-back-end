<?php
/**
 * Created by PhpStorm.
 * User: wb-2
 * Date: 7/21/16
 * Time: 11:49 AM
 */

namespace Wego\Recruitment;


use Illuminate\Support\Facades\Validator;
use Wego\UserHandle\Validateable;

class ApplicantValidator implements Validateable
{

    public function validate($request)
    {
        return Validator::make($request,[
            "last_name" => "required",
            "gender" => "required",
            "marital_status" => "required",
            "military_service_status" => "required",
            "birth_year" => "required",
            "birth_month" => "required",
            "birth_day" => "required",
            "landline_number" => "required",
            "mobile_number" => "required",
            "emergency_number" => "required",
            "email" => "required",
            "major" => "required",
            "diploma_level" => "required",
            "education_place" => "required",
            "continuing_education" => "required",
            "english_listening"=> "required",
            "english_reading"=> "required",
            "english_speaking"=> "required",
            "english_writing"=> "required",
            "smoke" => "required",
            "driving_license" => "required",
            "has_car" => "required",
            "nigh_shift" => "required",
            //TODO : sare in comment shodeha error mide nemidunam chera
//            "skills.skill_name" => "required",
//            "skills.knowledge_level" => "required",
//            "skills.has_certificate" => "required",
//            'acquaintance.presenter' => "required",
//            'acquaintance.personnel_name' => "required",
        ]);
    }
}