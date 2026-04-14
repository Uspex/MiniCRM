<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    protected $fillable = ['name', 'guard_name', 'group'];


    const GROUP_ACTIVITY  = 'activity';
    const GROUP_TASK      = 'task';
    const GROUP_ANALYTICS = 'analytics';
    const GROUP_SETTING   = 'setting';
    const GROUP_REPORT    = 'report';


    //----------------------------------------------------------
    const PERMISSION_ACTIVITY_SELECT = 'activity_select';
    const PERMISSION_ACTIVITY_LIST = 'activity_list';
    const PERMISSION_ACTIVITY_INFO = 'activity_info';
    const PERMISSION_ACTIVITY_CREATE = 'activity_create';
    const PERMISSION_ACTIVITY_UPDATE = 'activity_update';
    const PERMISSION_ACTIVITY_DESTROY = 'activity_destroy';

    //----------------------------------------------------------
    const PERMISSION_ANALYTICS_DASHBOARD  = 'analytics_dashboard';
    const PERMISSION_ANALYTICS_ALL_USERS  = 'analytics_all_users';

    //----------------------------------------------------------
    const PERMISSION_SETTING_EDIT = 'setting_edit';

    //----------------------------------------------------------
    const PERMISSION_REPORT_LIST      = 'report_list';
    const PERMISSION_REPORT_GENERATE  = 'report_generate';
    const PERMISSION_REPORT_ALL_USERS = 'report_all_users';

       //----------------------------------------------------------
    const PERMISSION_TASK_LIST = 'task_list';
    const PERMISSION_TASK_CREATE = 'task_create';
    const PERMISSION_TASK_UPDATE = 'task_update';
    const PERMISSION_TASK_INFO = 'task_info';
    const PERMISSION_TASK_DESTROY   = 'task_destroy';
    const PERMISSION_TASK_ALL_USERS = 'task_all_users';

    /**
     * Список групп
     * @return string[]
     */
    public static function getGroupList()
    {
        return [
            self::GROUP_ACTIVITY,
            self::GROUP_TASK,
            self::GROUP_ANALYTICS,
            self::GROUP_SETTING,
            self::GROUP_REPORT,
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
                self::PERMISSION_TASK_ALL_USERS,
            ],
            self::GROUP_ANALYTICS => [
                self::PERMISSION_ANALYTICS_DASHBOARD,
                self::PERMISSION_ANALYTICS_ALL_USERS,
            ],
            self::GROUP_SETTING => [
                self::PERMISSION_SETTING_EDIT,
            ],
            self::GROUP_REPORT => [
                self::PERMISSION_REPORT_LIST,
                self::PERMISSION_REPORT_GENERATE,
                self::PERMISSION_REPORT_ALL_USERS,
            ],
        ];
    }

}
