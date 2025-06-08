<?php

namespace yangweijie\cache\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * ThinkCache Plus 安装命令
 * 用于自动复制配置文件和资源文件
 */
class InstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('cache-plus:install')
            ->setDescription('Install ThinkCache Plus package')
            ->addOption('force', 'f', \think\console\input\Option::VALUE_NONE, 'Force overwrite existing files');
    }

    protected function execute(Input $input, Output $output)
    {
        $force = $input->getOption('force');
        
        $output->writeln('<info>Installing ThinkCache Plus...</info>');
        if ($force) {
            $output->writeln('<comment>Force mode enabled - existing files will be overwritten</comment>');
        }

        // 复制配置文件
        $this->copyConfigFile($output, $force);
        
        // 复制数据库迁移文件
        $this->copyMigrationFiles($output, $force);
        
        // 复制视图文件
        $this->copyViewFiles($output, $force);
        
        // 复制静态资源
        $this->copyAssetFiles($output, $force);

        $output->writeln('<info>ThinkCache Plus installed successfully!</info>');
        $output->writeln('<comment>Please run database migrations if needed.</comment>');
    }

    /**
     * 复制配置文件
     */
    protected function copyConfigFile(Output $output, $force = false)
    {
        $source = __DIR__ . '/../../config/cache_plus.php';
        $target = $this->app->getConfigPath() . 'cache_plus.php';

        if (!file_exists($target) || $force) {
            if (copy($source, $target)) {
                $action = file_exists($target) && $force ? 'overwritten' : 'copied';
                $output->writeln("<info>✓ Config file {$action}</info>");
            } else {
                $output->writeln('<error>✗ Failed to copy config file</error>');
            }
        } else {
            $output->writeln('<comment>Config file already exists, skipped (use --force to overwrite)</comment>');
        }
    }

    /**
     * 复制数据库迁移文件
     */
    protected function copyMigrationFiles(Output $output, $force = false)
    {
        $sourceDir = __DIR__ . '/../../database/migrations/';
        $targetDir = $this->app->getRootPath() . 'database/migrations/';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (is_dir($sourceDir)) {
            $files = glob($sourceDir . '*.php');
            foreach ($files as $file) {
                $filename = basename($file);
                $target = $targetDir . $filename;
                
                if (!file_exists($target) || $force) {
                    if (copy($file, $target)) {
                        $action = file_exists($target) && $force ? 'overwritten' : 'copied';
                        $output->writeln("<info>✓ Migration file {$action}: {$filename}</info>");
                    } else {
                        $output->writeln("<error>✗ Failed to copy migration: {$filename}</error>");
                    }
                } else {
                    $output->writeln("<comment>Migration file already exists, skipped: {$filename} (use --force to overwrite)</comment>");
                }
            }
        }
    }

    /**
     * 复制视图文件
     */
    protected function copyViewFiles(Output $output, $force = false)
    {
        $sourceDir = __DIR__ . '/../../resources/views/';
        $targetDir = $this->app->getRootPath() . 'view/cache_plus/';

        if (is_dir($sourceDir)) {
            $this->copyDirectory($sourceDir, $targetDir, $output, 'view', $force);
        }
    }

    /**
     * 复制静态资源
     */
    protected function copyAssetFiles(Output $output, $force = false)
    {
        $sourceDir = __DIR__ . '/../../resources/assets/';
        $targetDir = $this->app->getRootPath() . 'public/static/cache_plus/';

        if (is_dir($sourceDir)) {
            $this->copyDirectory($sourceDir, $targetDir, $output, 'asset', $force);
        }
    }

    /**
     * 递归复制目录 - 使用简单的实现，兼容性更好
     */
    protected function copyDirectory($source, $target, Output $output, $type, $force = false)
    {
        // 确保源目录以分隔符结尾
        $source = rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $target = rtrim($target, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $this->copyDirectoryRecursive($source, $target, $output, $type, '', $force);
    }

    /**
     * 递归复制目录的具体实现
     */
    protected function copyDirectoryRecursive($source, $target, Output $output, $type, $relativePath, $force = false)
    {
        $currentSource = $source . $relativePath;
        $currentTarget = $target . $relativePath;

        if (!is_dir($currentSource)) {
            return;
        }

        if (!is_dir($currentTarget)) {
            mkdir($currentTarget, 0755, true);
        }

        $files = scandir($currentSource);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sourceFile = $currentSource . DIRECTORY_SEPARATOR . $file;
            $targetFile = $currentTarget . DIRECTORY_SEPARATOR . $file;
            $newRelativePath = $relativePath ? $relativePath . DIRECTORY_SEPARATOR . $file : $file;

            if (is_dir($sourceFile)) {
                // 递归处理子目录
                $this->copyDirectoryRecursive($source, $target, $output, $type, $newRelativePath, $force);
            } else {
                // 复制文件
                if (!file_exists($targetFile) || $force) {
                    if (copy($sourceFile, $targetFile)) {
                        $action = file_exists($targetFile) && $force ? 'overwritten' : 'copied';
                        $output->writeln("<info>✓ {$type} file {$action}: {$newRelativePath}</info>");
                    } else {
                        $output->writeln("<error>✗ Failed to copy {$type}: {$newRelativePath}</error>");
                    }
                } else {
                    $output->writeln("<comment>{$type} file already exists, skipped: {$newRelativePath} (use --force to overwrite)</comment>");
                }
            }
        }
    }
}
