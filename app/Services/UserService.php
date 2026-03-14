<?php

namespace App\Services;


use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
   public static function create($data)
   {
       $data['password'] = Hash::make($data['password']);
       $user = User::create($data);
       $user->syncRoles($data['role']);

       return $user;
   }
}
