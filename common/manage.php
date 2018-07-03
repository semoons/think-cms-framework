<?php
use think\Route;
use think\Hook;

// 注册路由
Route::rule('module/:_module_/:_controller_/:_action_', 'manage/loader/run');

// 添加行为
Hook::add('app_begin', '\\app\\manage\\behavior\\LoadConfigBehavior');