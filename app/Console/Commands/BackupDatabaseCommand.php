<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup {--path=}';

    protected $description = 'Create a SQL backup of the current database';

    public function handle(): int
    {
        $connection = Config::get('database.default');
        $config = Config::get("database.connections.{$connection}");

        if (!$config || $config['driver'] !== 'mysql') {
            $this->error('Database backup is only supported for MySQL connections.');
            return 1;
        }

        $database = $config['database'];
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        $backupPath = $this->option('path') ?: base_path('database_backup.sql');
        $directory = dirname($backupPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $command = ['mysqldump', '--user=' . $username, '--password=' . $password, '--host=' . $host, '--port=' . $port, '--default-character-set=utf8mb4', '--no-data', $database];

        $process = new Process($command, base_path());
        $process->setTimeout(0);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error($process->getErrorOutput() ?: $process->getOutput());
            return 1;
        }

        file_put_contents($backupPath, $process->getOutput());

        $this->info("Database backup created at {$backupPath}");

        return 0;
    }
}
