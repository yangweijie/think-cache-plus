<?php

namespace yangweijie\cache\service;

use think\facade\Cache;
use yangweijie\cache\model\CacheLog;

/**
 * 缓存管理服务
 * 专注于缓存管理的业务逻辑，避免过度封装
 */
class CacheManager
{
    /**
     * 获取当前所有缓存键
     * 从日志记录中获取，这是有业务价值的方法
     *
     * @return array
     */
    public function getAllCacheKeys(): array
    {
        $keys = [];

        try {
            // 从日志中获取最近的缓存键
            $logs = CacheLog::field('cache_key')
                ->where('operation', 'write')
                ->group('cache_key')
                ->order('created_at', 'desc')
                ->limit(1000)
                ->select();

            foreach ($logs as $log) {
                if (!empty($log->cache_key)) {
                    $keys[] = $log->cache_key;
                }
            }
        } catch (\Exception $e) {
            error_log('获取缓存键失败: ' . $e->getMessage());
        }

        return array_unique($keys);
    }

    /**
     * 获取缓存详细信息（包含日志）
     * 这是有业务价值的方法，组合了缓存数据和日志信息
     *
     * @param string $key
     * @return array
     */
    public function getCacheInfo(string $key): array
    {
        $info = [
            'key' => $key,
            'exists' => Cache::has($key),
            'value' => null,
            'size' => 0,
            'type' => 'unknown',
            'logs' => [],
            'tags' => [],
        ];

        if ($info['exists']) {
            $value = Cache::get($key);
            $info['value'] = $value;
            $info['size'] = strlen(serialize($value));
            $info['type'] = gettype($value);
        }

        // 获取该key的操作日志 - 这是核心业务逻辑
        // 获取该key的操作日志 - 这是核心业务逻辑
        $logs = CacheLog::getKeyLogs($key);
        $info['logs'] = $logs->toArray();

        // 从最新的日志中提取 tags 信息
        if (!$logs->isEmpty()) {
            $latestLog = $logs->first();
            if (!empty($latestLog->tags)) {
                $tags = json_decode($latestLog->tags, true);
                if (is_array($tags)) {
                    $info['tags'] = $tags;
                }
            }
        }

        return $info;
    }

    /**
     * 获取所有标签
     * 从日志中提取标签信息，有业务价值
     *
     * @return array
     */
    public function getAllTags(): array
    {
        $tags = [];

        try {
            $logs = CacheLog::where('tags', '<>', '')
                ->where('tags', 'not null')
                ->field('tags')
                ->select();

            foreach ($logs as $log) {
                if (!empty($log->tags)) {
                    $logTags = json_decode($log->tags, true);
                    if (is_array($logTags)) {
                        $tags = array_merge($tags, $logTags);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('获取tags失败: ' . $e->getMessage());
        }

        return array_unique($tags);
    }

    /**
     * 获取缓存统计信息
     * 组合多个数据源的统计，有业务价值
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $stats = CacheLog::getStatistics();

        // 添加当前缓存数量
        $currentKeys = $this->getAllCacheKeys();
        $existingKeys = 0;

        foreach ($currentKeys as $key) {
            if (Cache::has($key)) {
                $existingKeys++;
            }
        }

        $stats['current_keys'] = count($currentKeys);
        $stats['existing_keys'] = $existingKeys;
        $stats['tags'] = count($this->getAllTags());

        return $stats;
    }

    /**
     * 批量删除缓存
     * 提供批量操作和结果反馈，有业务价值
     *
     * @param array $keys
     * @return array 删除结果
     */
    public function batchDeleteCache(array $keys): array
    {
        $results = [];

        foreach ($keys as $key) {
            try {
                $results[$key] = Cache::delete($key);
            } catch (\Exception $e) {
                $results[$key] = false;
                error_log("删除缓存 {$key} 失败: " . $e->getMessage());
            }
        }

        return $results;
    }
}
