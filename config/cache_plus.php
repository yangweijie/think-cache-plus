<?php

return [
    // 是否启用缓存监听
    'enable_listener' => true,

    // 日志保留天数
    'log_retention_days' => 30,

    // 是否记录闭包内容
    'log_closure_content' => true,

    // 最大闭包内容长度（字节）
    'max_closure_content_length' => 10240,

    // 排除监听的缓存key模式（正则表达式）
    'exclude_key_patterns' => [
        '/^session_/',
        '/^csrf_token_/',
        '/^captcha_/',
    ],

    // 排除监听的文件路径模式（正则表达式）
    'exclude_file_patterns' => [
        '/vendor\//',
        '/runtime\//',
        '/storage\//',
    ],

    // 管理界面配置
    'admin' => [
        // 是否启用管理界面
        'enable' => true,

        // 访问密码（为空则不需要密码）
        'password' => '',

        // 每页显示数量
        'page_size' => 20,

        // 是否显示缓存值
        'show_cache_value' => true,

        // 缓存值最大显示长度
        'max_value_display_length' => 1000,
    ],

    // 数据库配置
    'database' => [
        // 表名前缀
        'prefix' => '',

        // 连接名（为空则使用默认连接）
        'connection' => '',
    ],

    // 性能配置
    'performance' => [
        // 是否启用性能监控
        'enable_monitoring' => true,

        // 日志记录频率限制（秒）
        'log_throttle_seconds' => 0,

        // 是否记录调用栈信息
        'log_stack_trace' => true,
    ],
];
