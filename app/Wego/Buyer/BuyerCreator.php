<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 12/27/15
 * Time: 4:34 PM
 */

namespace Wego\Buyer;

use App\Buyer;
use App\Http\Requests\Request;
use App\Role;
use App\User;
use App\UserValidToken;
use Illuminate\Support\Facades\DB;
use Mockery\ReceivedMethodCalls;
use Wego\GenerateToken;
use Wego\AuthenticateResponseFormat;

use Wego\UserHandle\PersonCreator;
class BuyerCreator implements PersonCreator
{
    protected $buyer;
    public function create($request)
    {
        DB::transaction(function() use ($request){
            $this->buyer = Buyer::create(array_except($request,['image_path','g-recaptcha-response']));
            $this->buyer->user()->create([
                'email' => $request['email'] ,
                'password' => bcrypt($request['password'])]);
            $this->buyer->user->attachRole(4);
        });

        $credentials = [
            'email' => $request['email'],
            'password' => $request['password']
        ];
        $token = (new GenerateToken($credentials))->getToken();
        UserValidToken::create(['user_email' => $this->buyer->user->email , 'token' => $token]);
        return  (new AuthenticateResponseFormat($this->buyer->user,$token))->format();
    }

    public function update(array $request, $id)
    {

    }

    public function show($id)
    {

        $buyer = Buyer::find($id);
        if(! $buyer)
            return [
                'type' => 'error',
                'message' => 'Buyer Not Found'
            ];
        return [
            'type' => 'message',
            'message' => $this->buyerTransformer->transform($buyer)
        ];

    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }
}