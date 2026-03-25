<?php

namespace App\Services;


use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
   public static function generateUsername(string $name): string
   {
       $base = Str::slug($name, '_');
       $username = $base;
       $i = 1;
       while (User::where('username', $username)->exists()) {
           $username = $base . '_' . $i++;
       }
       return $username;
   }

   public static function create($data)
   {
       if (empty($data['username'])) {
           $data['username'] = self::generateUsername($data['name']);
       }
       $data['password'] = Hash::make($data['password']);
       $user = User::create($data);
       $user->syncRoles($data['role']);

       return $user;
   }
}
