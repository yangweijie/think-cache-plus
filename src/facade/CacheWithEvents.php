<?php

namespace yangweijie\cache\facade;

use think\facade\Cache;
use think\facade\Event;

/**
 * 带事件触发的缓存包装器
 * 支持 tags 和链式调用
 */
class CacheWithEvents
{
    /**
     * 当前实例的标签
     * @var array
     */
    protected $tags = [];

    /**
     * 构造函数
     */
    public function __construct($tags = [])
    {
        $this->tags = is_array($tags) ? $tags : [$tags];
    }

    /**
     * 设置标签（支持链式调用）
     * @param array|string $tags
     * @return static
     */
    public static function tags($tags)
    {
        return new static($tags);
    }

    /**
     * 获取真实调用者信息
     * @return array|null
     */
    protected static function getRealCaller()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $caller = null;

        // 跳过当前类的调用
        foreach ($trace as $item) {
            if (isset($item['file']) && !str_contains($item['file'], 'CacheWithEvents')) {
                $caller = $item;
                break;
            }
        }

        return $caller;
    }

    /**
     * 获取缓存实例（带标签支持）
     * @return \think\Cache
     */
    protected function getCacheInstance()
    {
        if (!empty($this->tags)) {
            // 检查缓存驱动是否支持标签
            try {
                return Cache::tags($this->tags);
            } catch (\Exception $e) {
                // 如果不支持标签，使用普通缓存
                return Cache::store();
            }
        }
        return Cache::store();
    }

    /**
     * 设置缓存并触发事件（实例方法）
     */
    public function setWithTags($key, $value, $expire = null)
    {
        $caller = static::getRealCaller();

        // 使用带标签的缓存实例
        $result = $this->getCacheInstance()->set($key, $value, $expire);

        if ($result) {
            // 触发缓存写入事件，传递调用者信息和标签
            Event::trigger('cache.write', [
                'key' => $key,
                'value' => $value,
                'expire' => $expire,
                'tags' => $this->tags,
                'timestamp' => time(),
                'caller' => $caller
            ]);
        }

        return $result;
    }

    /**
     * 记住缓存并触发事件（实例方法）
     */
    public function rememberWithTags($key, $value, $expire = null)
    {
        $caller = static::getRealCaller();

        // 检查缓存是否已存在
        $exists = $this->getCacheInstance()->has($key);

        // 执行 remember 操作
        $result = $this->getCacheInstance()->remember($key, $value, $expire);

        // 如果缓存之前不存在，说明是新创建的，触发写入事件
        if (!$exists) {
            Event::trigger('cache.write', [
                'key' => $key,
                'value' => $value instanceof \Closure ? $value : $result, // 传递原始闭包或结果
                'closure' => $value instanceof \Closure ? $value : null,   // 单独传递闭包
                'result' => $result,  // 闭包执行结果
                'expire' => $expire,
                'tags' => $this->tags,
                'timestamp' => time(),
                'caller' => $caller
            ]);
        }

        return $result;
    }

    /**
     * 删除缓存并触发事件（实例方法）
     */
    public function deleteWithTags($key)
    {
        $caller = static::getRealCaller();

        // 使用带标签的缓存实例
        $result = $this->getCacheInstance()->delete($key);

        if ($result) {
            // 触发缓存删除事件
            Event::trigger('cache.delete', [
                'key' => $key,
                'tags' => $this->tags,
                'timestamp' => time(),
                'caller' => $caller
            ]);
        }

        return $result;
    }

    /**
     * 清空缓存并触发事件（实例方法）
     */
    public function clearWithTags()
    {
        $caller = static::getRealCaller();

        // 使用带标签的缓存实例
        $result = $this->getCacheInstance()->clear();

        if ($result) {
            // 触发缓存清空事件
            Event::trigger('cache.clear', [
                'tags' => $this->tags,
                'timestamp' => time(),
                'caller' => $caller
            ]);
        }

        return $result;
    }

    /**
     * 获取缓存（不触发事件）
     */
    public function getWithTags($key, $default = null)
    {
        return $this->getCacheInstance()->get($key, $default);
    }

    /**
     * 检查缓存是否存在（不触发事件）
     */
    public function hasWithTags($key)
    {
        return $this->getCacheInstance()->has($key);
    }

    /**
     * 根据标签删除缓存
     */
    public function flush()
    {
        $caller = static::getRealCaller();

        if (empty($this->tags)) {
            return false;
        }

        $result = Cache::tags($this->tags)->clear();

        if ($result) {
            Event::trigger('cache.flush', [
                'tags' => $this->tags,
                'timestamp' => time(),
                'caller' => $caller
            ]);
        }

        return $result;
    }

    // ========== 静态方法（向后兼容） ==========

    /**
     * 静态 set 方法（向后兼容）
     */
    public static function set($key, $value, $expire = null)
    {
        $caller = static::getRealCaller();

        $result = Cache::set($key, $value, $expire);

        if ($result) {
            Event::trigger('cache.write', [
                'key' => $key,
                'value' => $value,
                'expire' => $expire,
                'tags' => [],
                'timestamp' => time(),
                'caller' => $caller
            ]);
        }

        return $result;
    }

    /**
     * 静态 remember 方法（向后兼容）
     */
    public static function remember($key, $value, $expire = null)
    {
        $caller = static::getRealCaller();

        // 检查缓存是否已存在
        $exists = Cache::has($key);

        // 执行 remember 操作
        $result = Cache::remember($key, $value, $expire);

        // 如果缓存之前不存在，说明是新创建的，触发写入事件
        if (!$exists) {
            Event::trigger('cache.write', [
                'key' => $key,
                'value' => $value instanceof \Closure ? $value : $result, // 传递原始闭包或结果
                'closure' => $value instanceof \Closure ? $value : null,   // 单独传递闭包
                'result' => $result,  // 闭包执行结果
                'expire' => $expire,
                'tags' => [],
                'timestamp' => time(),
                'caller' => $caller
            ]);
        }

        return $result;
    }

    /**
     * 静态 delete 方法（向后兼容）
     */
    public static function delete($key)
    {
        $caller = static::getRealCaller();

        $result = Cache::delete($key);

        if ($result) {
            Event::trigger('cache.delete', [
                'key' => $key,
                'tags' => [],
                'timestamp' => time(),
                'caller' => $caller
            ]);
        }

        return $result;
    }

    /**
     * 静态 clear 方法（向后兼容）
     */
    public static function clear()
    {
        $caller = static::getRealCaller();

        $result = Cache::clear();

        if ($result) {
            Event::trigger('cache.clear', [
                'tags' => [],
                'timestamp' => time(),
                'caller' => $caller
            ]);
        }

        return $result;
    }

    /**
     * 静态 get 方法（向后兼容）
     */
    public static function get($key, $default = null)
    {
        return Cache::get($key, $default);
    }

    /**
     * 静态 has 方法（向后兼容）
     */
    public static function has($key)
    {
        return Cache::has($key);
    }
}
