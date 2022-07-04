<?php

use Illuminate\Database\Seeder;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Department::insert([
            [
                'department_name'=>'فروش',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],
            [
                'department_name'=>'فروش(۲)',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],
            [
                'department_name'=>'فروش(۳)',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],
            [
                'department_name'=>'حسابداری',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],
            [
                'department_name'=>'روابط عمومی',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],
            [
                'department_name'=>'بازاریابی',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],
            [
                'department_name'=>'بین الملل',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],
            [
                'department_name'=>'آی تی',
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ],

        ]);
    }
}
