<?php

namespace yangweijie\cache\db;

use think\db\Query;
use yangweijie\cache\service\TableNameExtractor;

/**
 * 扩展查询类 - 自动添加表名作为缓存标签
 */
class ExtendedQuery extends Query
{
    /**
     * 查询缓存 数据为空不缓存
     * 自动添加表名作为缓存标签
     *
     * @param mixed         $key    缓存key
     * @param int|\DateTime $expire 缓存有效期
     * @param string|array  $tag    缓存标签
     * @param bool          $always 始终缓存
     *
     * @return $this
     */
    public function cache($key = true, $expire = null, $tag = null, bool $always = false)
    {
        if (false === $key || !$this->getConnection()->getCache()) {
            return $this;
        }

        if ($key instanceof \DateTimeInterface || $key instanceof \DateInterval || (is_int($key) && is_null($expire))) {
            $expire = $key;
            $key    = true;
        }

        // 自动提取表名作为标签
        $autoTags = $this->extractTableTags();
        
        // 合并用户指定的标签和自动提取的表名标签
        if ($tag !== null) {
            if (is_string($tag)) {
                $tag = [$tag];
            }
            $tag = array_merge((array)$tag, $autoTags);
        } else {
            $tag = $autoTags;
        }

        // 去重并过滤空值
        $tag = array_unique(array_filter($tag));

        $this->options['cache']         = [$key, $expire, $tag];
        $this->options['cache_always']  = $always;

        return $this;
    }

    /**
     * 提取查询涉及的所有表名作为缓存标签
     *
     * @return array
     */
    protected function extractTableTags(): array
    {
        $extractor = new TableNameExtractor();
        return $extractor->extractFromQuery($this);
    }

    /**
     * 获取查询选项（供外部访问）
     *
     * @param string $name 选项名称
     * @return mixed
     */
    public function getOptions(string $name = '')
    {
        if ('' === $name) {
            return $this->options;
        }

        return $this->options[$name] ?? null;
    }
}
