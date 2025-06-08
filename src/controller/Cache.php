<?php

namespace yangweijie\cache\controller;

// use think\Controller; // 在测试环境中可能不存在
use think\Request;
use think\Response;
use think\facade\Cache as Caches;
use yangweijie\cache\service\CacheManager;
use yangweijie\cache\model\CacheLog;

/**
 * 缓存管理控制器
 */
class Cache
{
    protected $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * 缓存管理首页
     */
    public function index()
    {
        $stats = $this->cacheManager->getStatistics();
        $recentLogs = CacheLog::getRecentLogs(10);

        return view('cache_plus/index', [
            'stats' => $stats,
            'recent_logs' => $recentLogs,
        ]);
    }

    /**
     * 获取缓存列表
     */
    public function list(Request $request)
    {
        $page = $request->param('page', 1);
        $limit = $request->param('limit', 20);
        $key = $request->param('key', '');

        $keys = $this->cacheManager->getAllCacheKeys();

        if ($key) {
            $keys = array_filter($keys, function($k) use ($key) {
                return strpos($k, $key) !== false;
            });
        }

        // 分页处理
        $total = count($keys);
        $offset = ($page - 1) * $limit;
        $keys = array_slice($keys, $offset, $limit);

        $caches = [];
        foreach ($keys as $cacheKey) {
            $info = $this->cacheManager->getCacheInfo($cacheKey);
            
            // 统一字段名，同时保持前端兼容性
            $info['cache_key'] = $info['key'];
            // 保留 key 字段供前端使用
            // unset($info['key']); // 不删除，前端需要使用 cache.key
            
            // 检查缓存是否真实存在
            $info['exists'] = Caches::has($cacheKey);
            $info['status'] = $info['exists'] ? 'active' : 'expired';
            
            // 获取最新的操作记录
            $latestLog = CacheLog::where('cache_key', $cacheKey)
                ->order('created_at', 'desc')
                ->find();
            
            if ($latestLog) {
                $info['last_operation'] = $latestLog->operation;
                $info['last_operation_time'] = $latestLog->created_at;
                $info['tags'] = $latestLog->tags ? json_decode($latestLog->tags, true) : [];
            }
            
            $caches[] = $info;
        }

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $caches,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * 获取缓存详情
     */
    public function detail(Request $request)
    {
        $key = $request->param('key');

        if (empty($key)) {
            return json(['code' => 1, 'msg' => '缓存key不能为空']);
        }

        $info = $this->cacheManager->getCacheInfo($key);

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $info,
        ]);
    }

    /**
     * 删除缓存
     */
    public function delete(Request $request)
    {
        $key = $request->param('key');

        if (empty($key)) {
            return json(['code' => 1, 'msg' => '缓存key不能为空']);
        }

        // 直接使用Cache门面，避免过度封装
        $result = Caches::delete($key);

        return json([
            'code' => $result ? 0 : 1,
            'msg' => $result ? '删除成功' : '删除失败',
        ]);
    }

    /**
     * 批量删除缓存
     */
    public function batchDelete(Request $request)
    {
        $keys = $request->param('keys', []);

        if (empty($keys) || !is_array($keys)) {
            return json(['code' => 1, 'msg' => '请选择要删除的缓存']);
        }

        $results = $this->cacheManager->batchDeleteCache($keys);
        $success = array_filter($results);

        return json([
            'code' => 0,
            'msg' => sprintf('成功删除 %d/%d 个缓存', count($success), count($keys)),
            'data' => $results,
        ]);
    }

    /**
     * 按tag删除缓存
     */
    public function deleteByTag(Request $request)
    {
        $tag = $request->param('tag');

        if (empty($tag)) {
            return json(['code' => 1, 'msg' => 'tag不能为空']);
        }

        try {
            // 从数据库日志中找到包含指定 tag 的所有缓存键
            $logs = CacheLog::where('tags', 'like', '%' . $tag . '%')
                ->field('cache_key')
                ->group('cache_key')
                ->select();

            $deletedKeys = [];
            $failedKeys = [];

            foreach ($logs as $log) {
                $cacheKey = $log->cache_key;
                
                // 检查缓存是否存在
                if (Caches::has($cacheKey)) {
                    // 删除实际缓存
                    if (Caches::delete($cacheKey)) {
                        $deletedKeys[] = $cacheKey;
                        
                        // 记录删除操作到日志
                        CacheLog::create([
                            'cache_key' => $cacheKey,
                            'operation' => 'delete',
                            'file_path' => __FILE__,
                            'line_number' => __LINE__,
                            'closure_content' => "Deleted by tag: {$tag}",
                            'content_md5' => md5("tag_delete_{$tag}"),
                            'tags' => json_encode([$tag]),
                            'expire_time' => 0,
                            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s.u'),
                        ]);
                    } else {
                        $failedKeys[] = $cacheKey;
                    }
                }
            }

            $totalFound = count($logs);
            $totalDeleted = count($deletedKeys);
            $totalFailed = count($failedKeys);

            if ($totalFound === 0) {
                return json([
                    'code' => 0,
                    'msg' => "未找到包含标签 '{$tag}' 的缓存",
                    'data' => [
                        'found' => 0,
                        'deleted' => 0,
                        'failed' => 0,
                        'deleted_keys' => [],
                        'failed_keys' => []
                    ]
                ]);
            }

            return json([
                'code' => 0,
                'msg' => "删除成功：找到 {$totalFound} 个缓存，成功删除 {$totalDeleted} 个" . 
                         ($totalFailed > 0 ? "，失败 {$totalFailed} 个" : ""),
                'data' => [
                    'found' => $totalFound,
                    'deleted' => $totalDeleted,
                    'failed' => $totalFailed,
                    'deleted_keys' => $deletedKeys,
                    'failed_keys' => $failedKeys
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'code' => 1,
                'msg' => '删除失败：' . $e->getMessage(),
            ]);
        }
    }

    /**
     * 清空所有缓存
     */
    public function clear()
    {
        // 直接使用Cache门面，避免过度封装
        $result = Caches::clear();

        return json([
            'code' => $result ? 0 : 1,
            'msg' => $result ? '清空成功' : '清空失败',
        ]);
    }

    /**
     * 获取所有tags
     */
    public function tags()
    {
        $tags = $this->cacheManager->getAllTags();

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $tags,
        ]);
    }

    /**
     * 获取缓存日志
     */
    public function logs(Request $request)
    {
        $params = $request->param();
        $logs = CacheLog::search($params);

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $logs->items(),
            'total' => $logs->total(),
            'page' => $logs->currentPage(),
            'limit' => $logs->listRows(),
        ]);
    }

    /**
     * 获取统计信息
     */
    public function statistics()
    {
        $stats = $this->cacheManager->getStatistics();

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $stats,
        ]);
    }
}
