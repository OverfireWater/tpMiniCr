<?php

use think\facade\Route;

Route::group('setting', function () {
    $prefix = 'v1.setting';
    Route::group(function () use ($prefix) {
        Route::resource('role', "$prefix.SystemRole")->option(['real_name', '角色资源路由']);
        Route::put('updateStatus/:id', "$prefix.SystemRole/updateStatus")->option(['real_name', '修改角色状态']);
    });
    Route::group(function () use ($prefix) {
        Route::resource('admin', "$prefix.SystemAdmin")->option(['real_name', '管理员资源路由']);
        Route::put('updateAdminStatus/:id', "$prefix.SystemAdmin/updateAdminStatus")->option(['real_name', '修改管理员状态']);
    });
})->middleware([
    \app\adminapi\middleware\AdminAuthMiddleware::class,
    \app\adminapi\middleware\AdminAuthCheckMiddleware::class
]);
