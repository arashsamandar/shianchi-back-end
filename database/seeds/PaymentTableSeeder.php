<?php

use Illuminate\Database\Seeder;

class PaymentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payments')->insert([
            ['name'=>'پرداخت آنلاین'],
            ['name'=>'پرداخت نقدی در محل'],
            ['name'=>'پرداخت کارتی در محل']
        ]);
    }
}
