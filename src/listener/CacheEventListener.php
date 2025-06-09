<?php

namespace yangweijie\cache\listener;

use think\Event;
use think\facade\Config;
use yangweijie\cache\model\CacheLog;

/**
 * 缓存事件监听器
 * 监听缓存的写入、删除等操作，记录详细信息
 */
class CacheEventListener
{
    /**
     * 配置缓存
     * @var array
     */
    protected $config = null;

    /**
     * 日志记录频率限制缓存
     * @var array
     */
    protected static $throttleCache = [];

    /**
     * 获取配置
     * @return array
     */
    protected function getConfig(): array
    {
        if ($this->config === null) {
            $this->config = Config::get('cache_plus', []);
        }
        return $this->config;
    }
    /**
     * 监听缓存写入事件
     *
     * @param Event $event
     * @param array $data
     */
    public function onCacheWrite(Event $event, array $data)
    {
        $this->logCacheOperation('write', $data);
    }

    /**
     * 监听缓存删除事件
     *
     * @param Event $event
     * @param array $data
     */
    public function onCacheDelete(Event $event, array $data)
    {
        $this->logCacheOperation('delete', $data);
    }

    /**
     * 监听缓存清空事件
     *
     * @param Event $event
     * @param array $data
     */
    public function onCacheClear(Event $event, array $data)
    {
        $this->logCacheOperation('clear', $data);
    }

