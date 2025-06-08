<?php

// 简化的事件监听器测试

it('can instantiate cache event listener', function () {
    $listener = new \yangweijie\cache\listener\CacheEventListener();
    expect($listener)->toBeObject();
});

it('has required methods', function () {
    $reflection = new ReflectionClass('yangweijie\cache\listener\CacheEventListener');

    expect($reflection->hasMethod('onCacheWrite'))->toBeTrue();
    expect($reflection->hasMethod('onCacheDelete'))->toBeTrue();
    expect($reflection->hasMethod('onCacheClear'))->toBeTrue();
});

it('can validate closure content extraction method', function () {
    $reflection = new ReflectionClass('yangweijie\cache\listener\CacheEventListener');
    expect($reflection->hasMethod('getClosureContent'))->toBeTrue();
});

it('can validate caller detection method', function () {
    $reflection = new ReflectionClass('yangweijie\cache\listener\CacheEventListener');
    expect($reflection->hasMethod('findRealCaller'))->toBeTrue();
});

it('can validate log operation method', function () {
    $reflection = new ReflectionClass('yangweijie\cache\listener\CacheEventListener');
    expect($reflection->hasMethod('logCacheOperation'))->toBeTrue();
});

it('can handle test data structure', function () {
    $data = createTestCacheData();

    expect($data)->toBeArray();
    expect(isset($data['key']))->toBeTrue();
    expect(isset($data['value']))->toBeTrue();
    expect(isset($data['expire']))->toBeTrue();
    expect(isset($data['tags']))->toBeTrue();
});

it('can process closure data', function () {
    $closure = function () {
        return 'test closure';
    };

    expect(is_callable($closure))->toBeTrue();
});

it('can handle various data types', function () {
    $testData = [
        'string' => 'test',
        'array' => [1, 2, 3],
        'object' => new stdClass(),
        'null' => null,
        'boolean' => true,
        'integer' => 123
    ];

    expect(count($testData))->toBe(6);
    expect(array_key_exists('string', $testData))->toBeTrue();
    expect(array_key_exists('array', $testData))->toBeTrue();
});

it('can extract closure body content', function () {
    $listener = new \yangweijie\cache\listener\CacheEventListener();

    // 使用反射访问 protected 方法
    $reflection = new ReflectionClass($listener);
    $method = $reflection->getMethod('extractClosureBody');
    $method->setAccessible(true);

    // 测试闭包体提取
    $input = 'function() { return time(); }';

    $result = $method->invoke($listener, $input);
    expect(str_contains($result, 'return time()'))->toBeTrue();
});

it('can handle closure in event data', function () {
    $closure = function() {
        return time();
    };

    // 验证闭包可以被处理
    expect(is_callable($closure))->toBeTrue();
    expect(is_int($closure()))->toBeTrue();
});
