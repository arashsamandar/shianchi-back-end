<?php

use Illuminate\Database\Seeder;

class MenuJsonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        file_put_contents(public_path().'/menuJSON.json',"");
    }
}
