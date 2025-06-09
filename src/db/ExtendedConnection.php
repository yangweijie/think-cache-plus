<?php

namespace yangweijie\cache\db;

use think\db\PDOConnection;

/**
 * 扩展数据库连接类 - 使用扩展的查询构建器
 */
class ExtendedConnection extends PDOConnection
{
    /**
     * 创建查询对象
     *
     * @return ExtendedQuery
     */
    public function newQuery(): ExtendedQuery
    {
        $query = new ExtendedQuery($this);
        
        // 设置模型
        if (!empty($this->model)) {
            $query->model($this->model);
        }

        return $query;
    }
}
