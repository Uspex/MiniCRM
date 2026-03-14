<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroupList = Permission::getPermissionGroupList();

        foreach ($permissionGroupList as $groupName => $group) {
            foreach ($group as $permission) {
                if(!Permission::where('name', $permission)->exists()) {
                    Permission::create([
                        'name' => $permission,
                        'guard_name' => 'web',
                        'group' => $groupName,
                    ]);
                }
            }
        }
    }
}
