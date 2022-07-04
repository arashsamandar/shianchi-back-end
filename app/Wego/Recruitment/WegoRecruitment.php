<?php

namespace Wego\Recruitment;


use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

/**
 * Created by PhpStorm.
 * User: wb-2
 * Date: 7/18/16
 * Time: 1:21 PM
 */
class WegoRecruitment
{
    const INDEX_NAME = 'wego_recruitment';
    const APPLICANT = 'applicant';
    protected $client;
    protected $matchFilters = [
        "major","education_place"
    ];
    protected $queryExistFilters = [
        "company_name",'work_experience'
    ];
    protected $queryRangeFilters = [
        'english_listening','english_reading','english_speaking','english_writing',

    ];

    public function __construct(){
        $this->client = ClientBuilder::create()->build();
    }

    public function addApplicant(Request $request){
        $params = [
            'index' => self::INDEX_NAME,
            'type' => self::APPLICANT,
            'body' => $this->fillBodyAddCommand($request)
        ];
        $response = $this->client->index($params);
        return $response;
    }

    public function getAllApplicants(){
        $params = [
            'index' => self::INDEX_NAME,
            'type' => self::APPLICANT,
            'body'=> []
        ];
        $response = $this->client->search($params);
        return $response;
    }

    public function getApplicant($id){
        $params = [
            'index' => self::INDEX_NAME,
            'type' => self::APPLICANT,
            'id' => $id
        ];
        $response = $this->client->get($params);
        return $response;
    }

    public function deleteApplicant($id){
        $params = [
            'index' => self::INDEX_NAME,
            'type' => self::APPLICANT,
            'id' => $id,
        ];
        $response = $this->client->delete($params);
        return $response;
    }

    public function initIndex(){
        $indexParams = [];
        $indexParams['index'] = self::INDEX_NAME;
        $this->client->indices()->create($indexParams);
    }

    public function deleteIndex(){
        $deleteParams = [];
        $deleteParams['index'] = self::INDEX_NAME;
        $this->client->indices()->delete($deleteParams);
    }

    public function getRecruitmentDetails(){

        return [
            'gender' => ["مرد","زن"],
            'marital_status' => ['مجرد','متاهل'],
            'military_service_status' => [
                'پایان خدمت','معافیت/دائم',
                'معافیت/کفالت' ,'معافیت/تحصیلی','معافیت/پزشکی','عدم مشمولیت' ,'سایر'
            ],
            'diploma_level'=>['زیر دیپلم','دیپلم','کاردانی','کارشناسی','کارشناسی ارشد','دکتری'],
            'available_days'=>["شنبه","یکشنبه","دوشنبه","سه‌شنبه","چهارشنبه","پنجشنبه","جمعه"],
            'skills' => [
                'php','javascript','HTML','CSS','SEO','Server Administrator','Object Oriented Design','SQL',
                'Linux','Security','ios','Android',
                'فروش','بازاریابی','کارشناس خرید','مدیریت منابع انسانی','ms project','حسابداری','بازرگانی',
                'پشتیبانی فنی','نگهبانی','مسئول خدماتی دفتر','انبار و لجستیک','تولید محتوا','سایر'
            ],
            'acquaintance' => ['آشنایان','وب‌سایته‌های استخدامی','آگهی روزنامه','جستجوی اینترنتی','پرسنل ویگو بازار',]
        ];
    }

    public function search($filters){
        $shoulds = [];
        $exists = [];
        $ranges = [];
        $filteredContent = [];
        foreach ($filters as $filter => $filterValue) {
            if(in_array($filter,$this->matchFilters)){
                $shoulds[] = ["match" => [$filter => $filterValue]];
            }elseif(in_array($filter,$this->queryExistFilters)){
                $exists[] = ["exists" => ["field" => $filter]];
            }elseif(in_array($filter,$this->queryRangeFilters)){
                $ranges[] = $this->rangeFormat($filter,$filterValue);
            }else{
                $shoulds[] = ["term" => [$filter => $filterValue]];
            }
        }
        $filteredContent = $this->addShouldsIfNotEmpty($filteredContent,$shoulds);
        $filteredContent = $this->addExistsIfNotEmpty($filteredContent,$exists);
        $filteredContent = $this->addRangesIfNotEmpty($filteredContent,$ranges);
        $params = [
            'index' => self::INDEX_NAME,
            'type' => self::APPLICANT,
            'body' => [
                'query' => ['filtered' => $filteredContent]
            ]
        ];
        $result = $this->client->search($params);
        return $result;
    }

    private function fillBodyAddCommand(Request $request){
        return [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'gender' => $request->input('gender'),
            'marital_status' => $request->input('marital_status'),
            'military_service_status' => $request->input('military_service_status'),
            'birth_year' => $request->input('birth_year'),
            'birth_month' => $request->input('birth_month'),
            'birth_day' => $request->input('birth_day'),
            'landline_number' => $request->input('landline_number'),
            'mobile_number' => $request->input('mobile_number'),
            'emergency_number' => $request->input('emergency_number'),
            'email' => $request->input('email'),
            'major' => $request->input('major'),
            'diploma_level' => $request->input('diploma_level'),
            'education_place' => $request->input('education_place'),
            'continuing_education' => $request->input('continuing_education'),
            'available_days' => $this->fillAvailableDays($request->input('available_days')) ,
            'english_listening' => $request->input('english_listening'),
            'english_reading' => $request->input('english_reading'),
            'english_speaking' => $request->input('english_speaking'),
            'english_writing' => $request->input('english_writing'),
            'non_english_languages' => $request->input('non_english_languages'),
            'smoke' => $request->input('smoke'),
            'driving_license' => $request->input('driving_license'),
            'has_car' => $request->input('has_car'),
            'work_experience' => $request->input('work_experience'),
            'skills' => $request->input('skills'),
            'acquaintance' => $request->input('acquaintance'),
            'night_shift' => $request->input('night_shift'),
            'further_notes' => $request->input('further_notes'),
            'resume_file_path' => $request->input(('resume_file_path')),
        ];
    }

    private function fillAvailableDays($days){
        $trueArray = array_fill(0,count($days),true);
        $result = array_combine($days,$trueArray);
        return $result;
    }

    private function addShouldsIfNotEmpty($filteredContent, $shoulds){
        if(count($shoulds) != 0){
            $filteredContent['query'] = $shoulds;
        }
        return $filteredContent;
    }

    private function addExistsIfNotEmpty($filteredContent, $exists){
        if(count($exists) != 0){
            $filteredContent['filter'] = $exists;
        }
        return $filteredContent;
    }

    private function addRangesIfNotEmpty($filteredContent, $ranges){
        if(count($ranges) != 0){
            $filteredContent['query'] = $ranges;
        }
        return $filteredContent;
    }

    private function rangeFormat($filter, $filterValue){
        $ranges = explode("_",$filterValue);
        if(!is_numeric($ranges[0]) && !is_numeric($ranges[1]))
            return null;
        $result = [];
        if(strcmp($ranges[0],'-1'))
            $result['gte'] = $ranges[0];
        if(strcmp($ranges[1],'-1'))
            $result['lte'] = $ranges[1];
        return [
            'range' => [
                $filter => $result
            ]
        ];
    }
}