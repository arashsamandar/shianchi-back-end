<?php

use Illuminate\Database\Seeder;

class ValueTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    	factory('App\Value')->create([
    		'name' => '4 گیگاهرتز' ,
    		'specification_id' => '1'
    		]);
    	factory('App\Value')->create([
    		'name' => '۲ گیگا هرتز' ,
    		'specification_id' => '1'
    		]);
    	factory('App\Value')->create([
    		'name' => '۸ گیگا هرتز' ,
    		'specification_id' => '1'
    		]);
    	factory('App\Value')->create([
    		'name' => '۱۶ گیگاهرتز' ,
    		'specification_id' => '1'
    		]);


    	factory('App\Value')->create([
    		'name' => 'کوچک' ,
    		'specification_id' => '2'
    		]);
    	factory('App\Value')->create([
    		'name' => 'بزرگ' ,
    		'specification_id' => '2'
    		]);
    	factory('App\Value')->create([
    		'name' => 'متوسط' ,
    		'specification_id' => '2'
    		]);




    	factory('App\Value')->create([
    		'name' => '۱ گیگ' ,
    		'specification_id' => '3'
    		]);
    	factory('App\Value')->create([
    		'name' => '۲ گیگ' ,
    		'specification_id' => '3'
    		]);
    	factory('App\Value')->create([
    		'name' => '۴ گیگ' ,
    		'specification_id' => '3'
    		]);
    	factory('App\Value')->create([
    		'name' => '۸ گیگ' ,
    		'specification_id' => '3'
    		]);
    	factory('App\Value')->create([
    		'name' => '۱۶ گیگ' ,
    		'specification_id' => '3'
    		]);
    	factory('App\Value')->create([
    		'name' => '۳۲ گیگ' ,
    		'specification_id' => '3'
    		]);


		factory('App\Value')->create([
			'name' => 'گالینگور' ,
			'specification_id' => '7'
		]);

		factory('App\Value')->create([
			'name' => 'شومیز' ,
			'specification_id' => '7'
		]);



//////////////////////////////////8//////////////////////
		factory('App\Value')->create([
			'name' => 'جیبی' ,
			'specification_id' => '8'
		]);

		factory('App\Value')->create([
			'name' => 'وزیری' ,
			'specification_id' => '8'
		]);

		factory('App\Value')->create([
			'name' => 'پالتویی' ,
			'specification_id' => '8'
		]);

		factory('App\Value')->create([
			'name' => 'رقعی' ,
			'specification_id' => '8'
		]);

		factory('App\Value')->create([
			'name' => 'خشتی' ,
			'specification_id' => '8'
		]);


		///////////////////////////////////
		factory('App\Value')->create([
			'name' => 'عاشقانه' ,
			'specification_id' => '12'
		]);
		factory('App\Value')->create([
			'name' => 'اجتماعی' ,
			'specification_id' => '12'
		]);
		factory('App\Value')->create([
			'name' => 'داستانی' ,
			'specification_id' => '12'
		]);
		factory('App\Value')->create([
			'name' => 'داستان خارجی' ,
			'specification_id' => '12'
		]);

		///////////////////////////////////
		factory('App\Value')->create([
			'name' => 'کنکوری' ,
			'specification_id' => '13'
		]);
		factory('App\Value')->create([
			'name' => 'دبستانی' ,
			'specification_id' => '13'
		]);
		factory('App\Value')->create([
			'name' => 'راهنمایی' ,
			'specification_id' => '13'
		]);
		factory('App\Value')->create([
			'name' => 'ابتدایی' ,
			'specification_id' => '13'
		]);

		///////////////////////////////////
		factory('App\Value')->create([
			'name' => 'مهندسی کامپیوتر' ,
			'specification_id' => '14'
		]);
		factory('App\Value')->create([
			'name' => 'مهندسی عمران' ,
			'specification_id' => '14'
		]);
		factory('App\Value')->create([
			'name' => 'مهندسی صنایع' ,
			'specification_id' => '14'
		]);
		factory('App\Value')->create([
			'name' => 'مهندسی هسته ای' ,
			'specification_id' => '14'
		]);

		////////////////14
		factory('App\Value')->create([
			'name' => 'بزرگ' ,
			'specification_id' => '7'
		]);
		factory('App\Value')->create([
			'name' => 'معمولی' ,
			'specification_id' => '7'
		]);
		factory('App\Value')->create([
			'name' => 'دنده ای' ,
			'specification_id' => '7'
		]);
		factory('App\Value')->create([
			'name' => 'اتوماتیک' ,
			'specification_id' => '7'
		]);
		factory('App\Value')->create([
			'name' => 'صندوقدار' ,
			'specification_id' => '7'
		]);
		////19
		factory('App\Value')->create([
			'name' => '۲ عدد' ,
			'specification_id' => '8'
		]);
		factory('App\Value')->create([
			'name' => '۳ عدد' ,
			'specification_id' => '8'
		]);
		factory('App\Value')->create([
			'name' => '۴ عدد' ,
			'specification_id' => '8'
		]);

		///22

		factory('App\Value')->create([
			'name' => 'دارد' ,
			'specification_id' => '9'
		]);
		factory('App\Value')->create([
			'name' => 'ندارد' ,
			'specification_id' => '9'
		]);


    }
}
