<?php

namespace yangweijie\cache\listener;

use yangweijie\cache\listener\CacheEventListener;
use yangweijie\cache\service\TableNameExtractor;

/**
 * 扩展缓存事件监听器 - 处理表名标签
 */
class ExtendedCacheEventListener extends CacheEventListener
{
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

            // 获取调用栈信息
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $caller = $this->findRealCaller($trace);

            // 检查文件路径是否需要排除
            if (!empty($caller['file']) && $this->shouldExcludeFile($caller['file'])) {
                return;
            }

            // 处理闭包内容
            $closureContent = '';
            $closureMd5 = '';

            if (($config['log_closure_content'] ?? true) && isset($data['closure']) && $data['closure'] instanceof \Closure) {
                $closureContent = $this->getClosureContent($data['closure']);
                $closureMd5 = md5($closureContent);

                // 检查闭包内容长度限制
                $maxLength = $config['max_closure_content_length'] ?? 10240;
                if (strlen($closureContent) > $maxLength) {
                    $closureContent = substr($closureContent, 0, $maxLength) . '... [truncated]';
                    $closureMd5 = md5($closureContent);
                }
            }

            // 处理表名标签
            $tags = $this->processTableTags($data);

            // 记录到数据库
            \yangweijie\cache\model\CacheLog::create([
                'cache_key' => $data['key'] ?? '',
                'operation' => $operation,
                'file_path' => $caller['file'] ?? '',
                'line_number' => $caller['line'] ?? 0,
                'closure_content' => $closureContent,
                'content_md5' => $closureMd5,
                'tags' => $tags ? json_encode($tags) : '',
                'expire_time' => $data['expire'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);

        } catch (\Exception $e) {
            // 记录错误但不影响正常流程
            error_log('ExtendedCacheEventListener Error: ' . $e->getMessage());
        }
    }

    /**
     * 处理表名标签
     *
     * @param array $data
     * @return array
     */
    protected function processTableTags(array $data): array
    {
        $tags = [];

        // 获取用户指定的标签
        if (isset($data['tags'])) {
            if (is_string($data['tags'])) {
                $tags[] = $data['tags'];
            } elseif (is_array($data['tags'])) {
                $tags = array_merge($tags, $data['tags']);
            }
        }

        // 尝试从调用栈中提取表名标签
        $tableTags = $this->extractTableTagsFromTrace();
        $tags = array_merge($tags, $tableTags);

        // 去重并过滤空值
        return array_unique(array_filter($tags));
    }

    /**
     * 从调用栈中提取表名标签
     *
     * @return array
     */
    protected function extractTableTagsFromTrace(): array
    {
        $tags = [];
        
        try {
            $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
            
            foreach ($trace as $item) {
                // 检查是否是查询对象
                if (isset($item['object']) && 
                    ($item['object'] instanceof \yangweijie\cache\db\ExtendedQuery ||
                     $item['object'] instanceof \think\db\BaseQuery)) {
                    
                    $extractor = new TableNameExtractor();
                    $tableTags = $extractor->extractFromQuery($item['object']);
                    $tags = array_merge($tags, $tableTags);
                    break; // 找到第一个查询对象就够了
                }
                
                // 检查是否是模型对象
                if (isset($item['object']) && 
                    ($item['object'] instanceof \yangweijie\cache\model\ExtendedModel ||
                     $item['object'] instanceof \think\Model)) {
                    
                    $tableName = $item['object']->getTable();
                    if ($tableName) {
                        $tags[] = $this->cleanTableName($tableName);
                    }
                    break;
                }
            }
        } catch (\Exception $e) {
            // 忽略错误，返回空数组
        }

        return array_unique(array_filter($tags));
    }

    /**
     * 清理表名
     *
     * @param string $tableName
     * @return string
     */
    protected function cleanTableName(string $tableName): string
    {
        // 去除数据库名前缀
        if (strpos($tableName, '.') !== false) {
            $parts = explode('.', $tableName);
            $tableName = end($parts);
        }

        // 这里可以根据配置去除表前缀
        // 暂时返回原表名
        return $tableName;
    }
}
