<?php

use App\Permission;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = self::getPermissions();
        foreach($permissions as $permission){
            if(Permission::where('name','=',$permission)->first() == null){
                Permission::create(['name' => $permission ]);
            }
        }
    }

    private static function getPermissions()
    {
        $reflectionClass = new ReflectionClass(Permission::class);
        $permissions = $reflectionClass->getConstants();
        return array_except($permissions,[strtoupper(Permission::CREATED_AT),strtoupper(Permission::UPDATED_AT)]);
    }
}
