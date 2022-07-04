<?php

use Illuminate\Database\Seeder;

class StaffTableSeeder extends Seeder
{
     public static $localStaffsEmail=[
        'staff@1.com','ahmad@1.com','mahdi@1.com','mojtaba@1.com',
        'sina@1.com','hosein@1.com'
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::$localStaffsEmail as $email) {
            factory('App\Staff')
                ->create()
                ->user()
                ->create([
                    'name'=>explode('@',$email)[0],
                    'email'=>$email,
                    'password'=>bcrypt(12)
                ]);

        }
        factory('App\Staff')->create()->user()->create(['name'=>'zizo','email'=>'bazaar1Staff@1.com','password'=>bcrypt(12)]);
        factory('App\Staff')->create()->user()->create(['name'=>'zizo2','email'=>'bazaar2Staff@1.com','password'=>bcrypt(12)]);
    }
}
