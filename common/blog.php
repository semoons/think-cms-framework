<?php
use think\Route;

// 注册路由
Route::rule('home', 'blog/index/index');
Route::rule('cate/:name', 'blog/index/cate');
Route::rule('article/:key', 'blog/index/show');