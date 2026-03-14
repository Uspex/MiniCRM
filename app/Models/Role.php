<?php

namespace App\Models;

use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{

    protected $fillable = ['name', 'guard_name'];

    const ROLE_ROOT = 'root';


    public static function getRoleList()
    {
        $roleList = self::when(!auth()->user()->hasRole(self::ROLE_ROOT), function ($query) {
                        return $query->where('name', '!=', self::ROLE_ROOT);
                    })->get();

        return $roleList;
    }

}
