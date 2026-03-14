<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    protected $fillable = ['name', 'guard_name', 'group'];


    const GROUP_ACTIVITY = 'activity';
    const GROUP_TASK = 'task';


    //----------------------------------------------------------
    const PERMISSION_ACTIVITY_SELECT = 'activity_select';
    const PERMISSION_ACTIVITY_LIST = 'activity_list';
    const PERMISSION_ACTIVITY_INFO = 'activity_info';
    const PERMISSION_ACTIVITY_CREATE = 'activity_create';
    const PERMISSION_ACTIVITY_UPDATE = 'activity_update';
    const PERMISSION_ACTIVITY_DESTROY = 'activity_destroy';

       //----------------------------------------------------------
    const PERMISSION_TASK_LIST = 'task_list';
    const PERMISSION_TASK_CREATE = 'task_create';
    const PERMISSION_TASK_UPDATE = 'task_update';
    const PERMISSION_TASK_INFO = 'task_info';
    const PERMISSION_TASK_DESTROY = 'task_destroy';

    /**
     * Список групп
     * @return string[]
     */
    public static function getGroupList()
    {
        return [
            self::GROUP_ACTIVITY,
            self::GROUP_TASK,
        ];
    }

    /**
     * Список разрешений
     * @return array[]
     */
    public static function getPermissionGroupList()
    {
        return [
            self::GROUP_ACTIVITY => [
                self::PERMISSION_ACTIVITY_SELECT,
                self::PERMISSION_ACTIVITY_LIST,
                self::PERMISSION_ACTIVITY_INFO,
                self::PERMISSION_ACTIVITY_CREATE,
                self::PERMISSION_ACTIVITY_UPDATE,
                self::PERMISSION_ACTIVITY_DESTROY,
            ],
            self::GROUP_TASK => [
                self::PERMISSION_TASK_LIST,
                self::PERMISSION_TASK_CREATE,
                self::PERMISSION_TASK_UPDATE,
                self::PERMISSION_TASK_INFO,
                self::PERMISSION_TASK_DESTROY,
            ],
        ];
    }

}
