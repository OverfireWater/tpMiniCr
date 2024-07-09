<?php

use think\facade\Route;

Route::group('setting', function () {
    $prefix = 'v1.setting';
    Route::group(function () use ($prefix) {
        Route::resource('role', "$prefix.SystemRole")->option(['real_name', '角色资源路由']);
    });
})->middleware([
    \app\adminapi\middleware\AdminAuthMiddleware::class,
    \app\adminapi\middleware\AdminAuthCheckMiddleware::class
]);