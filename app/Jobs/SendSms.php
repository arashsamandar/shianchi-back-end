<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kavenegar\KavenegarApi;

class SendSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    protected $sender, $receiverId, $message;

    function __construct()
    {
        $this->sender = "10008445"; // I changed it fron env('SMS_NUMBER'); to the string
    }

    public function handle()
    {
        $client = new KavenegarApi(env('SMS_API_KEY'));
        $client->Send($this->sender, $this->receiverId, $this->message);
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
