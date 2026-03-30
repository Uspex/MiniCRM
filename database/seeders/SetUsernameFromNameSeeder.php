<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SetUsernameFromNameSeeder extends Seeder
{
    /**
     * Устанавливает username для пользователей, у которых он не задан.
     * Генерируется из поля name через slug (пробелы → подчёркивания).
     * При конфликте добавляется числовой суффикс.
     */
    public function run(): void
    {
        User::whereNull('username')->get()->each(function (User $user) {
            $base = Str::slug($user->name, '_');
            $username = $base;
            $i = 1;

            while (User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $base . '_' . $i;
                $i++;
            }

            $user->update(['username' => $username]);

            $this->command?->info("User id={$user->id} name=\"{$user->name}\" → username=\"{$username}\"");
        });
    }
}
