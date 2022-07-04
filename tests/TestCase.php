<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * @param $email
     * @param $password
     * @return mixed
     */
    public function login($email,$password){
        $response=$this->action('POST','AuthenticateController@store',[
            'email'=>$email,'password'=>$password]);
        $token=\GuzzleHttp\json_decode($response->content(),true)['token'];
        return $token;
    }

}

