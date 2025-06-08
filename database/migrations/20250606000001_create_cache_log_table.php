<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateCacheLogTable extends Migrator
{
    /**
     * 创建缓存日志表
     */
    public function change()
    {
        $table = $this->table('cache_log', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '缓存操作日志表'
        ]);

        $table->addColumn('id', 'biginteger', [
            'identity' => true,
            'signed' => false,
            'comment' => '主键ID'
        ])
        ->addColumn('cache_key', 'string', [
            'limit' => 255,
            'null' => false,
            'comment' => '缓存键名'
        ])
        ->addColumn('operation', 'string', [
            'limit' => 20,
            'null' => false,
            'comment' => '操作类型：write/delete/clear'
        ])
        ->addColumn('file_path', 'string', [
            'limit' => 500,
            'null' => true,
            'comment' => '调用文件路径'
        ])
        ->addColumn('line_number', 'integer', [
            'null' => true,
            'default' => 0,
            'comment' => '调用行号'
        ])
        ->addColumn('closure_content', 'text', [
            'null' => true,
            'comment' => '闭包函数内容或缓存值'
        ])
        ->addColumn('content_md5', 'string', [
            'limit' => 32,
            'null' => true,
            'comment' => '内容MD5值'
        ])
        ->addColumn('tags', 'json', [
            'null' => true,
            'comment' => '缓存标签'
        ])
        ->addColumn('expire_time', 'integer', [
            'null' => true,
            'default' => 0,
            'comment' => '过期时间（秒）'
        ])
        ->addColumn('request_uri', 'string', [
            'limit' => 500,
            'null' => true,
            'comment' => '请求URI'
        ])
        ->addColumn('user_agent', 'string', [
            'limit' => 500,
            'null' => true,
            'comment' => '用户代理'
        ])
        ->addColumn('created_at', 'datetime', [
            'null' => false,
            'comment' => '创建时间'
        ])
        ->addColumn('updated_at', 'datetime', [
            'null' => true,
            'comment' => '更新时间'
        ])
        ->addIndex(['cache_key'], ['name' => 'idx_cache_key'])
        ->addIndex(['operation'], ['name' => 'idx_operation'])
        ->addIndex(['created_at'], ['name' => 'idx_created_at'])
        ->addIndex(['content_md5'], ['name' => 'idx_content_md5'])
        ->create();
    }
}