    /**
     * 记录缓存操作
     *
     * @param string $operation 操作类型
     * @param array $data 缓存数据
     */
    protected function logCacheOperation(string $operation, array $data)
    {
        try {
            $config = $this->getConfig();

            // 检查是否启用监听
            if (!($config['enable_listener'] ?? true)) {
                return;
            }

            // 检查性能监控是否启用
            $performanceConfig = $config['performance'] ?? [];
            if (!($performanceConfig['enable_monitoring'] ?? true)) {
                return;
            }

            // 检查日志记录频率限制
            if ($this->shouldThrottleLog($data['key'] ?? '', $performanceConfig)) {
                return;
            }

            // 检查缓存键是否需要排除
            if ($this->shouldExcludeKey($data['key'] ?? '')) {
                return;
            }

            // 如果事件数据中包含调用者信息，直接使用
            if (isset($data['caller']) && $data['caller']) {
                $caller = $data['caller'];
            } else {
                // 获取调用栈信息
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
                $caller = $this->findRealCaller($trace);
            }

            // 检查文件路径是否需要排除
            if ($this->shouldExcludeFile($caller['file'] ?? '')) {
                return;
            }

            // 获取闭包内容（如果存在）
            $closureContent = '';
            $closureMd5 = '';

            // 检查是否记录闭包内容
            if ($config['log_closure_content'] ?? true) {
                // 优先检查单独传递的闭包
                if (isset($data['closure']) && $data['closure'] instanceof \Closure) {
                    $closureContent = $this->getClosureContent($data['closure']);
                    $closureMd5 = md5($closureContent);
                } elseif (isset($data['value']) && $data['value'] instanceof \Closure) {
                    $closureContent = $this->getClosureContent($data['value']);
                    $closureMd5 = md5($closureContent);
                } elseif (isset($data['result'])) {
                    // 如果有闭包执行结果，记录结果而不是闭包内容
                    $closureContent = is_string($data['result']) ? $data['result'] : serialize($data['result']);
                    $closureMd5 = md5($closureContent);
                } elseif (isset($data['value'])) {
                    $closureContent = is_string($data['value']) ? $data['value'] : serialize($data['value']);
                    $closureMd5 = md5($closureContent);
                }

                // 检查闭包内容长度限制
                $maxLength = $config['max_closure_content_length'] ?? 10240;
                if (strlen($closureContent) > $maxLength) {
                    $closureContent = substr($closureContent, 0, $maxLength) . '... [truncated]';
                    $closureMd5 = md5($closureContent);
                }
            }

            // 记录到数据库
            CacheLog::create([
                'cache_key' => $data['key'] ?? '',
                'operation' => $operation,
                'file_path' => $caller['file'] ?? '',
                'line_number' => $caller['line'] ?? 0,
                'closure_content' => $closureContent,
                'content_md5' => $closureMd5,
                'tags' => isset($data['tags']) ? json_encode($data['tags']) : '',
                'expire_time' => $data['expire'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);

        } catch (\Exception $e) {
            // 记录错误但不影响正常流程
            error_log('CacheEventListener Error: ' . $e->getMessage());
        }
    }

    /**
     * 查找真实的调用者（排除框架内部调用）
     *
     * @param array $trace
     * @return array
     */
    protected function findRealCaller(array $trace): array
    {
        $skipPatterns = [
            '/vendor\/topthink\/think-cache/',
            '/vendor\/topthink\/framework/',
            '/think\/cache/',
            '/yangweijie\/cache/',
            '/think-cache-plus\/src\/listener/',
            '/app\/common\/CacheWithEvents\.php$/',
            '/CacheEventListener\.php$/',
            '/CacheWithEvents\.php$/',
            '/Event\.php$/',
            '/facade\/Event\.php$/',
        ];

        // 同时检查类名和方法名
        $skipClasses = [
            'yangweijie\cache\listener\CacheEventListener',
            'app\common\CacheWithEvents',
            'think\facade\Event',
            'think\Event',
        ];

        foreach ($trace as $item) {
            if (!isset($item['file'])) {
                continue;
            }

            $skip = false;

            // 检查文件路径模式
            foreach ($skipPatterns as $pattern) {
                if (preg_match($pattern, $item['file'])) {
                    $skip = true;
                    break;
                }
            }

            // 检查类名
            if (!$skip && isset($item['class'])) {
                foreach ($skipClasses as $skipClass) {
                    if ($item['class'] === $skipClass) {
                        $skip = true;
                        break;
                    }
                }
            }

            if (!$skip) {
                return $item;
            }
        }

        return $trace[0] ?? [];
    }

    /**
     * 获取闭包函数的内容（只提取函数体）
     *
     * @param \Closure $closure
     * @return string
     */
    protected function getClosureContent(\Closure $closure): string
    {
        try {
            $reflection = new \ReflectionFunction($closure);
            $filename = $reflection->getFileName();
            $startLine = $reflection->getStartLine();
            $endLine = $reflection->getEndLine();

            if ($filename && $startLine && $endLine) {
                $lines = file($filename);
                $closureLines = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);
                $fullContent = implode('', $closureLines);

                // 尝试提取函数体内容（去掉 function() { 和 }）
                return $this->extractClosureBody($fullContent);
            }
        } catch (\Exception $e) {
            // 如果无法获取闭包内容，返回序列化结果
        }

        return serialize($closure);
    }

    /**
     * 提取闭包函数体内容
     *
     * @param string $fullContent
     * @return string
     */
    protected function extractClosureBody(string $fullContent): string
    {
        // 移除前后空白
        $content = trim($fullContent);

        // 查找第一个 { 和最后一个 }
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');

        if ($firstBrace !== false && $lastBrace !== false && $firstBrace < $lastBrace) {
            // 提取大括号之间的内容
            $body = substr($content, $firstBrace + 1, $lastBrace - $firstBrace - 1);
            return trim($body);
        }

        // 如果无法解析，返回原始内容
        return $content;
    }

    /**
     * 检查缓存键是否需要排除
     *
     * @param string $key
     * @return bool
     */
    protected function shouldExcludeKey(string $key): bool
    {
        $config = $this->getConfig();
        $patterns = $config['exclude_key_patterns'] ?? [];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 检查文件路径是否需要排除
     *
     * @param string $file
     * @return bool
     */
    protected function shouldExcludeFile(string $file): bool
    {
        $config = $this->getConfig();
        $patterns = $config['exclude_file_patterns'] ?? [];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 检查是否应该限制日志记录频率
     *
     * @param string $key
     * @param array $performanceConfig
     * @return bool
     */
    protected function shouldThrottleLog(string $key, array $performanceConfig): bool
    {
        $throttleSeconds = $performanceConfig['log_throttle_seconds'] ?? 0;

        // 如果没有设置频率限制，不限制
        if ($throttleSeconds <= 0) {
            return false;
        }

        $now = time();
        $throttleKey = md5($key);

        // 检查是否在限制时间内
        if (isset(self::$throttleCache[$throttleKey])) {
            $lastLogTime = self::$throttleCache[$throttleKey];
            if (($now - $lastLogTime) < $throttleSeconds) {
                return true; // 需要限制
            }
        }

        // 更新最后记录时间
        self::$throttleCache[$throttleKey] = $now;

        // 清理过期的缓存项（避免内存泄漏）
        if (count(self::$throttleCache) > 1000) {
            $cutoff = $now - $throttleSeconds;
            self::$throttleCache = array_filter(self::$throttleCache, function($time) use ($cutoff) {
                return $time > $cutoff;
            });
        }

        return false;
    }
}
