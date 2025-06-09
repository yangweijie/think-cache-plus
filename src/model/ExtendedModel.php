<?php

namespace yangweijie\cache\model;

use think\Model;
use yangweijie\cache\db\ExtendedQuery;

/**
 * 扩展模型类 - 使用扩展的查询类
 */
abstract class ExtendedModel extends Model
{
    /**
     * 创建模型的查询对象
     *
     * @return ExtendedQuery
     */
    public function db()
    {
        // 获取数据库连接
        $connection = \think\facade\Db::connect($this->connection);

        // 创建扩展查询对象
        $query = new ExtendedQuery($connection);

        if (!empty($this->table)) {
            $query->table($this->table . $this->suffix);
        } else {
            $query->name($this->name);
        }

        return $query->model($this);
    }

    /**
     * 静态查询
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public static function __callStatic(string $method, array $args)
    {
        $model = new static();
        return call_user_func_array([$model->db(), $method], $args);
    }

    /**
     * 保存数据时自动清理相关缓存
     *
     * @param array  $data     数据
     * @param string $sequence 自增序列名
     * @return bool
     */
    public function save(array $data = [], ?string $sequence = null): bool
    {
        $result = parent::save($data, $sequence);

        if ($result) {
            $this->clearRelatedCache();
        }

        return $result;
    }

    /**
     * 删除数据时自动清理相关缓存
     *
     * @return bool
     */
    public function delete(): bool
    {
        $result = parent::delete();

        if ($result) {
            $this->clearRelatedCache();
        }

        return $result;
    }

    /**
     * 清理相关缓存
     */
    protected function clearRelatedCache(): void
    {
        try {
            $tableName = $this->getTable();

            // 使用缓存标签清理相关缓存
            $cache = \think\facade\Cache::store();
            if ($cache && method_exists($cache, 'tag')) {
                $cache->tag($tableName)->clear();
            }
        } catch (\Exception $e) {
            // 记录错误但不影响主业务
            error_log('清理缓存失败: ' . $e->getMessage());
        }
    }
}
