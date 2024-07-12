<?php

use think\facade\Route;


Route::group('system', function () {
    $systemPre = 'v1.system';// 控制器 设置 路由的前缀
    // 菜单
    Route::group(function () use ($systemPre) {
        // 权限菜单资源路由
        Route::resource('menus', "$systemPre.SystemMenus")->option([
            'real_name' => [
                'index' => '获取权限菜单列表',
                'create' => '获取权限菜单表单',
                'save' => '保存权限菜单',
                'edit' => '获取修改权限菜单表单',
                'read' => '查看权限菜单信息',
                'update' => '修改权限菜单',
                'delete' => '删除权限菜单'
            ],
        ]);
        Route::get('uniqueMenus', "$systemPre.SystemMenus/uniqueMenus")->option(['real_name' => '获取当前管理员菜单和权限']);
        Route::put('changeShowMenus/:id', "$systemPre.SystemMenus/changeShowMenus")->option(['real_name' => '修改权限菜单显示状态']);

        Route::post('saveSelectRouteRule', "$systemPre.SystemMenus/saveSelectRouteRule")->option(['real_name' => '保存权限菜单路由规则']);
        Route::get('routeCate', "$systemPre.SystemMenus/routeCate")->option(['real_name' => '获取后台接口分类']);
        Route::get('routeList/:id', "$systemPre.SystemMenus/routeList")->option(['real_name' => '获取后台接口列表']);
    })->option(['real_name' => '后台权限菜单管理']);

    // 后台路由接口管理
    Route::group(function () use ($systemPre) {
        // 后台路由接口管理
        Route::resource('routeApi', "$systemPre.SystemRoute")->only(['save', 'read', 'update', 'delete']);
        // 后台路由接口管理
        Route::get('route/tree/:app_name', "$systemPre.SystemRoute/tree")->option(['real_name' => '获取路由树']);
        // 删除分类下所有api
        Route::delete('route/deleteAllApi/:id/:app_name', "$systemPre.SystemRoute/deleteAllApi");
    })->option(['real_name' => '后台接口管理']);

    // 接口分类路由
    Route::group(function () use ($systemPre) {
        Route::resource('routeCate', "$systemPre.SystemRouteCate");
        Route::delete('routeCate/delete/:id/:app_name', "$systemPre.SystemRouteCate/deleteCate")->option(['real_name' => '删除后台接口分类']);
    })->option(['real_name' => '后台接口分类管理']);
})->middleware([
    \app\adminapi\middleware\AdminAuthMiddleware::class,
    \app\adminapi\middleware\AdminAuthCheckMiddleware::class
]);