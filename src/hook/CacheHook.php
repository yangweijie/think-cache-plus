<?php

namespace yangweijie\cache\hook;

use think\facade\Event;

/**
 * 缓存钩子类
 * 用于在缓存操作时触发事件
 */
class CacheHook
{
    /**
     * 缓存写入钩子
     * 
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int $expire 过期时间
     * @param array $tags 标签
     */
    public static function onWrite(string $key, $value, int $expire = 0, array $tags = [])
    {
        Event::trigger('cache.write', [
            'key' => $key,
            'value' => $value,
            'expire' => $expire,
            'tags' => $tags,
        ]);
    }

    /**
     * 缓存删除钩子
     * 
     * @param string $key 缓存键
     */
    public static function onDelete(string $key)
    {
        Event::trigger('cache.delete', [
            'key' => $key,
        ]);
    }

    /**
     * 缓存清空钩子
     * 
     * @param array $tags 标签（如果指定）
     */
    public static function onClear(array $tags = [])
    {
        Event::trigger('cache.clear', [
            'tags' => $tags,
        ]);
    }

    /**
     * 缓存标签清空钩子
     * 
     * @param string $tag 标签
     */
    public static function onTagClear(string $tag)
    {
        Event::trigger('cache.tag.clear', [
            'tag' => $tag,
        ]);
    }
}
