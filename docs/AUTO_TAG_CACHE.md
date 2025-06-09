# 自动表名标签缓存功能

## 功能概述

ThinkCache Plus 扩展包现在支持自动表名标签缓存功能，当使用 Db 和 Model 查询的 `cache()` 方法时，会自动：

1. **自动添加表名作为缓存标签** - 单表查询自动使用表名作为标签
2. **多表查询支持** - JOIN 查询和关联查询自动提取所有涉及的表名作为标签数组
3. **智能缓存清理** - 数据更新时自动清理对应表名标签的所有相关缓存
4. **无侵入式集成** - 通过 Provider 系统替换 DbManager，无需修改现有代码

## 安装配置

### 1. 确保服务已注册

在 `config/app.php` 中确认服务提供者已注册：

```php
'providers' => [
    // 其他服务...
    \yangweijie\cache\Service::class,
],
```

### 2. 配置缓存驱动

确保缓存驱动支持标签功能（如 Redis）：

```php
// config/cache.php
return [
    'default' => 'redis',
    'stores' => [
        'redis' => [
            'type' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '',
            'select' => 0,
            'timeout' => 0,
            'expire' => 0,
            'persistent' => false,
            'prefix' => '',
            'tag_prefix' => 'tag:',
        ],
    ],
];
```

## 使用方法

### 1. 使用扩展模型类

创建模型时继承 `ExtendedModel`：

```php
<?php

namespace app\model;

use yangweijie\cache\model\ExtendedModel;

class User extends ExtendedModel
{
    protected $table = 'user';
}
```

### 2. 单表查询缓存

```php
// 自动使用 'user' 作为缓存标签
$user = User::where('id', 1)->cache(true)->find();

// 或者指定缓存键
$user = User::where('status', 1)->cache('active_users', 3600)->select();
```

### 3. JOIN 查询缓存

```php
// 自动使用 ['user', 'profile'] 作为缓存标签
$result = User::alias('u')
    ->join('user_profile p', 'u.id = p.user_id')
    ->where('u.status', 1)
    ->cache('user_with_profile', 3600)
    ->select();
```

### 4. 模型关联查询缓存

#### withJoin 关联（会自动添加标签）
```php
// 自动添加 ['user', 'profile'] 标签
$users = User::withJoin(['profile'])
    ->cache('users_with_profile', 3600)
    ->select();
```

#### 普通 with 预载入（只添加主表标签）
```php
// 只添加 'user' 标签，不包含关联表标签
$users = User::with(['profile'])
    ->cache('users_data', 3600)
    ->select();

// 关联查询单独缓存（会添加关联表标签）
$users = User::with(['profile'])
    ->withCache(['profile' => ['profile_cache', 3600]])
    ->select();
```

### 5. 使用 Db 查询

```php
use think\facade\Db;

// 单表查询 - 自动使用 'user' 标签
$users = Db::name('user')
    ->where('status', 1)
    ->cache(true, 3600)
    ->select();

// 多表查询 - 自动使用 ['user', 'order'] 标签
$result = Db::name('user')
    ->alias('u')
    ->join('order o', 'u.id = o.user_id')
    ->cache('user_orders', 3600)
    ->select();
```

## 自动缓存清理

### 1. 模型数据更新

当使用扩展模型进行数据更新时，会自动清理相关缓存：

```php
$user = User::find(1);
$user->name = 'New Name';
$user->save(); // 自动清理 'user' 标签的所有缓存

// 删除数据
$user->delete(); // 自动清理 'user' 标签的所有缓存
```

### 2. 手动清理标签缓存

```php
use think\facade\Cache;

// 清理指定表的所有缓存
Cache::tag('user')->clear();

// 清理多个表的缓存
Cache::tag(['user', 'profile'])->clear();
```

## 缓存事件记录

所有缓存操作都会被记录到 `cache_log` 表中，包括：

- 缓存键名
- 操作类型（write/delete/clear）
- 涉及的表名标签
- 调用文件和行号
- 创建时间等

可以通过管理界面查看：`http://your-domain/cache-plus/`

## 配置选项

在 `config/cache_plus.php` 中可以配置相关选项：

```php
return [
    // 是否启用缓存监听
    'enable_listener' => true,

    // 是否记录闭包内容
    'log_closure_content' => true,

    // 排除监听的缓存key模式
    'exclude_key_patterns' => [
        '/^session_/',
        '/^csrf_token_/',
    ],

    // 性能配置
    'performance' => [
        'enable_monitoring' => true,
        'log_throttle_seconds' => 60,
    ],
];
```

## 注意事项

1. **缓存驱动要求** - 必须使用支持标签功能的缓存驱动（如 Redis）
2. **性能考虑** - 大量缓存操作时建议适当配置 `log_throttle_seconds`
3. **表名提取** - 复杂的子查询可能无法完全提取所有表名
4. **向后兼容** - 现有代码无需修改，自动启用新功能

## 故障排除

### 1. 缓存标签不生效

检查缓存驱动是否支持标签：

```php
$cache = \think\facade\Cache::store();
if (!method_exists($cache, 'tag')) {
    echo '当前缓存驱动不支持标签功能';
}
```

### 2. 自动清理不工作

确保使用了扩展模型类：

```php
// 正确
class User extends \yangweijie\cache\model\ExtendedModel {}

// 错误
class User extends \think\Model {}
```

### 3. 查看日志记录

检查 `cache_log` 表中的记录：

```sql
SELECT * FROM cache_log WHERE tags != '' ORDER BY created_at DESC LIMIT 10;
```
