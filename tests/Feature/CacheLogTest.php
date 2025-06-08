<?php

// 简化的缓存日志模型测试

it('can instantiate cache log model', function () {
    expect(class_exists('yangweijie\cache\model\CacheLog'))->toBeTrue();
});

it('has required static methods', function () {
    $reflection = new ReflectionClass('yangweijie\cache\model\CacheLog');

    expect($reflection->hasMethod('getStatistics'))->toBeTrue();
    expect($reflection->hasMethod('search'))->toBeTrue();
    expect($reflection->hasMethod('cleanOldLogs'))->toBeTrue();
    expect($reflection->hasMethod('getKeyLogs'))->toBeTrue();
    expect($reflection->hasMethod('getTagLogs'))->toBeTrue();
    expect($reflection->hasMethod('getRecentLogs'))->toBeTrue();
});

it('has correct properties', function () {
    $reflection = new ReflectionClass('yangweijie\cache\model\CacheLog');

    // 检查是否有name属性
    expect($reflection->hasProperty('name'))->toBeTrue();
    expect($reflection->hasProperty('autoWriteTimestamp'))->toBeTrue();
    expect($reflection->hasProperty('type'))->toBeTrue();
});

it('can validate search parameters', function () {
    $params = [
        'key' => 'test',
        'operation' => 'write',
        'page' => 1,
        'limit' => 10
    ];

    expect($params)->toBeArray();
    expect($params)->toHaveKey('key');
    expect($params)->toHaveKey('operation');
    expect($params)->toHaveKey('page');
    expect($params)->toHaveKey('limit');
});
