<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 初始化基本的测试环境
        $this->initTestEnvironment();
    }

    protected function tearDown(): void
    {
        // 清理测试环境
        $this->cleanupTestEnvironment();

        parent::tearDown();
    }

    /**
     * 初始化测试环境
     */
    protected function initTestEnvironment()
    {
        // 设置测试环境变量
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        // 模拟基本的应用路径
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(__DIR__) . DS);
        }

        // 初始化错误处理
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    /**
     * 清理测试环境
     */
    protected function cleanupTestEnvironment()
    {
        // 清理可能的全局状态
        // 这里可以添加清理逻辑
    }

    /**
     * 模拟ThinkPHP应用环境
     */
    protected function mockThinkApp()
    {
        // 创建一个简单的应用容器模拟
        return new class {
            public function get($name) {
                return null;
            }

            public function bind($name, $value) {
                return $this;
            }
        };
    }

    /**
     * 创建测试用的缓存数据
     */
    protected function createTestCacheData()
    {
        return [
            'key' => 'test_cache_key',
            'value' => 'test_cache_value',
            'expire' => 3600,
            'tags' => ['test', 'cache'],
        ];
    }

    /**
     * 模拟缓存操作
     */
    protected function mockCache()
    {
        return new class {
            private $data = [];

            public function get($key, $default = null) {
                return $this->data[$key] ?? $default;
            }

            public function set($key, $value, $ttl = null) {
                $this->data[$key] = $value;
                return true;
            }

            public function delete($key) {
                unset($this->data[$key]);
                return true;
            }

            public function clear() {
                $this->data = [];
                return true;
            }

            public function has($key) {
                return isset($this->data[$key]);
            }
        };
    }
}
