<?php

use think\facade\Route;


Route::group(function (){
    Route::post('login', 'LoginController/login')->name('adminLogin')->option(['real_name' => '管理员登陆']);
    Route::get('login/info', 'LoginController/info')->name('adminLoginInfo')->option(['real_name' => '登陆前的信息']);
    Route::get('logout', 'LoginController/logout')->name('adminLogout')->option(['real_name' => '退出登陆']);
});
