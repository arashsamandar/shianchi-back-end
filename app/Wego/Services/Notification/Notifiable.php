<?php

namespace Wego\Services\Notification;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 03/09/17
 * Time: 11:36
 */
interface Notifiable
{
    public function send();
    public function setMessage($message);
    public function setReceiver($receiverId);
}