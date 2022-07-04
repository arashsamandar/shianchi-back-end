<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(BazaarTableSeeder::class);
        $this->call(DepartmentTableSeeder::class);
        $this->call(PaymentTableSeeder::class);
        $this->call(StoreTableSeeder::class);
        $this->call(BuyerTableSeeder::class);


        $this->call(CategoryTableSeeder::class);
        $this->call(BrandTableSeeder::class);
        $this->call(TitleTableSeeder::class);
        $this->call(SpecificationTableSeeder::class);
        $this->call(ValueTableSeeder::class);
        //$this->call(CategorySpecificationTableSeeder::class);

        $this->call(ColorTableSeeder::class);

        $this->call(ProductTableSeeder::class);
        //$this->call(ProductValueTableSeeder::class);
        //$this->call(ColorProductTableSeeder::class);

        $this->call(ScoreTitleTableSeeder::class);
        $this->call(StaffTableSeeder::class);
        $this->call(RoleTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);
        $this->call(PaymentTableSeeder::class);
        $this->call(ViewCountSeeder::class);
        $this->call(StoreMappingCreator::class);
        $this->call(ProductMappingCreator::class);
        $this->call(CategoryMappingCreator::class);
        $this->call(OrderMappingCreator::class);
        $this->call(BazaarStoreTableSeeder::class);

    }
    private function truncateAllTables()
    {
        //NOTE: DONT TOUCH ORDERS OF THIS ARRAY
        //IF YOU NEED TO ADD TABLE TO TRUNCATE BE CAREFUL OF ORDERS OF IT IN ARRAY

        $toTruncates = [
            'order_product','address_order','payments'/*,'permission_role','role_user','permissions','roles','bazaar_staff','staffs','score_title',
            'color_product','product_value','product_pictures','special_conditions',
            'products','colors','category_specification', 'categories','values',
            'specifications','buyer_addresses','wego_coins','buyers','category_store',
            'store_phones','department_store','manager_mobiles','work_times',
            'store_pictures','stores','payments','departments','bazaars','users'*/
        ];
        foreach ($toTruncates as $toTruncate) {
            DB::table($toTruncate)->truncate();
            var_dump($toTruncate.' completed :) ');
        }

    }
}
