<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs the site self-deploy (git pull + migrations + cache clear) that replaces the
 * temporary manual public/_deploy_fix.php script. All shell commands are fixed strings;
 * no user input is ever interpolated into a command.
 */
class DeployService
{
    private const ADDONS_CONFIG_RELATIVE = 'config/system-addons.php';
    private const REMOTE = 'origin';
    private const BRANCH = 'main';
    private const MAX_LISTED_COMMITS = 20;

    /**
     * Read-only repository status: current branch, local short commit and how many
     * commits behind origin/main the working tree is (after a fetch).
     *
     * @return array{is_git_repo:bool, branch:?string, local_commit:?string, behind_count:int, behind_commits:array<int,string>, fetch_ok:bool, message:?string}
     */
    public function getStatus(): array
    {
        $inRepo = $this->runGit(command: 'git rev-parse --is-inside-work-tree');
        if (!$inRepo['success']) {
            return [
                'is_git_repo' => false,
                'branch' => null,
                'local_commit' => null,
                'behind_count' => 0,
                'behind_commits' => [],
                'fetch_ok' => false,
                'message' => $this->firstLine(output: $inRepo['output']),
            ];
        }

        $branch = $this->firstLine(output: $this->runGit(command: 'git rev-parse --abbrev-ref HEAD')['output']);
        $localCommit = $this->firstLine(output: $this->runGit(command: 'git rev-parse --short HEAD')['output']);

        $fetch = $this->runGit(command: 'git fetch ' . self::REMOTE);
        $behindCount = 0;
        $behindCommits = [];
        if ($fetch['success']) {
            $countResult = $this->runGit(command: 'git rev-list --count HEAD..' . self::REMOTE . '/' . self::BRANCH);
            $behindCount = (int)$this->firstLine(output: $countResult['output']);
            if ($behindCount > 0) {
                $logResult = $this->runGit(command: 'git log --oneline --no-decorate HEAD..' . self::REMOTE . '/' . self::BRANCH);
                $behindCommits = array_slice($logResult['output'], 0, self::MAX_LISTED_COMMITS);
            }
        }

        return [
            'is_git_repo' => true,
            'branch' => $branch,
            'local_commit' => $localCommit,
            'behind_count' => $behindCount,
            'behind_commits' => $behindCommits,
            'fetch_ok' => $fetch['success'],
            'message' => $fetch['success'] ? null : $this->firstLine(output: $fetch['output']),
        ];
    }

    /**
     * Destructive deploy: fetch + hard reset to origin/main + artisan maintenance.
     * Mirrors the manual _deploy_fix.php script. Each execution is logged.
     *
     * @return array{success:bool, steps:array<int,array{name:string, command:?string, output:array<int,string>, exit_code:int, success:bool}>}
     */
    public function deploy(int $adminId, ?string $adminName): array
    {
        $steps = [];
        $success = true;

        // 1. Backup license activation state before touching the working tree.
        $addonsPath = base_path(self::ADDONS_CONFIG_RELATIVE);
        $backupPath = null;
        if (is_file($addonsPath)) {
            $backupPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'system-addons.backup.' . time() . '.php';
            @copy($addonsPath, $backupPath);
        }

        // 2. git fetch origin
        $fetch = $this->runGit(command: 'git fetch ' . self::REMOTE);
        $steps[] = $this->shellStep(name: 'git fetch ' . self::REMOTE, result: $fetch);

        // 3. git reset --hard origin/main (only if the fetch succeeded).
        $gitSuccess = false;
        if ($fetch['success']) {
            $reset = $this->runGit(command: 'git reset --hard ' . self::REMOTE . '/' . self::BRANCH);
            $steps[] = $this->shellStep(name: 'git reset --hard ' . self::REMOTE . '/' . self::BRANCH, result: $reset);
            $gitSuccess = $reset['success'];
        }
        if (!$gitSuccess) {
            $success = false;
        }

        // 4. Restore license state if the reset removed it.
        if ($backupPath && is_file($backupPath)) {
            if (!is_file($addonsPath)) {
                @copy($backupPath, $addonsPath);
                $steps[] = $this->noteStep(
                    name: 'restore ' . self::ADDONS_CONFIG_RELATIVE,
                    line: self::ADDONS_CONFIG_RELATIVE . ' restored from backup',
                );
            }
            @unlink($backupPath);
        }

        // 5. Artisan maintenance — only when the tree updated cleanly.
        if ($gitSuccess) {
            foreach ([
                ['command' => 'migrate', 'params' => ['--force' => true]],
                ['command' => 'package:discover', 'params' => []],
                ['command' => 'optimize:clear', 'params' => []],
            ] as $artisan) {
                $step = $this->runArtisan(command: $artisan['command'], params: $artisan['params']);
                $steps[] = $step;
                if (!$step['success']) {
                    $success = false;
                }
            }

            if (function_exists('opcache_reset')) {
                @opcache_reset();
                $steps[] = $this->noteStep(name: 'opcache_reset()', line: 'OPcache reset');
            }
        }

        $result = ['success' => $success, 'steps' => $steps];
        $this->log(adminId: $adminId, adminName: $adminName, result: $result);

        return $result;
    }

