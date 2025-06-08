<?php

// 简化的控制器测试

it('can instantiate controller', function () {
    // 测试控制器类是否存在
    expect(class_exists('yangweijie\cache\controller\Cache'))->toBeTrue();
});

it('has required methods', function () {
    $reflection = new ReflectionClass('yangweijie\cache\controller\Cache');

    expect($reflection->hasMethod('index'))->toBeTrue();
    expect($reflection->hasMethod('list'))->toBeTrue();
    expect($reflection->hasMethod('detail'))->toBeTrue();
    expect($reflection->hasMethod('delete'))->toBeTrue();
    expect($reflection->hasMethod('batchDelete'))->toBeTrue();
    expect($reflection->hasMethod('deleteByTag'))->toBeTrue();
    expect($reflection->hasMethod('clear'))->toBeTrue();
    expect($reflection->hasMethod('tags'))->toBeTrue();
    expect($reflection->hasMethod('logs'))->toBeTrue();
    expect($reflection->hasMethod('statistics'))->toBeTrue();
});

it('can create mock request', function () {
    $request = create_mock_request(['test' => 'value']);
    expect($request->param('test'))->toBe('value');
});

it('can handle request parameters', function () {
    $request = create_mock_request([
        'page' => 1,
        'limit' => 20,
        'key' => 'test_key'
    ]);

    expect($request->param('page'))->toBe(1);
    expect($request->param('limit'))->toBe(20);
    expect($request->param('key'))->toBe('test_key');
    expect($request->param('nonexistent', 'default'))->toBe('default');
});
