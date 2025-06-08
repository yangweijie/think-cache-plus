<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// 使用基础的PHPUnit TestCase，避免复杂的依赖
uses(PHPUnit\Framework\TestCase::class)->in('Feature', 'Unit', '.');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeValidCacheKey', function () {
    return $this->toBeString()->not->toBeEmpty();
});

expect()->extend('toBeValidCacheData', function () {
    return $this->toBeArray()
        ->toHaveKey('key')
        ->toHaveKey('exists')
        ->toHaveKey('logs');
});

// 兼容旧函数名的别名
function createTestCacheData($key = 'test_key', $value = 'test_value') {
    return create_test_cache_data($key, $value);
}

function createMockRequest($params = []) {
    return create_mock_request($params);
}

function mockThinkCache() {
    return mock_think_cache();
}

expect()->extend('toBeJsonResponse', function () {
    return $this->toBeObject();
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the amount of code you type.
|
*/

function create_test_cache_data($key = 'test_key', $value = 'test_value')
{
    return [
        'key' => $key,
        'value' => $value,
        'expire' => 3600,
        'tags' => ['test']
    ];
}

function create_mock_request($params = [])
{
    return new class($params) {
        private $params;

        public function __construct($params)
        {
            $this->params = $params;
        }

        public function param($key = null, $default = null)
        {
            if ($key === null) {
                return $this->params;
            }
            return $this->params[$key] ?? $default;
        }
    };
}

function mock_think_cache()
{
    // 这里可以添加模拟ThinkPHP缓存的逻辑
    return true;
}
