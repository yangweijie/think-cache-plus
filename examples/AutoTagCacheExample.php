<?php

/**
 * 自动表名标签缓存功能使用示例
 *
 * 本示例展示如何使用 ThinkCache Plus 的自动表名标签缓存功能
 */

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use yangweijie\cache\model\ExtendedModel;
use think\facade\Db;
use think\facade\Cache;

/**
 * 示例用户模型
 */
class User extends ExtendedModel
{
    protected $table = 'user';
}

/**
 * 示例用户资料模型
 */
class UserProfile extends ExtendedModel
{
    protected $table = 'user_profile';
}

/**
 * 示例订单模型
 */
class Order extends ExtendedModel
{
    protected $table = 'order';
}

echo "=== ThinkCache Plus 自动表名标签缓存示例 ===\n\n";

// 1. 单表查询缓存示例
echo "1. 单表查询缓存示例\n";
echo "-------------------\n";

// 使用模型查询，自动添加 'user' 标签
$activeUsers = User::where('status', 1)
    ->cache('active_users', 3600)  // 缓存1小时，自动添加 'user' 标签
    ->select();

echo "查询活跃用户，自动添加 'user' 标签\n";
echo "缓存键: active_users\n";
echo "标签: ['user']\n\n";

// 使用 Db 查询
$userCount = Db::name('user')
    ->where('status', 1)
    ->cache('user_count', 1800)  // 缓存30分钟，自动添加 'user' 标签
    ->count();

echo "统计用户数量，自动添加 'user' 标签\n";
echo "缓存键: user_count\n";
echo "标签: ['user']\n\n";

// 2. JOIN 查询缓存示例
echo "2. JOIN 查询缓存示例\n";
echo "-------------------\n";

// 用户和资料的关联查询
$usersWithProfile = User::alias('u')
    ->join('user_profile p', 'u.id = p.user_id')
    ->field('u.*, p.nickname, p.avatar')
    ->where('u.status', 1)
    ->cache('users_with_profile', 3600)  // 自动添加 ['user', 'user_profile'] 标签
    ->select();

echo "用户关联资料查询，自动添加多个表标签\n";
echo "缓存键: users_with_profile\n";
echo "标签: ['user', 'user_profile']\n\n";

// 2.1 模型关联查询缓存示例
echo "2.1 模型关联查询缓存示例\n";
echo "------------------------\n";

// withJoin 关联查询 - 会自动添加关联表标签
$usersWithJoin = User::withJoin(['profile'])
    ->cache('users_withjoin_profile', 3600)  // 自动添加 ['user', 'user_profile'] 标签
    ->select();

echo "withJoin 关联查询，自动添加关联表标签\n";
echo "缓存键: users_withjoin_profile\n";
echo "标签: ['user', 'user_profile']\n\n";

// 普通 with 预载入 - 只添加主表标签
$usersWithPreload = User::with(['profile'])
    ->cache('users_with_preload', 3600)  // 只添加 ['user'] 标签
    ->select();

echo "普通 with 预载入查询，只添加主表标签\n";
echo "缓存键: users_with_preload\n";
echo "标签: ['user'] (不包含关联表)\n\n";

// 关联查询单独缓存 - 关联表会有自己的缓存标签
$usersWithCache = User::with(['profile'])
    ->withCache(['profile' => ['profile_cache', 3600]])  // profile 查询会有 'user_profile' 标签
    ->select();

echo "关联查询单独缓存，关联表有自己的缓存标签\n";
echo "主查询标签: ['user']\n";
echo "关联查询标签: ['user_profile']\n\n";

// 复杂的多表查询
$userOrders = Db::name('user')
    ->alias('u')
    ->join('user_profile p', 'u.id = p.user_id')
    ->join('order o', 'u.id = o.user_id')
    ->field('u.username, p.nickname, COUNT(o.id) as order_count')
    ->where('u.status', 1)
    ->group('u.id')
    ->cache('user_order_stats', 1800)  // 自动添加 ['user', 'user_profile', 'order'] 标签
    ->select();

echo "用户订单统计查询，自动添加所有相关表标签\n";
echo "缓存键: user_order_stats\n";
echo "标签: ['user', 'user_profile', 'order']\n\n";

// 3. 自定义标签与自动标签合并
echo "3. 自定义标签与自动标签合并\n";
echo "-----------------------------\n";

$vipUsers = User::where('vip_level', '>', 0)
    ->cache('vip_users', 3600, ['vip', 'premium'])  // 用户标签 + 自动 'user' 标签
    ->select();

echo "VIP用户查询，合并自定义标签和自动标签\n";
echo "缓存键: vip_users\n";
echo "标签: ['vip', 'premium', 'user']\n\n";

// 4. 数据更新自动清理缓存
echo "4. 数据更新自动清理缓存\n";
echo "-----------------------\n";

// 模拟数据更新
echo "更新用户数据...\n";
$user = new User();
$user->id = 1;
$user->username = 'updated_user';
$user->exists(true);  // 标记为已存在的记录
// $user->save();  // 这会自动清理所有 'user' 标签的缓存

echo "用户数据更新完成，自动清理 'user' 标签的所有缓存\n";
echo "受影响的缓存: active_users, user_count, users_with_profile, user_order_stats, vip_users\n\n";

// 5. 手动清理标签缓存
echo "5. 手动清理标签缓存\n";
echo "------------------\n";

// 清理单个表的缓存
echo "清理用户表相关的所有缓存...\n";
// Cache::tag('user')->clear();

// 清理多个表的缓存
echo "清理用户和订单相关的所有缓存...\n";
// Cache::tag(['user', 'order'])->clear();

echo "缓存清理完成\n\n";

// 6. 查看缓存日志
echo "6. 查看缓存日志\n";
echo "--------------\n";

echo "所有缓存操作都会记录到 cache_log 表中，包括:\n";
echo "- 缓存键名\n";
echo "- 操作类型 (write/delete/clear)\n";
echo "- 涉及的表名标签\n";
echo "- 调用文件和行号\n";
echo "- 创建时间\n\n";

echo "可以通过以下方式查看日志:\n";
echo "1. 访问管理界面: http://your-domain/cache-plus/\n";
echo "2. 直接查询数据库: SELECT * FROM cache_log WHERE tags != '' ORDER BY created_at DESC\n\n";

// 7. 配置选项
echo "7. 配置选项\n";
echo "----------\n";

echo "在 config/cache_plus.php 中可以配置:\n";
echo "- enable_listener: 是否启用缓存监听\n";
echo "- log_closure_content: 是否记录闭包内容\n";
echo "- exclude_key_patterns: 排除监听的缓存键模式\n";
echo "- performance.enable_monitoring: 是否启用性能监控\n";
echo "- performance.log_throttle_seconds: 日志记录频率限制\n\n";

echo "=== 示例完成 ===\n";

/**
 * 实际使用建议
 */
echo "\n实际使用建议:\n";
echo "1. 确保缓存驱动支持标签功能 (推荐使用 Redis)\n";
echo "2. 继承 ExtendedModel 类来使用自动缓存清理功能\n";
echo "3. 合理设置缓存过期时间，避免缓存雪崩\n";
echo "4. 在高并发场景下适当配置日志记录频率限制\n";
echo "5. 定期清理过期的缓存日志记录\n";
