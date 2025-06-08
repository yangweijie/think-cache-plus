<?php

/**
 * 闭包内容记录演示
 * 
 * 这个示例演示了修复后的 CacheWithEvents::remember() 
 * 如何正确记录闭包函数体内容而不是缓存值
 */

require_once __DIR__ . '/../vendor/autoload.php';

use yangweijie\cache\facade\CacheWithEvents;

// 模拟 ThinkPHP Request 对象
class MockRequest 
{
    public $id = 123;
    
    public function param($key, $default = null) 
    {
        return $key === 'id' ? $this->id : $default;
    }
}

echo "=== 闭包内容记录演示 ===\n\n";

// 示例1：简单的时间戳闭包
echo "1. 简单时间戳闭包:\n";
$result1 = CacheWithEvents::remember('test_time', function() {
    return time();
});
echo "缓存结果: {$result1}\n";
echo "期望记录的闭包内容: return time();\n\n";

// 示例2：带参数的闭包
echo "2. 带参数的闭包:\n";
$request = new MockRequest();
$result2 = CacheWithEvents::remember('test_request', function($request) {
    return $request->param('id');
}, 3600);
echo "缓存结果: {$result2}\n";
echo "期望记录的闭包内容: return \$request->param('id');\n\n";

// 示例3：复杂逻辑的闭包
echo "3. 复杂逻辑闭包:\n";
$result3 = CacheWithEvents::remember('test_complex', function() {
    $data = [
        'timestamp' => time(),
        'random' => rand(1, 100),
        'status' => 'active'
    ];
    return json_encode($data);
});
echo "缓存结果: {$result3}\n";
echo "期望记录的闭包内容: \n";
echo "    \$data = [\n";
echo "        'timestamp' => time(),\n";
echo "        'random' => rand(1, 100),\n";
echo "        'status' => 'active'\n";
echo "    ];\n";
echo "    return json_encode(\$data);\n\n";

// 示例4：使用标签的闭包
echo "4. 带标签的闭包:\n";
$result4 = CacheWithEvents::tags(['user', 'profile'])->rememberWithTags('user_profile', function() {
    return [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'created_at' => date('Y-m-d H:i:s')
    ];
});
echo "缓存结果: " . json_encode($result4) . "\n";
echo "期望记录的闭包内容: \n";
echo "    return [\n";
echo "        'name' => 'John Doe',\n";
echo "        'email' => 'john@example.com',\n";
echo "        'created_at' => date('Y-m-d H:i:s')\n";
echo "    ];\n\n";

echo "=== 修复说明 ===\n";
echo "修复前: closure_content 字段存储的是缓存值（如时间戳 1234567890）\n";
echo "修复后: closure_content 字段存储的是闭包函数体内容（如 return time();）\n\n";

echo "=== 数据库记录示例 ===\n";
echo "cache_key: test_time\n";
echo "operation: write\n";
echo "closure_content: return time();\n";
echo "content_md5: " . md5('return time();') . "\n";
echo "file_path: " . __FILE__ . "\n";
echo "line_number: [调用行号]\n";
echo "tags: []\n";
echo "expire_time: 0\n";
echo "created_at: " . date('Y-m-d H:i:s') . "\n\n";

echo "=== 技术实现要点 ===\n";
echo "1. CacheWithEvents 在触发事件时同时传递原始闭包和执行结果\n";
echo "2. CacheEventListener 优先检查 'closure' 字段获取原始闭包\n";
echo "3. 使用 ReflectionFunction 获取闭包源码\n";
echo "4. extractClosureBody() 方法提取函数体内容（去掉 function(){} 包装）\n";
echo "5. 确保记录的是闭包逻辑而不是执行结果\n\n";

echo "演示完成！\n";
