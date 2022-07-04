<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Wego\UserHandle\StaffCreator;
use Wego\UserHandle\UserPermission;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon;
class StaffController extends ApiController
{
    protected $userPermission;
    protected $authenticatedUser;
    protected $manipulateStaff;
    public function __construct(UserPermission $userPermission, StaffCreator $manipulateStaff)
    {
        $this->userPermission = $userPermission;
        $this->manipulateStaff = $manipulateStaff;
        $this->middleware('jwt.auth');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }


    public function store(Request $request)
    {
        $input = $request->all();

        $this->setAuthenticatedUser(JWTAuth::parseToken()->authenticate());
        if (!$this->getAuthenticatedUser()) {
            return $this->setStatusCode(404)->respondNotFound('User Not Found');
        }

        $user = $this->getAuthenticatedUser();
        $this->userPermission->setUser($user);

        if($this->userPermission->hasCreateStaffAbility())
        {
            $this->manipulateStaff->create($input);
            return $this->respondOk('staff successfully added','message');
        }

        return $this->respondUnAuthorized();

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function getAuthenticatedUser()
    {
        return $this->authenticatedUser->toArray();
    }
    /**
     * @param mixed $authenticatedUser
     */
    public function setAuthenticatedUser($authenticatedUser)
    {
        $this->authenticatedUser = $authenticatedUser;
    }
}
