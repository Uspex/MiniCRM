<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Admin\DashboardController;


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

        Route::get('/', [DashboardController::class, 'admin.dashboard']);

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
    });
});

