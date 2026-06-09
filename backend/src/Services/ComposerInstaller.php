<?php

declare(strict_types=1);

namespace App\Services;

final class ComposerInstaller
{
    public function __construct(
        private readonly string $backendRoot,
    ) {
    }

    public function isInstalled(): bool
    {
        return is_file($this->backendRoot . '/vendor/autoload.php');
    }

    /** @return array{ok:bool,message:string,output:string} */
    public function install(): array
    {
        if (!is_file($this->backendRoot . '/composer.json')) {
            return [
                'ok' => false,
                'message' => 'composer.json not found in backend folder.',
                'output' => '',
            ];
        }

        if ($this->isInstalled()) {
            return [
                'ok' => true,
                'message' => 'Vendor folder already exists. Skipped.',
                'output' => '',
            ];
        }

        $command = $this->resolveInstallCommand();
        if ($command === null) {
            return [
                'ok' => false,
                'message' => 'Composer not found. Install it globally or allow the setup page to download composer.phar.',
                'output' => '',
            ];
        }

        [$output, $exitCode] = $this->runCommand($command);

        if ($exitCode !== 0) {
            return [
                'ok' => false,
                'message' => 'composer install failed (exit code ' . $exitCode . ').',
                'output' => $output,
            ];
        }

        if (!$this->isInstalled()) {
            return [
                'ok' => false,
                'message' => 'Composer finished but vendor/autoload.php is still missing.',
                'output' => $output,
            ];
        }

        return [
            'ok' => true,
            'message' => 'PHP dependencies installed successfully.',
            'output' => $output,
        ];
    }

    private function resolveInstallCommand(): ?string
    {
        if ($this->commandWorks('composer --version')) {
            return 'composer install --no-interaction --no-ansi --working-dir=' . escapeshellarg($this->backendRoot);
        }

        $phar = $this->backendRoot . '/composer.phar';
        if (is_file($phar) && $this->commandWorks('php ' . escapeshellarg($phar) . ' --version')) {
            return 'php ' . escapeshellarg($phar) . ' install --no-interaction --no-ansi --working-dir=' . escapeshellarg($this->backendRoot);
        }

        if (!$this->downloadComposerPhar($phar)) {
            return null;
        }

        if ($this->commandWorks('php ' . escapeshellarg($phar) . ' --version')) {
            return 'php ' . escapeshellarg($phar) . ' install --no-interaction --no-ansi --working-dir=' . escapeshellarg($this->backendRoot);
        }

        return null;
    }

    private function downloadComposerPhar(string $target): bool
    {
        if (is_file($target)) {
            return true;
        }

        $installer = $this->backendRoot . '/composer-setup.php';
        if (!@copy('https://getcomposer.org/installer', $installer)) {
            return false;
        }

        $dir = escapeshellarg($this->backendRoot);
        exec("php {$installer} --install-dir={$dir} --filename=composer.phar 2>&1", $out, $code);
        @unlink($installer);

        return $code === 0 && is_file($target);
    }

    private function commandWorks(string $command): bool
    {
        exec($command . ' 2>&1', $out, $code);

        return $code === 0;
    }

    /** @return array{0:string,1:int} */
    private function runCommand(string $command): array
    {
        $output = [];
        $exitCode = 1;

        if (function_exists('proc_open')) {
            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $process = proc_open($command, $descriptors, $pipes, $this->backendRoot);
            if (is_resource($process)) {
                fclose($pipes[0]);
                $stdout = stream_get_contents($pipes[1]) ?: '';
                $stderr = stream_get_contents($pipes[2]) ?: '';
                fclose($pipes[1]);
                fclose($pipes[2]);
                $exitCode = proc_close($process);
                $output[] = trim($stdout . ($stderr !== '' ? "\n" . $stderr : ''));
            }
        } else {
            exec($command . ' 2>&1', $lines, $exitCode);
            $output[] = trim(implode("\n", $lines));
        }

        return [implode("\n", array_filter($output)), $exitCode];
    }
}
