<?php

namespace yangweijie\cache\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Config;
use yangweijie\cache\model\CacheLog;

/**
 * 清理过期缓存日志命令
 */
class CleanLogsCommand extends Command
{
    protected function configure()
    {
        $this->setName('cache-plus:clean-logs')
            ->setDescription('Clean expired cache logs')
            ->addOption('days', 'd', \think\console\input\Option::VALUE_OPTIONAL, 'Days to keep logs (default from config)')
            ->addOption('force', 'f', \think\console\input\Option::VALUE_NONE, 'Force clean without confirmation');
    }

    protected function execute(Input $input, Output $output)
    {
        $days = $input->getOption('days');
        $force = $input->getOption('force');
        
        // 获取配置
        $config = Config::get('cache_plus', []);
        $retentionDays = $days ? (int)$days : ($config['log_retention_days'] ?? 30);
        
        $output->writeln('<info>ThinkCache Plus - 清理过期日志</info>');
        $output->writeln("保留天数: {$retentionDays} 天");
        
        // 获取将要删除的记录数
        $date = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));
        $willDeleteCount = CacheLog::where('created_at', '<', $date)->count();
        
        if ($willDeleteCount === 0) {
            $output->writeln('<comment>没有需要清理的过期日志</comment>');
            return;
        }
        
        $output->writeln("将要删除 {$willDeleteCount} 条过期日志记录");
        
        // 确认操作
        if (!$force) {
            $confirm = $output->confirm('确认执行清理操作？', false);
            if (!$confirm) {
                $output->writeln('<comment>操作已取消</comment>');
                return;
            }
        }
        
        // 执行清理
        $deletedCount = CacheLog::cleanOldLogs($retentionDays);
        
        if ($deletedCount > 0) {
            $output->writeln("<info>✓ 成功清理 {$deletedCount} 条过期日志</info>");
        } else {
            $output->writeln('<comment>没有记录被删除</comment>');
        }
        
        // 显示当前统计
        $totalLogs = CacheLog::count();
        $output->writeln("当前日志总数: {$totalLogs}");
    }
}
