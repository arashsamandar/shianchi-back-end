<?php

use Illuminate\Database\Seeder;

class SpecialConditionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\SpecialCondition',10)->create();
    }
}
