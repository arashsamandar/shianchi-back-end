<?php
namespace Wego;

use App\User;
use Tymon;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 2/15/16
 * Time: 12:58 PM
 */
class AuthenticateResponseFormat
{
    protected $user, $token;

    function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function format()
    {
        $type = $this->removeAppPrefix($this->user['userable_type']);

        return [
            'token' => $this->token,
            'type' => $type,
            'name' => $this->user['name']
        ];
    }

    private function removeAppPrefix($type)
    {
        return strtolower(substr($type, 4));
    }


}