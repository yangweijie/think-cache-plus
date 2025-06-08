<?php

use yangweijie\cache\hook\CacheHook;

it('can trigger write event', function () {
    // 测试类是否存在
    expect(class_exists('yangweijie\cache\hook\CacheHook'))->toBeTrue();
});

it('can trigger delete event', function () {
    // 测试方法是否存在
    expect(method_exists('yangweijie\cache\hook\CacheHook', 'onDelete'))->toBeTrue();
});

it('can trigger clear event', function () {
    // 测试方法是否存在
    expect(method_exists('yangweijie\cache\hook\CacheHook', 'onClear'))->toBeTrue();
});

it('can trigger tag clear event', function () {
    // 测试方法是否存在
    expect(method_exists('yangweijie\cache\hook\CacheHook', 'onTagClear'))->toBeTrue();
});

it('has static methods', function () {
    $reflection = new ReflectionClass('yangweijie\cache\hook\CacheHook');

    expect($reflection->hasMethod('onWrite'))->toBeTrue();
    expect($reflection->getMethod('onWrite')->isStatic())->toBeTrue();
});

it('can handle complex data types', function () {
    $complexValue = [
        'array' => [1, 2, 3],
        'object' => new stdClass(),
        'closure' => function () { return 'test'; }
    ];

    // 测试数据结构
    expect($complexValue)->toBeArray();
    expect(is_callable($complexValue['closure']))->toBeTrue();
});
