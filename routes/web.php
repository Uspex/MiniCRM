<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Activity\ActivityController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Server\ServerController;
use App\Http\Controllers\Admin\Setting\SettingController;


Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return redirect('/');
    });
    Route::get('/', [AuthenticatedSessionController::class, 'create']);
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
});

Route::middleware(['auth', 'setUserLanguage'])->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');


    Route::group(['prefix' => 'admin'], function () {

        Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');

        //Пользователи
        Route::group(['namespace' => 'User'], function() {
            Route::resource('user', '\App\Http\Controllers\Admin\User\UserController')->except(['show'])->names('admin.user');
            Route::get('user/set-setting',  [UserController::class, 'setSetting'])->name('admin.user.set_setting');
        });

        //Роли
        Route::group(['namespace' => 'Role'], function() {
            Route::resource('role', '\App\Http\Controllers\Admin\Role\RoleController')->except(['show'])->names('admin.role');
        });

        //Permission
        Route::group(['namespace' => 'Permission'], function() {
            Route::resource('permission', '\App\Http\Controllers\Admin\Permission\PermissionController')->except(['show'])->names('admin.permission');
        });

        //Активности
        Route::group(['namespace' => 'Activity'], function() {
            Route::resource('activity', '\App\Http\Controllers\Admin\Activity\ActivityController')->except(['show'])->names('admin.activity');
        });

        //Задачи
        Route::group(['namespace' => 'Task'], function() {
            Route::resource('task', '\App\Http\Controllers\Admin\Task\TaskController')->except(['show'])->names('admin.task');
        });

        //Настройки
        Route::group(['namespace' => 'Setting', 'prefix' => 'setting'], function() {
            Route::get('/',           [SettingController::class, 'index'])->name('admin.setting.index');
            Route::get('{type}/edit', [SettingController::class, 'edit'])->name('admin.setting.edit');
            Route::post('{type}',     [SettingController::class, 'update'])->name('admin.setting.update');
        });

        //Сервер
        Route::group(['namespace' => 'Server'], function() {
            Route::get('server', [ServerController::class, 'index'])->name('admin.server.index');
            Route::post('server/step/backup', [ServerController::class, 'stepBackup'])->name('admin.server.step.backup');
            Route::post('server/step/git', [ServerController::class, 'stepGit'])->name('admin.server.step.git');
            Route::post('server/step/migrate', [ServerController::class, 'stepMigrate'])->name('admin.server.step.migrate');
            Route::post('server/step/seed', [ServerController::class, 'stepSeed'])->name('admin.server.step.seed');
            Route::post('server/step/cache', [ServerController::class, 'stepCache'])->name('admin.server.step.cache');
            Route::get('server/backup/{filename}', [ServerController::class, 'downloadBackup'])->name('admin.server.backup.download');
        });
    });
});

