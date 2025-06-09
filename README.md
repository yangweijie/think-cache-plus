# ThinkCache Plus

<div align="center">

![ThinkCache Plus Logo](https://img.shields.io/badge/ThinkCache-Plus-blue?style=for-the-badge)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.1-777BB4?style=flat-square&logo=php)](https://php.net)
[![ThinkPHP Version](https://img.shields.io/badge/ThinkPHP-%3E%3D6.0-green?style=flat-square)](https://thinkphp.cn)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)](LICENSE)
[![Tests](https://img.shields.io/badge/Tests-24%20Passed-success?style=flat-square)](tests)

**一个强大的 ThinkPHP 缓存扩展包，提供缓存变更事件监听和可视化管理界面**

[功能特性](#功能特性) • [快速开始](#快速开始) • [文档](#文档) • [API](#api-接口) • [测试](#测试)

</div>

---

## ✨ 功能特性

### 🔍 **智能监听**
- **自动事件监听** - 无侵入式监听缓存的写入、删除、清空操作
- **精确调用追踪** - 记录缓存操作的文件路径和行号
- **闭包内容提取** - 智能提取 `remember()` 闭包函数体内容
- **内容完整性** - 支持内容 MD5 校验和重复检测

### 🏷️ **标签管理**
- **标签分组** - 支持按标签分组管理缓存
- **批量操作** - 按标签批量删除缓存
- **标签统计** - 实时统计各标签下的缓存数量

### 📊 **可视化界面**
- **现代化设计** - 基于 TailwindCSS 的响应式界面
- **实时统计** - 缓存数量、大小、命中率等统计信息
- **操作历史** - 完整的缓存操作时间线
- **搜索过滤** - 支持按键名、标签、操作类型搜索

### 🗂️ **数据管理**
- **多版本记录** - 同一个 key 可以记录多条变更历史
- **批量操作** - 支持批量删除缓存和按标签清理
- **数据导出** - 支持导出缓存数据和操作日志
- **自动清理** - 可配置的过期日志自动清理

### ⚡ **性能优化**
- **异步记录** - 支持异步日志记录，不影响主业务性能
- **智能过滤** - 可配置排除监听的缓存键和文件
- **批量写入** - 优化的批量数据库写入
- **内存优化** - 智能内存管理，避免内存泄漏

## 🚀 快速开始

### 📦 安装

使用 Composer 安装：

```bash
composer require yangweijie/think-cache-plus
```

### ⚙️ 一键安装（推荐）

使用内置安装命令，自动完成所有配置：

```bash
# 基础安装
php think cache-plus:install

# 强制覆盖已存在的文件
php think cache-plus:install --force
```

### 🔧 手动配置

如果需要手动配置，请按以下步骤操作：

#### 1. 发布配置文件

```bash
php think vendor:publish --tag=config
```

#### 2. 运行数据库迁移

```bash
php think migrate:run
```

#### 3. 发布静态资源（可选）

```bash
php think vendor:publish --tag=assets
```

#### 4. 注册服务提供者

在 `config/app.php` 中添加服务提供者：

```php
'providers' => [
    // 其他服务...
    \yangweijie\cache\Service::class,
],
```

## 📖 文档

### 🎯 基本配置

在 `config/cache_plus.php` 中配置扩展包：

```php
return [
    // 是否启用缓存监听
    'enable_listener' => true,

    // 日志保留天数（自动清理）
    'log_retention_days' => 30,

    // 是否记录闭包内容
    'log_closure_content' => true,

    // 管理界面配置
    'admin' => [
        'enable' => true,              // 是否启用管理界面
        'password' => '',              // 访问密码，为空则不需要密码
        'page_size' => 20,             // 每页显示数量
        'show_cache_value' => true,    // 是否显示缓存值
        'max_value_display_length' => 1000, // 缓存值最大显示长度
    ],

    // 排除监听的缓存key模式（正则表达式）
    'exclude_key_patterns' => [
        '/^session_/',
        '/^csrf_token_/',
        '/^captcha_/',
    ],

    // 排除监听的文件路径模式（正则表达式）
    'exclude_file_patterns' => [
        '/vendor\//',
        '/runtime\//',
        '/storage\//',
    ],

    // 最大闭包内容长度（字节）
    'max_closure_content_length' => 10240,

    // 性能配置
    'performance' => [
        'enable_monitoring' => true,        // 是否启用性能监控
        'log_throttle_seconds' => 0,       // 日志记录频率限制（秒）
        'log_stack_trace' => true,         // 是否记录调用栈信息
    ],
];
```

### 🖥️ 访问管理界面

启动应用后，访问 `/cache-plus` 即可进入缓存管理界面。

#### 🔐 安全配置

```php
// 启用密码保护
'admin' => [
    'enable' => true,
    'password' => 'your_secure_password',  // 设置访问密码
],
```

访问时需要在URL中添加密码参数：
```
http://your-domain.com/cache-plus?password=your_secure_password
```

#### 🎛️ 界面定制

```php
'admin' => [
    'page_size' => 50,                     // 每页显示50条记录
    'show_cache_value' => false,           // 隐藏缓存值（提升安全性）
    'max_value_display_length' => 500,     // 限制显示长度
],
```

![管理界面截图](https://via.placeholder.com/800x400/4F46E5/FFFFFF?text=ThinkCache+Plus+管理界面)

### 💡 使用示例

#### 基本缓存操作

```php
use yangweijie\cache\facade\CacheWithEvents;

// 普通缓存操作（会自动记录）
CacheWithEvents::set('user:1', $userData, 3600);
CacheWithEvents::get('user:1');
CacheWithEvents::delete('user:1');

// 使用闭包缓存（会记录闭包内容）
$user = CacheWithEvents::remember('user:' . $id, function() use ($id) {
    return User::find($id);
}, 3600);
```

#### 标签缓存操作

```php
// 使用标签
CacheWithEvents::tags(['user', 'profile'])->set('user:1:profile', $profile);

// 按标签删除
CacheWithEvents::tags(['user'])->flush();

// 链式调用
$data = CacheWithEvents::tags(['api', 'external'])
    ->remember('api:weather', function() {
        return $this->fetchWeatherData();
    }, 1800);
```

## 🔌 API 接口

扩展包提供了完整的 REST API，支持 JSON 响应：

### 📋 缓存管理 API

| 方法 | 端点 | 描述 | 参数 |
|------|------|------|------|
| `GET` | `/cache-plus/api/list` | 获取缓存列表 | `page`, `limit`, `key` |
| `GET` | `/cache-plus/api/detail` | 获取缓存详情 | `key` |
| `DELETE` | `/cache-plus/api/delete` | 删除指定缓存 | `key` |
| `DELETE` | `/cache-plus/api/batch-delete` | 批量删除缓存 | `keys[]` |
| `DELETE` | `/cache-plus/api/clear` | 清空所有缓存 | - |

### 🏷️ 标签管理 API

| 方法 | 端点 | 描述 | 参数 |
|------|------|------|------|
| `GET` | `/cache-plus/api/tags` | 获取所有标签 | - |
| `DELETE` | `/cache-plus/api/delete-by-tag` | 按标签删除缓存 | `tag` |

### 📊 统计和日志 API

| 方法 | 端点 | 描述 | 参数 |
|------|------|------|------|
| `GET` | `/cache-plus/api/statistics` | 获取统计信息 | - |
| `GET` | `/cache-plus/api/logs` | 获取操作日志 | `page`, `limit` |
| `POST` | `/cache-plus/api/clean-logs` | 清理过期日志 | `days`, `password` |
| `GET` | `/cache-plus/api/config` | 获取配置信息 | `password` |

### 📝 API 使用示例

```javascript
// 获取缓存列表
fetch('/cache-plus/api/list?page=1&limit=20')
  .then(response => response.json())
  .then(data => console.log(data));

// 删除缓存
fetch('/cache-plus/api/delete?key=user:1', {
  method: 'DELETE'
}).then(response => response.json());

// 批量删除
fetch('/cache-plus/api/batch-delete', {
  method: 'DELETE',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ keys: ['user:1', 'user:2'] })
});

// 清理过期日志
fetch('/cache-plus/api/clean-logs', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    days: 7,  // 保留7天
    password: 'your_password'  // 如果设置了密码
  })
});

// 获取配置信息
fetch('/cache-plus/api/config?password=your_password')
  .then(response => response.json())
  .then(data => console.log(data));
```

### 🛠️ 编程接口

```php
use yangweijie\cache\controller\CacheController;

// 获取控制器实例
$controller = new CacheController();

// 获取缓存列表
$list = $controller->getCacheList($page = 1, $limit = 20);

// 获取缓存详情
$detail = $controller->getCacheDetail('user:1');

// 删除缓存
$result = $controller->deleteCache('user:1');

// 获取统计信息
$stats = $controller->getStatistics();
```

## 🎧 事件监听

### 📡 自动监听事件

扩展包会自动监听以下缓存事件：

| 事件 | 触发时机 | 记录内容 |
|------|----------|----------|
| `cache.write` | 缓存写入/更新 | 键名、值、过期时间、调用位置 |
| `cache.delete` | 缓存删除 | 键名、调用位置 |
| `cache.clear` | 缓存清空 | 清空范围、调用位置 |
| `cache.flush` | 标签清空 | 标签信息、调用位置 |

### 📝 记录信息

每次操作都会详细记录：

- **🔑 缓存信息** - 键名、值、过期时间、标签
- **📍 调用追踪** - 文件路径、行号、调用栈
- **🔒 内容完整性** - 闭包内容、MD5 校验
- **🌐 请求上下文** - URI、User-Agent、时间戳
- **🏷️ 分类标签** - 支持多标签分组管理

### 🎯 闭包内容提取

**重要特性**：智能提取 `remember()` 闭包函数体内容

```php
// 示例代码
CacheWithEvents::remember('user:' . $id, function() use ($id) {
    return User::find($id);
});

// 记录的 closure_content 字段内容：
// "return User::find($id);"  ← 函数体内容，不是缓存值
```

## 🗄️ 数据库结构

### 📊 cache_log 表结构

| 字段 | 类型 | 索引 | 说明 |
|------|------|------|------|
| `id` | `bigint` | PRIMARY | 主键 |
| `cache_key` | `varchar(255)` | INDEX | 缓存键名 |
| `operation` | `varchar(20)` | INDEX | 操作类型 (write/delete/clear) |
| `file_path` | `varchar(500)` | - | 调用文件路径 |
| `line_number` | `int` | - | 调用行号 |
| `closure_content` | `text` | - | 闭包内容或缓存值 |
| `content_md5` | `varchar(32)` | INDEX | 内容 MD5 校验 |
| `tags` | `json` | - | 缓存标签 (JSON 格式) |
| `expire_time` | `int` | - | 过期时间戳 |
| `request_uri` | `varchar(500)` | - | 请求 URI |
| `user_agent` | `varchar(500)` | - | 用户代理 |
| `created_at` | `datetime` | INDEX | 创建时间 |
| `updated_at` | `datetime` | - | 更新时间 |

### 🔍 索引优化

```sql
-- 主要查询索引
INDEX `idx_cache_key` (`cache_key`)
INDEX `idx_operation` (`operation`)
INDEX `idx_created_at` (`created_at`)
INDEX `idx_content_md5` (`content_md5`)

-- 复合索引
INDEX `idx_key_operation` (`cache_key`, `operation`)
INDEX `idx_operation_time` (`operation`, `created_at`)
```

## ⚡ 性能优化

### 🚀 核心优化特性

- **智能过滤机制** - 可配置排除特定缓存键和文件
- **频率限制控制** - 防止高频操作产生大量日志
- **内容长度限制** - 防止过大的闭包内容影响性能
- **正则表达式过滤** - 灵活的键名和文件路径过滤
- **配置化控制** - 可完全关闭监听功能
- **自动日志清理** - 定期清理过期日志数据
- **异常处理** - 监听器错误不影响主业务

### ⚙️ 性能配置

```php
// config/cache_plus.php
return [
    // 是否启用缓存监听
    'enable_listener' => true,

    // 是否记录闭包内容
    'log_closure_content' => true,

    // 最大闭包内容长度（字节）
    'max_closure_content_length' => 10240,

    // 排除监听的缓存key模式（正则表达式）
    'exclude_key_patterns' => [
        '/^session_/',      // 排除 session 相关缓存
        '/^csrf_token_/',   // 排除 CSRF token
        '/^captcha_/',      // 排除验证码缓存
        '/^temp_/',         // 排除临时缓存
    ],

    // 排除监听的文件路径模式（正则表达式）
    'exclude_file_patterns' => [
        '/vendor\//',       // 排除第三方包
        '/runtime\//',      // 排除运行时文件
        '/storage\//',      // 排除存储目录
        '/cache\//',        // 排除缓存目录
    ],

    // 性能优化配置
    'performance' => [
        'enable_monitoring' => true,        // 启用性能监控
        'log_throttle_seconds' => 60,      // 同一缓存键60秒内只记录一次
        'log_stack_trace' => false,        // 关闭调用栈记录（提升性能）
    ],
];
```

### 🎯 过滤规则说明

#### **缓存键过滤**
```php
// 示例：这些缓存键会被排除监听
'session_abc123'        // 匹配 /^session_/
'csrf_token_xyz'        // 匹配 /^csrf_token_/
'captcha_12345'         // 匹配 /^captcha_/
```

#### **文件路径过滤**
```php
// 示例：这些文件的缓存操作会被排除
'/vendor/topthink/framework/src/Cache.php'  // 匹配 /vendor\//
'/app/runtime/cache/temp.php'               // 匹配 /runtime\//
'/storage/logs/cache.log'                   // 匹配 /storage\//
```

### 🗂️ 日志管理

#### **自动清理配置**
```php
// 配置日志保留天数
'log_retention_days' => 7,  // 只保留7天的日志

// 自动清理将在以下情况触发：
// 1. 手动调用 API 接口
// 2. 执行命令行工具
// 3. 可集成到定时任务中
```

#### **命令行清理**
```bash
# 使用配置的保留天数清理
php think cache-plus:clean-logs

# 指定保留天数
php think cache-plus:clean-logs --days=30

# 强制清理（不询问确认）
php think cache-plus:clean-logs --force

# 查看帮助
php think cache-plus:clean-logs --help
```

#### **API 清理**
```bash
# 使用配置的保留天数
curl -X POST "http://your-domain.com/cache-plus/api/clean-logs"

# 指定保留天数
curl -X POST "http://your-domain.com/cache-plus/api/clean-logs" \
  -H "Content-Type: application/json" \
  -d '{"days": 15}'

# 带密码访问
curl -X POST "http://your-domain.com/cache-plus/api/clean-logs" \
  -H "Content-Type: application/json" \
  -d '{"days": 15, "password": "your_password"}'
```

#### **定时任务集成**
```bash
# 添加到 crontab，每天凌晨2点清理过期日志
0 2 * * * cd /path/to/your/project && php think cache-plus:clean-logs --force

# 每周清理一次，保留30天
0 2 * * 0 cd /path/to/your/project && php think cache-plus:clean-logs --days=30 --force
```

## 🧪 测试

本项目使用 [Pest](https://pestphp.com/) 作为测试框架，提供了现代化的测试体验。

### 🚀 运行测试

```bash
# 安装测试依赖
composer install --dev

# 运行所有测试
./vendor/bin/pest

# 运行特定测试套件
./vendor/bin/pest tests/Feature
./vendor/bin/pest tests/Unit

# 运行单个测试文件
./vendor/bin/pest tests/Feature/CacheEventListenerTest.php

# 生成测试覆盖率报告
./vendor/bin/pest --coverage
```

### 📊 测试统计

```
✅ Tests: 24 passed
⏱️ Time: 0.05s
📈 Coverage: 95%+
```

### 📁 测试结构

```
tests/
├── Pest.php                    # Pest 配置和助手函数
├── TestCase.php                # 基础测试类
├── Feature/                    # 功能测试
│   ├── CacheControllerTest.php # 控制器功能测试
│   ├── CacheEventListenerTest.php # 事件监听器测试
│   └── CacheLogTest.php        # 日志模型测试
└── Unit/                       # 单元测试
    └── CacheHookTest.php       # 缓存钩子单元测试
```

### 🔧 测试助手函数

```php
// 创建测试缓存数据
$data = createTestCacheData('key', 'value');

// 创建模拟请求对象
$request = createMockRequest(['param' => 'value']);

// 模拟 ThinkPHP 缓存
mockThinkCache();
```

## 📋 系统要求

| 组件 | 版本要求 | 说明 |
|------|----------|------|
| **PHP** | `>= 7.1` | 支持 PHP 7.1+ 和 PHP 8.x |
| **ThinkPHP** | `>= 6.0` | 兼容 ThinkPHP 6.0+ |
| **MySQL** | `>= 5.7` | 需要 JSON 字段支持 |
| **Composer** | `>= 2.0` | 包管理器 |

### 🔧 开发依赖

| 包 | 版本 | 用途 |
|------|------|------|
| `pestphp/pest` | `^1.0` | 测试框架 |
| `mockery/mockery` | `^1.0` | 模拟对象 |

## 📄 许可证

本项目基于 [MIT License](LICENSE) 开源协议发布。

## 🤝 贡献指南

我们欢迎所有形式的贡献！

### 🐛 报告问题

- 使用 [GitHub Issues](../../issues) 报告 Bug
- 提供详细的错误信息和复现步骤
- 包含环境信息（PHP 版本、ThinkPHP 版本等）

### 💡 功能建议

- 在 Issues 中提出新功能建议
- 详细描述功能需求和使用场景
- 欢迎提供设计思路和实现方案

### 🔧 代码贡献

1. Fork 本仓库
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交更改 (`git commit -m 'Add amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 创建 Pull Request

### 📝 代码规范

- 遵循 PSR-12 编码标准
- 添加适当的注释和文档
- 确保所有测试通过
- 保持向后兼容性

## 📈 更新日志

### 🎉 v1.3.0 (Latest)
- ✨ 实现所有配置项的功能支持
- 🔐 新增管理界面密码保护功能
- 🗂️ 添加自动日志清理功能
- ⚡ 实现性能监控和频率限制
- 🛠️ 新增命令行日志清理工具
- 📊 增强 API 接口（配置获取、日志清理）
- 🎛️ 支持界面定制（分页、缓存值显示控制）

### v1.2.0
- ✨ 新增闭包内容智能提取功能
- 🔧 修复缓存详情弹窗样式问题
- 🚀 添加 `--force` 安装选项
- 📊 优化管理界面用户体验
- 🧪 完善测试覆盖率

### v1.1.0
- 🏷️ 增强标签管理功能
- 📱 响应式界面优化
- ⚡ 性能优化和内存管理
- 🔍 改进搜索和过滤功能

### v1.0.0
- 🎊 初始版本发布
- 🔍 基础缓存监听功能
- 🎨 TailwindCSS 管理界面
- 🔌 完整的 REST API 接口
- 📊 实时统计和日志功能

---

<div align="center">

**⭐ 如果这个项目对你有帮助，请给我们一个 Star！**

[🏠 首页](../../) • [📖 文档](../../wiki) • [🐛 问题反馈](../../issues) • [💬 讨论](../../discussions)

Made with ❤️ by [yangweijie](https://github.com/yangweijie)

</div>
