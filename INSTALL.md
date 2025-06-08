# ThinkCache Plus 安装指南

## 📦 安装步骤

### 1. 通过 Composer 安装

```bash
composer require yangweijie/think-cache-plus
```

### 2. 手动复制配置文件

由于 ThinkPHP 不支持自动发布资源文件，需要手动复制配置文件：

```bash
# 复制配置文件
cp vendor/yangweijie/think-cache-plus/config/cache_plus.php config/

# 复制数据库迁移文件（可选）
cp -r vendor/yangweijie/think-cache-plus/database/migrations/ database/migrations/

# 复制视图文件（可选，如果需要自定义UI）
cp -r vendor/yangweijie/think-cache-plus/resources/views/ view/cache_plus/

# 复制静态资源（可选，如果需要自定义UI）
cp -r vendor/yangweijie/think-cache-plus/resources/assets/ public/static/cache_plus/
```

### 3. 配置数据库

如果需要使用缓存日志功能，请执行数据库迁移：

```bash
# 使用 ThinkPHP 命令行工具
php think migrate:run

# 或者手动执行 SQL
# 查看 database/migrations/ 目录下的迁移文件
```

### 4. 配置缓存

编辑 `config/cache_plus.php` 文件，根据需要调整配置：

```php
return [
    // 是否启用缓存事件监听
    'enable_listener' => true,

    // 日志保留天数
    'log_retention_days' => 30,

    // 其他配置...
];
```

### 5. 注册服务

在 `config/app.php` 中注册服务提供者（ThinkPHP 8.0+）：

```php
'services' => [
    // 其他服务...
    \yangweijie\cache\Service::class,
],
```

或者在应用启动时手动注册（ThinkPHP 6.0）：

```php
// 在 app/provider.php 中添加
return [
    'yangweijie\cache\Service',
];
```

## 🚀 使用安装命令（推荐）

### 基本安装
```bash
php think cache-plus:install
```

### 强制覆盖安装
如果需要覆盖已存在的文件，可以使用 `--force` 或 `-f` 选项：
```bash
php think cache-plus:install --force
# 或者
php think cache-plus:install -f
```

**注意**：
- 不使用 `--force` 时，已存在的文件会被跳过
- 使用 `--force` 时，所有文件都会被强制覆盖
- 建议在首次安装时不使用 `--force`，更新时根据需要使用

## 🚀 使用方法

### 基本使用

安装完成后，缓存事件监听器会自动工作，记录所有缓存操作。

### 访问管理界面

```
http://your-domain/cache-plus/
```

### API 接口

```
GET  /cache-plus/api/list        # 获取缓存列表
GET  /cache-plus/api/detail      # 获取缓存详情
DELETE /cache-plus/api/delete    # 删除缓存
DELETE /cache-plus/api/clear     # 清空缓存
GET  /cache-plus/api/logs        # 获取日志
GET  /cache-plus/api/statistics  # 获取统计信息
```

## 🔧 故障排除

### 1. 服务未注册

确保在 `config/app.php` 或 `app/provider.php` 中正确注册了服务提供者。

### 2. 数据库表不存在

执行数据库迁移或手动创建 `cache_log` 表。

### 3. 路由冲突

如果路由 `/cache-plus` 与现有路由冲突，可以修改 `routes/web.php` 中的路由前缀。

## 📝 注意事项

1. 本扩展包专为 ThinkPHP 6.0+ 设计
2. 需要启用数据库支持才能使用日志功能
3. 建议在生产环境中定期清理过期日志
4. 管理界面建议添加访问权限控制

## 🆘 获取帮助

如果遇到问题，请：

1. 检查 ThinkPHP 版本兼容性
2. 查看错误日志
3. 提交 Issue 到项目仓库
