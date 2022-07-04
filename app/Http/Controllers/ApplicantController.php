<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Wego\Recruitment\ApplicantValidator;
use Wego\Recruitment\WegoRecruitment;

class ApplicantController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $wr = new WegoRecruitment();
        return $wr->getAllApplicants();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = (new ApplicantValidator())->validate($request->toArray());
        if($validation->fails())
            return $this->setStatusCode(400)->respondWithError('bad request'.$validation->errors());
        $wr = new WegoRecruitment();
        return $wr->addApplicant($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $wr = new WegoRecruitment();
        return $wr->getApplicant($id);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $wr = new WegoRecruitment();
        return $wr->deleteApplicant($id);
    }

    public function getRecruitmentDetails(){
        $wr = new WegoRecruitment();
        return $wr->getRecruitmentDetails();
    }

    public function search(Request $request){
        $wr = new WegoRecruitment();
        return $wr->search($request->toArray());
    }
}
