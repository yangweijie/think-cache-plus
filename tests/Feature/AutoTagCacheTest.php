<?php

use yangweijie\cache\db\ExtendedQuery;
use yangweijie\cache\model\ExtendedModel;
use yangweijie\cache\service\TableNameExtractor;

/**
 * 自动表名标签缓存功能测试
 */
class AutoTagCacheTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 这里可以设置测试环境
    }

    /**
     * 测试表名提取器
     */
    public function testTableNameExtractor()
    {
        $extractor = new TableNameExtractor();
        
        // 模拟查询选项
        $options = [
            'table' => 'user',
            'join' => [
                ['user_profile', 'INNER', 'user.id = user_profile.user_id'],
                ['user_role', 'LEFT', 'user.id = user_role.user_id']
            ]
        ];
        
        // 创建模拟查询对象
        $query = $this->createMockQuery($options);
        
        $tables = $extractor->extractFromQuery($query);
        
        $this->assertContains('user', $tables);
        $this->assertContains('user_profile', $tables);
        $this->assertContains('user_role', $tables);
    }

    /**
     * 测试扩展查询类的缓存方法
     */
    public function testExtendedQueryCache()
    {
        $connection = $this->createMockConnection();
        $query = new ExtendedQuery($connection);
        
        // 设置表名
        $query->table('user');
        
        // 调用缓存方法
        $query->cache(true, 3600);
        
        $options = $query->getOptions();
        
        // 验证缓存选项已设置
        $this->assertArrayHasKey('cache', $options);
        $this->assertTrue(is_array($options['cache']));
        
        // 验证标签包含表名
        $cacheOptions = $options['cache'];
        $this->assertCount(3, $cacheOptions); // [key, expire, tag]
        
        $tags = $cacheOptions[2];
        $this->assertContains('user', $tags);
    }

    /**
     * 测试JOIN查询的标签提取
     */
    public function testJoinQueryTags()
    {
        $connection = $this->createMockConnection();
        $query = new ExtendedQuery($connection);
        
        // 设置主表和JOIN
        $query->table('user')
              ->join('user_profile', 'user.id = user_profile.user_id')
              ->join('user_role', 'user.id = user_role.user_id');
        
        // 调用缓存方法
        $query->cache('user_with_profile_role', 3600);
        
        $options = $query->getOptions();
        $tags = $options['cache'][2];
        
        // 验证所有表名都被提取
        $this->assertContains('user', $tags);
        $this->assertContains('user_profile', $tags);
        $this->assertContains('user_role', $tags);
    }

    /**
     * 测试用户指定标签与自动标签的合并
     */
    public function testMergeUserAndAutoTags()
    {
        $connection = $this->createMockConnection();
        $query = new ExtendedQuery($connection);
        
        $query->table('user');
        
        // 用户指定标签
        $query->cache('user_cache', 3600, ['custom_tag', 'another_tag']);
        
        $options = $query->getOptions();
        $tags = $options['cache'][2];
        
        // 验证包含用户标签和自动标签
        $this->assertContains('custom_tag', $tags);
        $this->assertContains('another_tag', $tags);
        $this->assertContains('user', $tags);
    }

    /**
     * 创建模拟连接对象
     */
    protected function createMockConnection()
    {
        $connection = $this->createMock(\think\db\ConnectionInterface::class);
        
        $connection->method('getCache')
                   ->willReturn($this->createMockCache());
        
        return $connection;
    }

    /**
     * 创建模拟缓存对象
     */
    protected function createMockCache()
    {
        return $this->createMock(\think\cache\Driver::class);
    }

    /**
     * 创建模拟查询对象
     */
    protected function createMockQuery(array $options)
    {
        $query = $this->createMock(\think\db\BaseQuery::class);
        
        $query->method('getOptions')
              ->willReturn($options);
        
        $query->method('getTable')
              ->willReturn($options['table'] ?? '');
        
        return $query;
    }
}

/**
 * 测试用的扩展模型
 */
class TestUserModel extends ExtendedModel
{
    protected $table = 'user';
    protected $connection = null; // 使用默认连接
}

/**
 * 集成测试
 */
class AutoTagCacheIntegrationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * 测试模型查询缓存
     */
    public function testModelQueryCache()
    {
        // 这里需要实际的数据库连接来测试
        $this->markTestSkipped('需要实际数据库连接');
        
        $user = new TestUserModel();
        $query = $user->db();
        
        $this->assertInstanceOf(ExtendedQuery::class, $query);
        
        // 测试缓存方法
        $query->cache(true, 3600);
        $options = $query->getOptions();
        
        $this->assertArrayHasKey('cache', $options);
    }

    /**
     * 测试缓存清理
     */
    public function testCacheClear()
    {
        $this->markTestSkipped('需要实际缓存驱动');
        
        // 这里测试实际的缓存清理功能
    }
}
