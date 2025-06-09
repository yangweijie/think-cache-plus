<?php

namespace yangweijie\cache;

use think\Service as BaseService;
use yangweijie\cache\listener\ExtendedCacheEventListener;

/**
 * ThinkCachePlus 服务提供者
 */
class Service extends BaseService
{
    public function register()
    {
        // 注册缓存管理服务
        $this->app->bind('cache.manager', \yangweijie\cache\service\CacheManager::class);

        // 注册数据库服务提供者
        $this->app->register(\yangweijie\cache\provider\DatabaseServiceProvider::class);
    }

    public function boot()
    {
        // 注册事件监听器
        $this->registerEventListeners();

        // 注册路由
        $this->loadRoutesFrom(__DIR__. '/../routes/web.php');

        // 注册命令
        $this->commands([
            'cache-plus:install' => \yangweijie\cache\command\InstallCommand::class,
            'cache-plus:auto-tag-setup' => \yangweijie\cache\command\AutoTagSetupCommand::class,
        ]);
    }

    /**
     * 注册事件监听器
     */
    protected function registerEventListeners()
    {
        $event = $this->app->event;
        $listener = new ExtendedCacheEventListener();

        // 监听缓存写入事件
        $event->listen('cache.write', [$listener, 'onCacheWrite']);

        // 监听缓存删除事件
        $event->listen('cache.delete', [$listener, 'onCacheDelete']);

        // 监听缓存清空事件
        $event->listen('cache.clear', [$listener, 'onCacheClear']);
    }

    /**
     * 加载路由文件
     */
    protected function loadRoutes()
    {
        // 在ThinkPHP中，我们需要手动包含路由文件
        $routeFile = __DIR__ . '/../routes/web.php';
        if (file_exists($routeFile)) {
            include $routeFile;
        }
    }
}
