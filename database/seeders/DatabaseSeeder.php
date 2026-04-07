<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        $this->call(RoleRootSeeder::class);
        $this->call(AddAdminUserSeeder::class);
        $this->call(RoleWorkerSeeder::class);
        $this->call(SetUsernameFromNameSeeder::class);
        $this->call(SettingSeeder::class);

    }

}
