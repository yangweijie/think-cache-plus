<?php

namespace yangweijie\cache\model;

use think\Model;

/**
 * 缓存日志模型
 * 记录缓存操作的详细信息
 */
class CacheLog extends Model
{
    protected $name = 'cache_log';
    
    protected $autoWriteTimestamp = true;
    
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    protected $type = [
        'tags' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取指定key的所有变更记录
     * 
     * @param string $key
     * @return \think\Collection
     */
    public static function getKeyLogs(string $key)
    {
        return self::where('cache_key', $key)
            ->order('created_at', 'desc')
            ->select();
    }

    /**
     * 获取指定tag下的所有缓存记录
     * 
     * @param string $tag
     * @return \think\Collection
     */
    public static function getTagLogs(string $tag)
    {
        return self::whereRaw("JSON_CONTAINS(tags, '\"$tag\"')")
            ->order('created_at', 'desc')
            ->select();
    }

    /**
     * 获取最近的缓存操作记录
     * 
     * @param int $limit
     * @return \think\Collection
     */
    public static function getRecentLogs(int $limit = 100)
    {
        return self::order('created_at', 'desc')
            ->limit($limit)
            ->select();
    }

    /**
     * 获取缓存统计信息
     * 
     * @return array
     */
    public static function getStatistics(): array
    {
        $total = self::count();
        $today = self::whereTime('created_at', 'today')->count();
        $operations = self::field('operation, count(*) as count')
            ->group('operation')
            ->select()
            ->toArray();

        $operationStats = [];
        foreach ($operations as $op) {
            $operationStats[$op['operation']] = $op['count'];
        }

        return [
            'total' => $total,
            'today' => $today,
            'operations' => $operationStats,
        ];
    }

    /**
     * 清理过期的日志记录
     * 
     * @param int $days 保留天数
     * @return int 删除的记录数
     */
    public static function cleanOldLogs(int $days = 30): int
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return self::where('created_at', '<', $date)->delete();
    }

    /**
     * 搜索缓存日志
     * 
     * @param array $params
     * @return \think\Paginator
     */
    public static function search(array $params)
    {
        $query = self::order('created_at', 'desc');

        // 按key搜索
        if (!empty($params['key'])) {
            $query->where('cache_key', 'like', '%' . $params['key'] . '%');
        }

        // 按操作类型搜索
        if (!empty($params['operation'])) {
            $query->where('operation', $params['operation']);
        }

        // 按文件路径搜索
        if (!empty($params['file'])) {
            $query->where('file_path', 'like', '%' . $params['file'] . '%');
        }

        // 按时间范围搜索
        if (!empty($params['start_time'])) {
            $query->where('created_at', '>=', $params['start_time']);
        }
        if (!empty($params['end_time'])) {
            $query->where('created_at', '<=', $params['end_time']);
        }

        // 按tag搜索
        if (!empty($params['tag'])) {
            $query->whereRaw("JSON_CONTAINS(tags, '\"" . $params['tag'] . "\"')");
        }

        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;

        return $query->paginate([
            'list_rows' => $limit,
            'page' => $page,
        ]);
    }
}
