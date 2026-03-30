<?php

namespace App\Http\Controllers\Admin\Server;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServerController extends Controller
{
    private const MAX_BACKUPS = 5;

    public static function middleware(): array
    {
        return [
            new Middleware('role:' . Role::ROLE_ROOT),
        ];
    }

    public function index(): View
    {
        $backups = $this->getBackups();

        return view('admin.server.index', compact('backups'));
    }

    public function stepBackup(): JsonResponse
    {
        try {
            $backupFile = $this->createBackup();
            $this->rotateBackups();

            return response()->json([
                'success' => true,
                'output'  => 'Backup saved: ' . basename($backupFile),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'output'  => $e->getMessage(),
            ]);
        }
    }

    public function stepGit(): JsonResponse
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return response()->json([
                'success' => true,
                'output'  => 'Skipped (not a production server).',
            ]);
        }

        try {
            $output = [];
            exec('git -C ' . escapeshellarg(base_path()) . ' pull origin master 2>&1', $output, $exitCode);

            return response()->json([
                'success' => $exitCode === 0,
                'output'  => implode("\n", $output),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'output'  => $e->getMessage(),
            ]);
        }
    }

    public function stepMigrate(): JsonResponse
    {
        try {
            Artisan::call('migrate', ['--force' => true, '--no-ansi' => true]);

            $output = trim(Artisan::output());
            $count = preg_match_all('/DONE/', $output);

            return response()->json([
                'success' => true,
                'summary' => $count > 0 ? "Выполнено миграций: {$count}" : 'Нет новых миграций',
                'log'     => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'summary' => $e->getMessage(),
                'log'     => $e->getMessage(),
            ]);
        }
    }

    public function stepSeed(): JsonResponse
    {
        try {
            Artisan::call('db:seed', ['--force' => true, '--no-ansi' => true]);

            $output = trim(Artisan::output());
            $count = preg_match_all('/DONE/', $output);

            return response()->json([
                'success' => true,
                'summary' => "Выполнено сидеров: {$count}",
                'log'     => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'summary' => $e->getMessage(),
                'log'     => $e->getMessage(),
            ]);
        }
    }

    public function stepCache(): JsonResponse
    {
        try {
            Artisan::call('config:cache', ['--no-ansi' => true]);
            Artisan::call('route:cache', ['--no-ansi' => true]);
            Artisan::call('view:cache', ['--no-ansi' => true]);

            return response()->json([
                'success' => true,
                'output'  => 'Cache cleared and rebuilt.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'output'  => $e->getMessage(),
            ]);
        }
    }

    public function downloadBackup(string $filename): BinaryFileResponse
    {
        if (!preg_match('/^backup_\d{8}_\d{6}$/', $filename)) {
            abort(404);
        }

        $filepath = storage_path('backups/' . $filename . '.sql');

        if (!file_exists($filepath)) {
            abort(404);
        }

        return response()->download($filepath);
    }

    private function createBackup(): string
    {
        $dir = storage_path('backups');
        File::ensureDirectoryExists($dir);

        $filename = 'backup_' . date('Ymd_His') . '.sql';
        $filepath = $dir . DIRECTORY_SEPARATOR . $filename;

        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $command = sprintf(
            'mysqldump --default-character-set=utf8mb4 --no-tablespaces -h %s -P %s -u %s -p%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        exec($command . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            @unlink($filepath);
            throw new \RuntimeException(implode("\n", $output));
        }

        return $filepath;
    }

    private function rotateBackups(): void
    {
        $dir = storage_path('backups');
        $files = File::glob($dir . '/backup_*.sql');

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        foreach (array_slice($files, self::MAX_BACKUPS) as $file) {
            @unlink($file);
        }
    }

    private function getBackups(): array
    {
        $path = storage_path('backups');

        if (!File::isDirectory($path)) {
            return [];
        }

        $files = File::glob($path . '/backup_*.sql');

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        return array_map(function ($file) {
            return [
                'name' => basename($file),
                'size' => $this->formatSize(filesize($file)),
                'date' => date('d.m.Y H:i:s', filemtime($file)),
            ];
        }, $files);
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
