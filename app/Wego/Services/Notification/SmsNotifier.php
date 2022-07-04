<?php
namespace Wego\Services\Notification;


use App\Http\Requests;
use Kavenegar\KavenegarApi;

class SmsNotifier implements Notifiable
{
    protected $sender, $receiverId, $message;

    function __construct()
    {
        $this->sender = env('SMS_NUMBER');
    }

    public function send()
    {
        $client = new KavenegarApi(env('SMS_API_KEY'));
        $client->Send($this->sender, $this->receiverId, $this->message);
    }

    public function sendOrder($receptor,$token) {
        $client = new KavenegarApi(env('SMS_API_KEY'));
        $client->VerifyLookup($receptor,$token,null,null,"onOrder","sms");
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function setReceiver($receiverId)
    {
        $this->receiverId = $receiverId;
        return $this;
    }
}
