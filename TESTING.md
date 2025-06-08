# 测试指南

本项目使用 [Pest](https://pestphp.com/) 作为测试框架，专门为ThinkPHP环境进行了优化配置。

## 为什么选择Pest？

- **现代化语法** - 更简洁、更易读的测试代码
- **强大的断言** - 链式断言和自定义断言
- **优秀的错误报告** - 清晰的测试失败信息
- **灵活的配置** - 易于扩展和自定义

## 依赖说明

我们只使用了核心的Pest包：

```json
{
    "require-dev": {
        "pestphp/pest": "^1.0 | ^2.0"
    }
}
```

**注意**：我们没有使用 `pestphp/pest-plugin-laravel`，因为：
1. 这个插件是专门为Laravel框架设计的
2. 我们的项目基于ThinkPHP，不需要Laravel特定的功能
3. 纯净的Pest已经足够满足我们的测试需求

## 测试结构

```
tests/
├── Pest.php                    # Pest配置文件
├── TestCase.php                # 基础测试类
├── CacheManagerTest.php        # 主要服务测试
├── Feature/                    # 功能测试
│   ├── CacheControllerTest.php # 控制器测试
│   ├── CacheEventListenerTest.php # 事件监听器测试
│   └── CacheLogTest.php        # 模型测试
└── Unit/                       # 单元测试
    └── CacheHookTest.php       # 钩子测试
```

## 自定义配置

### 测试基类

我们创建了自定义的 `TestCase` 类来适配ThinkPHP环境：

```php
abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // ThinkPHP环境初始化
    }
    
    protected function mockThinkApp()
    {
        // 模拟ThinkPHP应用实例
    }
}
```

### 自定义断言

针对缓存功能，我们添加了专用断言：

```php
expect()->extend('toBeValidCacheKey', function () {
    return $this->toBeString()->not->toBeEmpty();
});

expect()->extend('toBeValidCacheData', function () {
    return $this->toBeArray()
        ->toHaveKey('key')
        ->toHaveKey('exists')
        ->toHaveKey('logs');
});

expect()->extend('toBeJsonResponse', function () {
    return $this->toBeObject();
});
```

### 测试助手函数

提供了便捷的测试数据创建函数：

```php
function createTestCacheData($key = 'test_key', $value = 'test_value')
{
    return [
        'key' => $key,
        'value' => $value,
        'expire' => 3600,
        'tags' => ['test']
    ];
}

function createMockRequest($params = [])
{
    // 创建模拟的ThinkPHP请求对象
}
```

## 运行测试

### 基本命令

```bash
# 运行所有测试
./vendor/bin/pest

# 运行特定目录的测试
./vendor/bin/pest tests/Feature
./vendor/bin/pest tests/Unit

# 运行单个测试文件
./vendor/bin/pest tests/Feature/CacheManagerTest.php
```

### Composer脚本

```bash
composer test              # 运行所有测试
composer test:unit         # 运行单元测试
composer test:feature      # 运行功能测试
composer test:coverage     # 生成覆盖率报告
```

### 测试选项

```bash
# 详细输出
./vendor/bin/pest --verbose

# 并行运行
./vendor/bin/pest --parallel

# 监视模式（需要安装fswatch）
./vendor/bin/pest --watch

# 生成覆盖率报告
./vendor/bin/pest --coverage --min=80
```

## 测试最佳实践

### 1. 测试命名

使用描述性的测试名称：

```php
it('can get all cache keys', function () {
    // 测试逻辑
});

it('handles empty cache key gracefully', function () {
    // 测试逻辑
});
```

### 2. 测试组织

使用 `describe` 来组织相关测试：

```php
describe('CacheManager', function () {
    it('can get statistics', function () {
        // 测试逻辑
    });
    
    it('can delete cache', function () {
        // 测试逻辑
    });
});
```

### 3. 数据准备

使用 `beforeEach` 进行测试前准备：

```php
beforeEach(function () {
    $this->cacheManager = new CacheManager();
});
```

### 4. 异常测试

测试异常情况：

```php
it('handles invalid data gracefully', function () {
    expect(function () {
        $this->service->processInvalidData();
    })->not->toThrow(Exception::class);
});
```

## 持续集成

在CI/CD环境中运行测试：

```yaml
# GitHub Actions 示例
- name: Run Tests
  run: |
    composer install --no-dev --optimize-autoloader
    composer install --dev
    ./vendor/bin/pest --coverage --min=80
```

## 故障排除

### 常见问题

1. **找不到Pest命令**
   ```bash
   composer install --dev
   ```

2. **测试失败但本地正常**
   - 检查PHP版本兼容性
   - 确认所有依赖已安装
   - 检查环境变量配置

3. **覆盖率报告生成失败**
   - 确保安装了Xdebug或PCOV扩展
   - 检查PHP配置

### 调试技巧

```php
// 在测试中使用dump进行调试
it('debugs cache data', function () {
    $data = $this->cacheManager->getCacheInfo('test');
    dump($data); // 输出调试信息
    expect($data)->toBeArray();
});
```

## 扩展测试

如果需要添加新的测试功能：

1. 在 `tests/Pest.php` 中添加新的助手函数
2. 创建新的自定义断言
3. 在相应目录下创建测试文件
4. 遵循现有的命名和组织约定
