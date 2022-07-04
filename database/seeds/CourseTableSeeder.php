<?php

use App\Course;
use Illuminate\Database\Seeder;

class CourseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Course::create(['name' => 'Html-Css']);
        Course::create(['name' => 'Javascript']);
        Course::create(['name' => 'Javascript']);
    }
}
