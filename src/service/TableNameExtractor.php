<?php

namespace yangweijie\cache\service;

use think\db\BaseQuery;

/**
 * 表名提取器 - 从查询对象中提取所有涉及的表名
 */
class TableNameExtractor
{
    /**
     * 从查询对象中提取所有表名
     *
     * @param BaseQuery $query
     * @return array
     */
    public function extractFromQuery(BaseQuery $query): array
    {
        $tables = [];
        $options = $query->getOptions();

        // 提取主表名
        $mainTable = $this->extractMainTable($query, $options);
        if ($mainTable) {
            $tables[] = $mainTable;
        }

        // 提取JOIN表名
        $joinTables = $this->extractJoinTables($options);
        $tables = array_merge($tables, $joinTables);

        // 提取关联查询表名
        $relationTables = $this->extractRelationTables($options);
        $tables = array_merge($tables, $relationTables);

        // 清理表名（去除前缀、别名等）
        $tables = $this->cleanTableNames($tables);

        return array_unique(array_filter($tables));
    }

    /**
     * 提取主表名
     *
     * @param BaseQuery $query
     * @param array $options
     * @return string|null
     */
    protected function extractMainTable(BaseQuery $query, array $options): ?string
    {
        // 从 table 选项中获取
        if (!empty($options['table'])) {
            return $this->parseTableName($options['table']);
        }

        // 从查询对象中获取
        try {
            $table = $query->getTable();
            return $this->parseTableName($table);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 提取JOIN表名
     *
     * @param array $options
     * @return array
     */
    protected function extractJoinTables(array $options): array
    {
        $tables = [];

        if (!empty($options['join'])) {
            foreach ($options['join'] as $join) {
                if (isset($join[0])) {
                    $tableName = $this->parseTableName($join[0]);
                    if ($tableName) {
                        $tables[] = $tableName;
                    }
                }
            }
        }

        return $tables;
    }

    /**
     * 提取关联查询表名
     *
     * @param array $options
     * @return array
     */
    protected function extractRelationTables(array $options): array
    {
        $tables = [];

        // 处理 with_join 关联查询
        if (!empty($options['with_join'])) {
            foreach ($options['with_join'] as $relation) {
                // 这里可以根据关联关系获取相关表名
                // 由于关联关系比较复杂，这里先做简单处理
                if (is_string($relation)) {
                    $tables[] = $relation;
                }
            }
        }

        return $tables;
    }

    /**
     * 解析表名（处理数组、别名等情况）
     *
     * @param mixed $table
     * @return string|null
     */
    protected function parseTableName($table): ?string
    {
        if (is_string($table)) {
            // 处理 "table alias" 格式
            if (strpos($table, ' ') !== false) {
                $parts = explode(' ', trim($table));
                return $parts[0];
            }
            return $table;
        }

        if (is_array($table)) {
            // 处理 ['table' => 'alias'] 格式
            if (count($table) === 1) {
                $key = array_keys($table)[0];
                return is_numeric($key) ? $table[0] : $key;
            }
            // 处理多表情况，返回第一个
            return reset($table);
        }

        return null;
    }

    /**
     * 清理表名（去除前缀、数据库名等）
     *
     * @param array $tables
     * @return array
     */
    protected function cleanTableNames(array $tables): array
    {
        $cleaned = [];

        foreach ($tables as $table) {
            if (empty($table)) {
                continue;
            }

            // 去除数据库名前缀（如 database.table）
            if (strpos($table, '.') !== false) {
                $parts = explode('.', $table);
                $table = end($parts);
            }

            // 去除表前缀（这里需要根据实际配置来处理）
            $table = $this->removeTablePrefix($table);

            if (!empty($table)) {
                $cleaned[] = $table;
            }
        }

        return $cleaned;
    }

    /**
     * 去除表前缀
     *
     * @param string $table
     * @return string
     */
    protected function removeTablePrefix(string $table): string
    {
        try {
            // 获取数据库配置中的表前缀
            $config = \think\facade\Config::get('database.default');
            $dbConfig = \think\facade\Config::get("database.connections.{$config}");
            $prefix = $dbConfig['prefix'] ?? '';

            if (!empty($prefix) && strpos($table, $prefix) === 0) {
                return substr($table, strlen($prefix));
            }
        } catch (\Exception $e) {
            // 如果获取配置失败，返回原表名
        }

        return $table;
    }
}
