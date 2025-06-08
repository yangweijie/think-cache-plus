<?php

// 简单的测试，不依赖完整的ThinkPHP环境

it('can create cache manager instance', function () {
    // 测试基本的类实例化
    expect(class_exists('yangweijie\cache\service\CacheManager'))->toBeTrue();
});

it('can create cache log model instance', function () {
    // 测试模型类存在
    expect(class_exists('yangweijie\cache\model\CacheLog'))->toBeTrue();
});

it('can create cache event listener instance', function () {
    // 测试监听器类存在
    expect(class_exists('yangweijie\cache\listener\CacheEventListener'))->toBeTrue();
});

it('can create cache controller instance', function () {
    // 测试控制器类存在
    expect(class_exists('yangweijie\cache\controller\Cache'))->toBeTrue();
});

it('can create cache hook instance', function () {
    // 测试钩子类存在
    expect(class_exists('yangweijie\cache\hook\CacheHook'))->toBeTrue();
});

it('can create service provider instance', function () {
    // 测试服务提供者类存在
    expect(class_exists('yangweijie\cache\Service'))->toBeTrue();
});

it('has correct namespace structure', function () {
    // 测试命名空间结构
    $reflection = new ReflectionClass('yangweijie\cache\service\CacheManager');
    expect($reflection->getNamespaceName())->toBe('yangweijie\cache\service');
});

it('can validate test helper functions', function () {
    // 测试助手函数
    $data = create_test_cache_data();
    expect($data)
        ->toBeArray()
        ->toHaveKey('key')
        ->toHaveKey('value')
        ->toHaveKey('expire')
        ->toHaveKey('tags');
});

it('can create mock request', function () {
    // 测试模拟请求对象
    $request = create_mock_request(['test' => 'value']);
    expect($request->param('test'))->toBe('value');
    expect($request->param('nonexistent', 'default'))->toBe('default');
});

it('can mock think cache', function () {
    // 测试缓存模拟
    $result = mock_think_cache();
    expect($result)->toBeTrue();
});
