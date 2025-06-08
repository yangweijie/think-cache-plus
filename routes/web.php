<?php

use think\facade\Route;
use yangweijie\cache\controller\Cache;
// 缓存管理路由组
Route::group('cache-plus', function () {
    // 管理界面
    Route::get('/', 'index');

    // API接口
    Route::group('api', function () {
        // 缓存列表
        Route::get('list', '\yangweijie\cache\controller\Cache@list');

        // 缓存详情
        Route::get('detail', '\yangweijie\cache\controller\Cache@detail');

        // 删除缓存
        Route::delete('delete', '\yangweijie\cache\controller\Cache@delete');

        // 批量删除缓存
        Route::delete('batch-delete', '\yangweijie\cache\controller\Cache@batchDelete');

        // 按tag删除缓存
        Route::delete('delete-by-tag', '\yangweijie\cache\controller\Cache@deleteByTag');

        // 清空所有缓存
        Route::delete('clear', '\yangweijie\cache\controller\Cache@clear');

        // 获取所有tags
        Route::get('tags', '\yangweijie\cache\controller\Cache@tags');

        // 获取缓存日志
        Route::get('logs', '\yangweijie\cache\controller\Cache@logs');

        // 获取统计信息
        Route::get('statistics', '\yangweijie\cache\controller\Cache@statistics');
    })->class(\yangweijie\cache\controller\Cache::class);;
})->class(\yangweijie\cache\controller\Cache::class);
