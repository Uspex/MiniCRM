<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleRootSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', Role::ROLE_ROOT)->first();
        if(!$role) {
            $role = Role::create([
                'name' => Role::ROLE_ROOT,
                'guard_name' => 'web',
            ]);
        }

        $permissionGroupList = Permission::getPermissionGroupList();

        foreach ($permissionGroupList as $permissionGroup => $permissions) {
            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission);
            }
        }
    }
}
