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

        $name     = config('add_user.admin.name');
        $username = config('add_user.admin.username');
        $email    = config('add_user.admin.email');
        $password = config('add_user.admin.password');

        if (User::where('username', $username)->orWhere('email', $email)->exists()) {
            $this->command?->info('User with username ' . $username . ' already exists');
            return;
        }

        $user = User::create([
            'name'               => $name,
            'username'           => $username,
            'email'              => $email ?: null,
            'password'           => Hash::make($password),
            'email_verified_at'  => Carbon::now(),
        ]);

        $user->syncRoles(Role::ROLE_ROOT);
    }
}
