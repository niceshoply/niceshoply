<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Backup\Services;

use Exception;
use Symfony\Component\Process\Process;

/**
 * 数据库备份服务（mysqldump + gzip）。
 */
class BackupService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function backupDir(): string
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * 执行一次数据库备份，返回生成的文件名。
     *
     * @throws Exception
     */
    public function run(): string
    {
        $conn = config('database.default');
        $db   = config("database.connections.{$conn}");
        if (($db['driver'] ?? '') !== 'mysql') {
            throw new Exception('Only MySQL backup is supported.');
        }

        $dumpBin = (string) plugin_setting('backup', 'mysqldump_path', '');
        $dumpBin = $dumpBin !== '' ? $dumpBin : 'mysqldump';

        $filename = 'backup_'.($db['database'] ?? 'db').'_'.date('Ymd_His').'.sql.gz';
        $target   = $this->backupDir().'/'.$filename;

        $args = [
            $dumpBin,
            '--host='.($db['host'] ?? '127.0.0.1'),
            '--port='.($db['port'] ?? '3306'),
            '--user='.($db['username'] ?? 'root'),
            '--single-transaction',
            '--quick',
            '--default-character-set='.($db['charset'] ?? 'utf8mb4'),
            $db['database'] ?? '',
        ];

        $env = ['MYSQL_PWD' => (string) ($db['password'] ?? '')];

        // mysqldump 输出经 gzip 压缩写入文件
        $process = Process::fromShellCommandline(
            implode(' ', array_map('escapeshellarg', $args)).' | gzip > '.escapeshellarg($target),
            base_path(),
            $env,
            null,
            600
        );

        $process->run();

        if (! $process->isSuccessful() || ! is_file($target) || filesize($target) === 0) {
            @unlink($target);
            throw new Exception('mysqldump failed: '.trim($process->getErrorOutput() ?: 'unknown error'));
        }

        $this->cleanup();

        return $filename;
    }

    /**
     * 列出备份文件（按时间倒序）。
     */
    public function list(): array
    {
        $files = glob($this->backupDir().'/*.sql.gz') ?: [];
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map(fn ($f) => [
            'name' => basename($f),
            'size' => filesize($f),
            'time' => date('Y-m-d H:i:s', filemtime($f)),
        ], $files);
    }

    public function pathFor(string $name): ?string
    {
        // 防目录穿越
        $name = basename($name);
        $path = $this->backupDir().'/'.$name;

        return is_file($path) ? $path : null;
    }

    public function delete(string $name): bool
    {
        $path = $this->pathFor($name);
        if ($path) {
            return @unlink($path);
        }

        return false;
    }

    /**
     * 按保留份数清理旧备份。
     */
    public function cleanup(): void
    {
        $keep = (int) plugin_setting('backup', 'keep_count', 10);
        if ($keep < 1) {
            return;
        }

        $files = glob($this->backupDir().'/*.sql.gz') ?: [];
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        foreach (array_slice($files, $keep) as $old) {
            @unlink($old);
        }
    }
}
