<?php

use think\facade\Route;

Route::get('/:id/:name', 'Index/index');

Route::group(function (){
    Route::post('login', 'v1.LoginController/login')->name('login')->option(['real_name' => '用户登陆']);
});