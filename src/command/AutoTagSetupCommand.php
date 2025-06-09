<?php

namespace yangweijie\cache\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 自动表名标签缓存设置命令
 */
class AutoTagSetupCommand extends Command
{
    protected function configure()
    {
        $this->setName('cache-plus:auto-tag-setup')
             ->setDescription('设置自动表名标签缓存功能');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('<info>开始设置自动表名标签缓存功能...</info>');

        // 1. 检查缓存驱动
        $this->checkCacheDriver($output);

        // 2. 检查服务注册
        $this->checkServiceRegistration($output);

        // 3. 提供使用指导
        $this->showUsageGuide($output);

        $output->writeln('<info>设置完成！</info>');
        
        return 0;
    }

    /**
     * 检查缓存驱动
     */
    protected function checkCacheDriver(Output $output)
    {
        $output->writeln('<comment>检查缓存驱动配置...</comment>');

        try {
            $cache = \think\facade\Cache::store();
            
            if (method_exists($cache, 'tag')) {
                $output->writeln('<info>✓ 缓存驱动支持标签功能</info>');
            } else {
                $output->writeln('<error>✗ 当前缓存驱动不支持标签功能</error>');
                $output->writeln('<comment>建议配置 Redis 缓存驱动:</comment>');
                $output->writeln("'default' => 'redis',");
                $output->writeln("'stores' => [");
                $output->writeln("    'redis' => [");
                $output->writeln("        'type' => 'redis',");
                $output->writeln("        'host' => '127.0.0.1',");
                $output->writeln("        'port' => 6379,");
                $output->writeln("        // 其他配置...");
                $output->writeln("    ],");
                $output->writeln("],");
            }
        } catch (\Exception $e) {
            $output->writeln('<error>✗ 缓存驱动检查失败: ' . $e->getMessage() . '</error>');
        }

        $output->writeln('');
    }

    /**
     * 检查服务注册
     */
    protected function checkServiceRegistration(Output $output)
    {
        $output->writeln('<comment>检查服务注册...</comment>');

        // 检查主服务是否注册
        if (class_exists('yangweijie\cache\Service')) {
            $output->writeln('<info>✓ ThinkCache Plus 服务已注册</info>');
        } else {
            $output->writeln('<error>✗ ThinkCache Plus 服务未注册</error>');
            $output->writeln('<comment>请在 config/app.php 中添加:</comment>');
            $output->writeln("'providers' => [");
            $output->writeln("    \\yangweijie\\cache\\Service::class,");
            $output->writeln("],");
        }

        // 检查扩展类是否可用
        if (class_exists('yangweijie\cache\db\ExtendedQuery')) {
            $output->writeln('<info>✓ 扩展查询类可用</info>');
        } else {
            $output->writeln('<error>✗ 扩展查询类不可用</error>');
        }

        if (class_exists('yangweijie\cache\model\ExtendedModel')) {
            $output->writeln('<info>✓ 扩展模型类可用</info>');
        } else {
            $output->writeln('<error>✗ 扩展模型类不可用</error>');
        }

        $output->writeln('');
    }

    /**
     * 显示使用指导
     */
    protected function showUsageGuide(Output $output)
    {
        $output->writeln('<comment>使用指导:</comment>');
        $output->writeln('');

        $output->writeln('<info>1. 创建扩展模型:</info>');
        $output->writeln('<?php');
        $output->writeln('namespace app\\model;');
        $output->writeln('use yangweijie\\cache\\model\\ExtendedModel;');
        $output->writeln('');
        $output->writeln('class User extends ExtendedModel');
        $output->writeln('{');
        $output->writeln('    protected $table = \'user\';');
        $output->writeln('}');
        $output->writeln('');

        $output->writeln('<info>2. 使用缓存查询:</info>');
        $output->writeln('// 单表查询 - 自动添加 \'user\' 标签');
        $output->writeln('$users = User::where(\'status\', 1)->cache(true, 3600)->select();');
        $output->writeln('');
        $output->writeln('// JOIN 查询 - 自动添加多个表标签');
        $output->writeln('$result = User::alias(\'u\')');
        $output->writeln('    ->join(\'user_profile p\', \'u.id = p.user_id\')');
        $output->writeln('    ->cache(\'user_with_profile\', 3600)');
        $output->writeln('    ->select();');
        $output->writeln('');

        $output->writeln('<info>3. 自动缓存清理:</info>');
        $output->writeln('$user = User::find(1);');
        $output->writeln('$user->name = \'New Name\';');
        $output->writeln('$user->save(); // 自动清理 \'user\' 标签的所有缓存');
        $output->writeln('');

        $output->writeln('<info>4. 手动清理标签缓存:</info>');
        $output->writeln('use think\\facade\\Cache;');
        $output->writeln('Cache::tag(\'user\')->clear(); // 清理单个表');
        $output->writeln('Cache::tag([\'user\', \'order\'])->clear(); // 清理多个表');
        $output->writeln('');

        $output->writeln('<info>5. 查看缓存日志:</info>');
        $output->writeln('访问管理界面: http://your-domain/cache-plus/');
        $output->writeln('');

        $output->writeln('<comment>更多信息请查看文档: docs/AUTO_TAG_CACHE.md</comment>');
    }
}
