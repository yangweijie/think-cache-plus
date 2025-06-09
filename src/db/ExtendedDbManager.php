<?php

namespace yangweijie\cache\db;

use think\DbManager;
use think\db\ConnectionInterface;

/**
 * 扩展数据库管理器 - 使用扩展的连接类
 */
class ExtendedDbManager extends DbManager
{
    /**
     * 创建数据库连接实例
     *
     * @param array  $config 连接配置
     * @param string $name   连接名称
     * @return ConnectionInterface
     */
    protected function createConnection(array $config, string $name): ConnectionInterface
    {
        $type = !empty($config['type']) ? $config['type'] : 'mysql';

        if (false !== strpos($type, '\\')) {
            $class = $type;
        } else {
            $class = '\\think\\db\\connector\\' . ucfirst($type);
        }

        // 对于 PDO 类型的连接，使用扩展连接
        if (in_array($type, ['mysql', 'pgsql', 'sqlite', 'sqlsrv'])) {
            $connection = new ExtendedConnection($config);
        } else {
            // 对于其他类型，使用原始连接
            $connection = new $class($config);
        }

        $connection->setDb($this);

        if (!empty($config['trigger_sql'])) {
            $connection->trigger($config['trigger_sql']);
        }

        return $connection;
    }

    /**
     * 创建查询对象
     *
     * @param string|null $connection 连接名称
     * @return ExtendedQuery
     */
    public function newQuery(?string $connection = null): ExtendedQuery
    {
        /** @var ExtendedConnection $conn */
        $conn = $this->connect($connection);
        return $conn->newQuery();
    }
}
