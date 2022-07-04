<?php

use Illuminate\Database\Seeder;

class ScoreTitleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\ScoreTitle')->create([
            'name' => 'زیبایی' ,
        ]);
        factory('App\ScoreTitle')->create([
            'name' => 'قیمت' ,
        ]);
        factory('App\ScoreTitle')->create([
            'name' => 'کارآیی' ,
        ]);
        factory('App\ScoreTitle')->create([
            'name' => 'کیفیت' ,
        ]);
    }
}
