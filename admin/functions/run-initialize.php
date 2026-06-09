<?php

declare(strict_types=1);

use App\Core\Database;
use App\Services\ComposerInstaller;
use App\Services\DatabaseSeeder;
use App\Services\MigrationRunner;

/** @param array{host:string,port:int,database:string,username:string,password:string,charset:string} $dbCfg */
function run_initialize_action(string $action, array $dbCfg): array
{
    $projectRoot = dirname(__DIR__, 2);
    $backendRoot = $projectRoot . '/backend';
    $migrationsDir = $backendRoot . '/database/migrations';
    $runner = new MigrationRunner($migrationsDir, $dbCfg);
    $output = ['action' => $action, 'steps' => []];

    if ($action === 'composer' || $action === 'full') {
        $composer = new ComposerInstaller($backendRoot);
        $result = $composer->install();
        $output['steps'][] = [
            'label' => 'Composer install',
            'status' => $result['ok'] ? 'ok' : 'error',
            'message' => $result['message'],
            'output' => $result['output'],
        ];
        if (!$result['ok'] && $action === 'full') {
            return $output;
        }
    }

    if ($action === 'create_db') {
        $created = $runner->ensureDatabase();
        $output['steps'][] = [
            'label' => 'Create database',
            'status' => $created ? 'ok' : 'error',
            'message' => $created
                ? "Database \"{$dbCfg['database']}\" is ready."
                : 'Could not create database. Create it manually in phpMyAdmin.',
        ];
        return $output;
    }

    if ($action === 'migrate' || $action === 'full') {
        $connError = $runner->testConnection();
        if ($connError !== null) {
            $runner->ensureDatabase();
            $connError = $runner->testConnection();
        }
        if ($connError !== null) {
            $output['steps'][] = [
                'label' => 'Database connection',
                'status' => 'error',
                'message' => $connError,
            ];
            return $output;
        }

        $results = $runner->run();
        foreach ($results as $row) {
            $output['steps'][] = [
                'label' => $row['filename'],
                'status' => $row['status'],
                'message' => $row['message'],
            ];
        }
    }

    if ($action === 'seed' || $action === 'full') {
        $hasErrors = array_filter(
            $output['steps'],
            static fn (array $s): bool => ($s['status'] ?? '') === 'error',
        );
        if ($hasErrors !== []) {
            $output['steps'][] = [
                'label' => 'Seed data',
                'status' => 'skip',
                'message' => 'Skipped because a previous step failed.',
            ];
            return $output;
        }

        if ($action === 'seed') {
            $connError = $runner->testConnection();
            if ($connError !== null) {
                $output['steps'][] = [
                    'label' => 'Database connection',
                    'status' => 'error',
                    'message' => $connError,
                ];
                return $output;
            }
            Database::init($dbCfg);
        }

        $seed = (new DatabaseSeeder(
            (string) ($_ENV['AUTH_PEPPER'] ?? $_ENV['JWT_PEPPER'] ?? ''),
        ))->run();

        $output['steps'][] = [
            'label' => 'Seed data',
            'status' => $seed['ok'] ? 'ok' : 'error',
            'message' => $seed['message'],
        ];
        $output['credentials'] = $seed['credentials'];
    }

    return $output;
}