    /**
     * @return array{output:array<int,string>, exit_code:int, success:bool}
     */
    private function runGit(string $command): array
    {
        if (!function_exists('exec')) {
            return ['output' => ['exec() is disabled on this server'], 'exit_code' => 127, 'success' => false];
        }

        $previousDir = getcwd();
        chdir(base_path());

        $output = [];
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);

        if ($previousDir !== false) {
            chdir($previousDir);
        }

        return [
            'output' => $this->sanitize(lines: $output),
            'exit_code' => $exitCode,
            'success' => $exitCode === 0,
        ];
    }

    /**
     * @return array{name:string, command:string, output:array<int,string>, exit_code:int, success:bool}
     */
    private function runArtisan(string $command, array $params): array
    {
        $label = 'php artisan ' . $command;
        try {
            Artisan::call($command, $params);
            return [
                'name' => $label,
                'command' => $label,
                'output' => $this->sanitize(lines: $this->splitLines(text: Artisan::output())),
                'exit_code' => 0,
                'success' => true,
            ];
        } catch (Throwable $exception) {
            return [
                'name' => $label,
                'command' => $label,
                'output' => $this->sanitize(lines: [$exception->getMessage()]),
                'exit_code' => 1,
                'success' => false,
            ];
        }
    }

    /**
     * @param array{output:array<int,string>, exit_code:int, success:bool} $result
     * @return array{name:string, command:string, output:array<int,string>, exit_code:int, success:bool}
     */
    private function shellStep(string $name, array $result): array
    {
        return [
            'name' => $name,
            'command' => $name,
            'output' => $result['output'],
            'exit_code' => $result['exit_code'],
            'success' => $result['success'],
        ];
    }

    /**
     * @return array{name:string, command:?string, output:array<int,string>, exit_code:int, success:bool}
     */
    private function noteStep(string $name, string $line): array
    {
        return ['name' => $name, 'command' => null, 'output' => [$line], 'exit_code' => 0, 'success' => true];
    }

    /**
     * Redact any .env secret value that could surface in command output.
     *
     * @param array<int,string> $lines
     * @return array<int,string>
     */
    private function sanitize(array $lines): array
    {
        $secrets = array_filter([
            env('APP_KEY'),
            env('DB_PASSWORD'),
            env('DB_USERNAME'),
            env('MAIL_PASSWORD'),
            env('REDIS_PASSWORD'),
            env('PUSHER_APP_SECRET'),
            env('AWS_SECRET_ACCESS_KEY'),
        ], fn($value) => is_string($value) && $value !== '');

        $lines = array_values($lines);
        if (empty($secrets)) {
            return $lines;
        }

        return array_map(fn($line) => str_replace($secrets, '***REDACTED***', (string)$line), $lines);
    }

    /**
     * @return array<int,string>
     */
    private function splitLines(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }
        return preg_split('/\r\n|\r|\n/', $text) ?: [];
    }

    /**
     * @param array<int,string> $output
     */
    private function firstLine(array $output): string
    {
        return isset($output[0]) ? trim((string)$output[0]) : '';
    }

    /**
     * @param array{success:bool, steps:array<int,array<string,mixed>>} $result
     */
    private function log(int $adminId, ?string $adminName, array $result): void
    {
        $logger = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/deploy.log'),
        ]);

        $lines = [];
        foreach ($result['steps'] as $step) {
            $lines[] = '[' . ($step['success'] ? 'OK' : 'ERR') . '] ' . $step['name'] . ' (exit ' . $step['exit_code'] . ')';
            foreach ($step['output'] as $outputLine) {
                $lines[] = '    ' . $outputLine;
            }
        }

        $header = 'Deploy ' . ($result['success'] ? 'SUCCESS' : 'FAILED')
            . ' by admin #' . $adminId . ' (' . ($adminName ?? 'unknown') . ')';

        $logger->info($header . PHP_EOL . implode(PHP_EOL, $lines));
    }
}
