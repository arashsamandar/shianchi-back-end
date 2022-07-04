<?php

use Illuminate\Database\Seeder;

class SpecificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\Specification')->create([
            'name' => 'فرکانس cpu',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 1,
            'multi_value' => 1,
            'searchable' => 1,
            'category_id' => 9,
            'title_id'=>1
        ]);
        factory('App\Specification')->create([
            'name' => 'سایز',
            'is_text_field' => 1,
            'important' => 0,
            'for_buy' => 1,
            'multi_value' => 1,
            'searchable' => 1,
            'category_id' => 5,
            'title_id'=>4
        ]);
        factory('App\Specification')->create([
            'name' => 'گنجایش',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 1,
            'multi_value' => 0,
            'searchable' => 1,
            'category_id' => 9,
            'title_id'=>2
        ]);
        factory('App\Specification')->create([
            'name' => 'ضد آب',
            'is_text_field' => 0,
            'important' => 1,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 0,
            'category_id' => 6,
            'title_id'=>3
        ]);
        factory('App\Specification')->create([
            'name' => 'نوع کابل',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 1,
            'multi_value' => 1,
            'searchable' => 1,
            'category_id' => 2,
            'title_id'=>2
        ]);
        factory('App\Specification')->create([
            'name' => 'بینش',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 1,
            'multi_value' => 1,
            'searchable' => 1,
            'category_id' => 2,
            'title_id'=>4
        ]);

        /////7

        factory('App\Specification')->create([
            'name' => 'نوع ماشین',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 1,
            'searchable' => 0,
            'category_id' => 4,
            'title_id'=>5
        ]);
        factory('App\Specification')->create([
            'name' => 'تعداد چرخ',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 0,
            'category_id' => 4,
            'title_id'=>5
        ]);
        factory('App\Specification')->create([
            'name' => 'سگ دست',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 1,
            'category_id' => 4,
            'title_id'=>5
        ]);
        factory('App\Specification')->create([
            'name' => 'نام راننده',
            'is_text_field' => 1,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 0,
            'category_id' => 4,
            'title_id'=>6
        ]);
        factory('App\Specification')->create([
            'name' => 'تحصیلات راننده',
            'is_text_field' => 1,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 0,
            'category_id' => 4,
            'title_id'=>6
        ]);
        factory('App\Specification')->create([
            'name' => 'پلاک',
            'is_text_field' => 1,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 0,
            'category_id' => 4,
            'title_id'=>5
        ]);

//7
        factory('App\Specification')->create([
            'name' => 'نوع جلد',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 1,
            'category_id' => 11,
            'title_id'=>4
        ]);
        factory('App\Specification')->create([
            'name' => 'قطع کتاب',
            'is_text_field' => 0,
            'important' => 1,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 1,
            'category_id' => 11,
            'title_id'=>4
        ]);
        factory('App\Specification')->create([
            'name' => 'تعداد صفحات',
            'is_text_field' => 1,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 0,
            'category_id' => 11,
            'title_id'=>4
        ]);
        factory('App\Specification')->create([
            'name' => 'نام نویسنده',
            'is_text_field' => 1,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 0,
            'category_id' => 11,
            'title_id'=>4
        ]);
        //11
        factory('App\Specification')->create([
            'name' => 'نام مترجم',
            'is_text_field' => 1,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 0,
            'category_id' => 11,
            'title_id'=>4
        ]);
        //12
        factory('App\Specification')->create([
            'name' => 'نوع',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 1,
            'category_id' => 11,
            'title_id'=>4
        ]);
        factory('App\Specification')->create([
            'name' => 'نوع',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 1,
            'category_id' => 12,
            'title_id'=>4
        ]);
        factory('App\Specification')->create([
            'name' => 'نوع',
            'is_text_field' => 0,
            'important' => 0,
            'for_buy' => 0,
            'multi_value' => 0,
            'searchable' => 1,
            'category_id' => 13,
            'title_id'=>4
        ]);
    }
}

