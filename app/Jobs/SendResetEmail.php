<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;

class SendResetEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    protected $to;
    protected $token;

    /**
     * must call php artisan queue:listen
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $resetLink = "https://shianchi.com/reset-password?token=".$this->token;
        Mail::send('email.forgetPassword',['reset_link'=>$resetLink],function($message){
            $message->to($this->to)->subject(
                Lang::get('generalMessage.ForgotEmailSubject')
            );
        });

    }

    /**
     * @param mixed $to
     * @return SendResetEmail
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @param mixed $token
     * @return SendResetEmail
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
}
