# ThinkCache Plus

一个强大的 ThinkPHP 缓存扩展包，提供缓存变更事件监听和可视化管理界面。

## 功能特性

- 🔍 **缓存事件监听** - 自动记录缓存的写入、删除、清空操作
- 📍 **调用追踪** - 记录缓存操作的文件路径和行号
- 🔐 **内容记录** - 支持记录闭包函数内容和内容 MD5
- 🏷️ **标签管理** - 支持按标签分组管理缓存
- 📊 **可视化界面** - 基于 TailwindCSS 的现代化管理界面
- 🗂️ **多条记录** - 同一个 key 可以记录多条变更历史
- 🧹 **批量操作** - 支持批量删除缓存和按标签清理

## 安装

使用 Composer 安装：

```bash
composer require yangweijie/think-cache-plus
```

## 配置

### 1. 发布配置文件

```bash
php think vendor:publish --tag=config
```

### 2. 运行数据库迁移

```bash
php think migrate:run
```

### 3. 发布静态资源（可选）

```bash
php think vendor:publish --tag=assets
```

## 使用方法

### 基本配置

在 `config/cache_plus.php` 中配置扩展包：

```php
return [
    // 是否启用缓存监听
    'enable_listener' => true,

    // 日志保留天数
    'log_retention_days' => 30,

    // 是否记录闭包内容
    'log_closure_content' => true,

    // 管理界面配置
    'admin' => [
        'enable' => true,
        'password' => '', // 访问密码，为空则不需要密码
        'page_size' => 20,
    ],
];
```

### 访问管理界面

启动应用后，访问 `/cache-plus` 即可进入缓存管理界面。

### API 接口

扩展包提供了完整的 REST API：

- `GET /cache-plus/api/list` - 获取缓存列表
- `GET /cache-plus/api/detail?key={key}` - 获取缓存详情
- `DELETE /cache-plus/api/delete?key={key}` - 删除指定缓存
- `DELETE /cache-plus/api/batch-delete` - 批量删除缓存
- `DELETE /cache-plus/api/delete-by-tag?tag={tag}` - 按标签删除缓存
- `DELETE /cache-plus/api/clear` - 清空所有缓存
- `GET /cache-plus/api/tags` - 获取所有标签
- `GET /cache-plus/api/logs` - 获取操作日志
- `GET /cache-plus/api/statistics` - 获取统计信息

### 编程接口

```php
use Yangweijie\ThinkCachePlus\Service\CacheManager;

// 获取缓存管理器实例
$manager = app('cache.manager');

// 获取所有缓存键
$keys = $manager->getAllCacheKeys();

// 获取缓存信息
$info = $manager->getCacheInfo('user:1');

// 删除缓存
$manager->deleteCache('user:1');

// 按标签删除
$manager->deleteCacheByTag('user');

// 获取统计信息
$stats = $manager->getStatistics();
```

## 事件监听

扩展包会自动监听以下缓存事件：

- `cache.write` - 缓存写入
- `cache.delete` - 缓存删除
- `cache.clear` - 缓存清空

每次操作都会记录：
- 缓存键名
- 操作类型
- 调用文件和行号
- 缓存内容和 MD5
- 标签信息
- 过期时间
- 请求信息

## 数据库结构

缓存日志表 `cache_log` 结构：

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 主键 |
| cache_key | varchar(255) | 缓存键名 |
| operation | varchar(20) | 操作类型 |
| file_path | varchar(500) | 调用文件路径 |
| line_number | int | 调用行号 |
| closure_content | text | 闭包内容或缓存值 |
| content_md5 | varchar(32) | 内容 MD5 |
| tags | json | 缓存标签 |
| expire_time | int | 过期时间 |
| request_uri | varchar(500) | 请求 URI |
| user_agent | varchar(500) | 用户代理 |
| created_at | datetime | 创建时间 |
| updated_at | datetime | 更新时间 |

## 性能优化

- 支持异步日志记录
- 可配置排除监听的缓存键和文件
- 自动清理过期日志
- 批量写入优化

## 测试

本项目使用 [Pest](https://pestphp.com/) 作为测试框架，提供了现代化的测试体验。我们使用纯净的Pest框架，专门为ThinkPHP环境进行了优化配置。

### 运行测试

```bash
# 安装测试依赖
composer install --dev

# 运行所有测试
./vendor/bin/pest

# 运行特定测试套件
./vendor/bin/pest tests/Feature
./vendor/bin/pest tests/Unit

# 运行单个测试文件
./vendor/bin/pest tests/Feature/CacheManagerTest.php

# 生成测试覆盖率报告
./vendor/bin/pest --coverage
```

### 测试结构

```
tests/
├── Pest.php                    # Pest 配置和助手函数
├── TestCase.php                # 基础测试类
├── Feature/                    # 功能测试
│   ├── CacheControllerTest.php
│   ├── CacheEventListenerTest.php
│   └── CacheLogTest.php
└── Unit/                       # 单元测试
    └── CacheHookTest.php
```

### 自定义断言

项目提供了一些自定义的 Pest 断言：

```php
expect($cacheKey)->toBeValidCacheKey();
expect($cacheData)->toBeValidCacheData();
expect($response)->toBeJsonResponse();
```

### 测试助手函数

```php
// 创建测试缓存数据
$data = createTestCacheData('key', 'value');

// 创建模拟请求对象
$request = createMockRequest(['param' => 'value']);

// 模拟 ThinkPHP 缓存
mockThinkCache();
```

## 系统要求

- PHP >= 7.1
- ThinkPHP >= 6.0
- MySQL >= 5.7 (支持 JSON 字段)
- Pest >= 1.0 (开发依赖)

## 许可证

MIT License

## 贡献

欢迎提交 Issue 和 Pull Request！

## 更新日志

### v1.0.0
- 初始版本发布
- 基础缓存监听功能
- TailwindCSS 管理界面
- 完整的 API 接口
