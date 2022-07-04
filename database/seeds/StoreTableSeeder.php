<?php

use Illuminate\Database\Seeder;

class StoreTableSeeder extends Seeder
{
    private $stores = [
        0 => [
            'name' =>'nikeStore',
            'email' =>'store@1.com',

        ],
        1 => [
            'name' =>'custom store',
            'email' =>'store@2.com',
        ]
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\Store',10)
            ->create()
            ->each(function($u){
                $u->user()->save(factory('App\User')->make());
                for($i = 0; $i < 2; ++$i){
                    $u->store_phones()->save(factory('App\StorePhone')->make());
                    $u->departments()->attach(random_int(2,7)*$i+1,[
                        'department_manager_first_name' => "joli",
                        'department_manager_last_name' => "fodi", 'department_email' => "li@g.com",
                        'department_manager_picture' => "/store/1.png"]);
                    $u->manager_mobiles()->save(factory('App\ManagerMobile')->make());
                    $u->work_times()->save(factory('App\WorkTime')->make());
                }
                $u->pictures()->save(factory('App\StorePicture')->make());
                $u->store_phones()->save(factory('App\StorePhone')->make(['type'=>1]));
            });
        $this->createCustomStore(0);
        $this->createCustomStore(1);
    }

    private function createCustomStore($index){
        $store = factory('App\Store')->create();
        $store->user()->create(['name'=>$this->stores[$index]['name'],
            'email'=>$this->stores[$index]['email'],'password'=>bcrypt(12)]);
        
        for($i = 0; $i < 2; ++$i){
            $store->store_phones()->save(factory('App\StorePhone')->make());

            $store->departments()->attach(random_int(2,7)*$i+1,[
                'department_manager_first_name' => "joli",
                'department_manager_last_name' => "fodi",
                'department_email' => "li@g.com",
                'department_manager_picture' => "/store/1.png"
            ]);
        }

        (new \Wego\Store\WorkTimeHandler)->save($this->getCustomWorkTimes(),$store);
        $store->pictures()->save(factory('App\StorePicture')->make());
        $store->store_phones()->save(factory('App\StorePhone')->make(['type'=>1]));

    }

    private function getCustomWorkTimes()
    {
        $days = ['شنبه','یکشنبه','دوشنبه','سه شنبه','چهارشنبه','پنج شنبه','جمعه'];
        $array = [];
        foreach ($days as $day) {
            $array[] = [
                'day'=>$day,'opening_time'=>rand(-1,5),'closing_time'=>rand(-1,2)
            ];
        }
        return $array;
    }
}
