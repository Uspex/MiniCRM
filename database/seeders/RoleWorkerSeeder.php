<?php

namespace database\seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleWorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', 'worker')->first();
        if($role) {
            return;
        }

        $role = Role::create([
            'name' => 'worker',
            'guard_name' => 'web',
        ]);

        $permissionGroupList = Permission::getPermissionGroupList();

        foreach ($permissionGroupList as $permissionGroup => $permissions) {

            if($permissionGroup == Permission::GROUP_TASK){
                foreach ($permissions as $permission) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }
}
