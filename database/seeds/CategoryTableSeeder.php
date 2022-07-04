<?php

use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\Category')->create([
            'english_path' => 'ELECTRONICS',
            'path' => 'الکترونیک',
            'name' => 'electronics',
            'persian_name' => 'الکترونیک',
            'menu_id' => "0_0"

        ]);

        factory('App\Category')->create([
            'english_path' => 'ELECTRONICS_TELEVISION',
            'path' => 'الکترونیک_تلویزیون',
            'name' => 'television',
            'persian_name' => 'تلویزیون',
            'menu_id' => "0_0_0"
        ]);
        factory('App\Category')->create([
            'english_path' => 'ELECTRONICS_TELEVISION_TUBE',
            'path' => 'الکترونیک_تلویزیون_تیوب',
            'name' => 'tube',
            'isLeaf' => 1,
            'persian_name' => 'تیوب',
            'menu_id' => "0_0_0_0"

        ]);
        factory('App\Category')->create([
            'english_path' => 'ELECTRONICS_TELEVISION_LCD',
            'path' => 'الکترونیک_تلویزیون_ال سی دی',
            'name' => 'lcd',
            'isLeaf' => 1,
            'persian_name' => 'ال سی دی',
            'menu_id' => "0_0_0_1"
        ]);
        factory('App\Category')->create([
            'english_path' => 'ELECTRONICS_TELEVISION_PLASMA',
            'path' => 'الکترونیک_تلویزیون_پلاسما',
            'name' => 'plasma',
            'isLeaf' => 1,
            'persian_name' => 'پلاسما',
            'menu_id' => "0_0_0_2"

        ]);
        factory('App\Category')->create([
            'english_path' => 'ELECTRONICS_PORTABLE-ELECTRONICS',
            'path' => 'الکترونیک_الکترونیک همراه',
            'name' => 'portable-electronics',
            'persian_name' => 'الکترونیک همراه',
            'menu_id' => "0_0_1"
        ]);

        factory('App\Category')->create([
            'english_path' => 'ELECTRONICS_PORTABLE-ELECTRONICS_MP3-PLAYER',
            'path' => 'الکترونیک_الکترونیک همراه_ام پی تری پلیر',
            'name' => 'mp3-player',
            'isLeaf' => 1,
            'persian_name' => 'ام پی تری پلیر',
            'menu_id' => "0_0_1_0"
        ]);

        factory('App\Category')->create([
            'english_path' => 'ELECTRONICS_PORTABLE-ELECTRONICS_MP3-PLAYER_FLASH',
            'path' => 'الکترونیک_الکترونیک همراه_ام پی تری پلیر_فلش',
            'unit' => 'عدد',
            'name' => 'flash',
            'isLeaf' => 1,
            'persian_name' => 'فلش',
            'menu_id' => "0_0_1_0_0"

        ]);
        factory('App\Category')->create([
            'english_path' => 'ELECTRONICS_PORTABLE-ELECTRONICS_CD-PLAYER',
            'path' => 'الکترونیک_الکترونیک همراه_پخش کننده سی دی',
            'unit' => 'تن',
            'name' => 'cd player',
            'isLeaf' => 1,
            'persian_name' => 'پخش کننده سی دی',
            'menu_id' => "0_0_1_1"

        ]);
        factory('App\Category')->create([
            'english_path' => 'ELECTRONICS_PORTABLE-ELECTRONICS_2-WAY-RADIO',
            'path' => 'الکترونیک_الکترونیک همراه_رادیو ۲ راه',
            'name' => ' 2 way radio',
            'isLeaf' => 1,
            'persian_name' => 'رادیو ۲ راه',
            'menu_id' => "0_0_1_2"
        ]);
        factory('App\Category')->create([
            'english_path' => 'BOOKS_GENERAL-BOOKS_NOVEL',
            'path' => 'کتاب_کتاب های عمومی_رمان',
            'name' => 'novel',
            'isLeaf' => 1,
            'persian_name' => 'رمان',
            'menu_id' => "0_0_2"
        ]);
        factory('App\Category')->create([
            'english_path' => 'BOOKS_GENERAL-BOOKS_NOVEL',
            'path' => 'کتاب_کتاب های عمومی_درسی',
            'name' => 'teaching',
            'isLeaf' => 1,
            'persian_name' => 'درسی',
            'menu_id' => "0_0_3"
        ]);
        factory('App\Category')->create([
            'english_path' => 'BOOKS_GENERAL-BOOKS_UNIVERSITY',
            'path' => 'کتاب_کتاب های عمومی_دانشگاهی',
            'name' => 'university',
            'isLeaf' => 1,
            'persian_name' => 'دانشگاهی',
            'menu_id' => "0_0_4"
        ]);

    }
}
