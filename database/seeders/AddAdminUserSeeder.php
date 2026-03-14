<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AddAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $name = config('add_user.admin.name');
        $email = config('add_user.admin.email');
        $password = config('add_user.admin.password');

        if(User::where('email', $email)->exists()) {
            echo '    ---- User with email ' . $email . ' already exists'.PHP_EOL;
            return;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => Carbon::now(),
        ]);

        $user->syncRoles(Role::ROLE_ROOT);
    }
}
