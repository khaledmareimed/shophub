<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use PDOException;
use RuntimeException;

final class MigrationRunner
{
    /** @param array{host:string,port:int,database:string,username:string,password:string,charset:string} $cfg */
    public function __construct(
        private readonly string $migrationsDir,
        private readonly array $cfg,
    ) {
    }

    public function testConnection(): ?string
    {
        try {
            $this->connect(includeDatabase: true);
            return null;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function ensureDatabase(): bool
    {
        try {
            $pdo = $this->connect(includeDatabase: false);
            $db = $this->cfg['database'];
            $charset = $this->cfg['charset'];
            $pdo->exec(
                "CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci"
            );
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    /** @return list<array{filename:string,status:string,message:string}> */
    public function run(): array
    {
        Database::init($this->cfg);
        $pdo = Database::pdo();
        $results = [];

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL UNIQUE,
                ran_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $files = glob($this->migrationsDir . '/*.sql') ?: [];
        sort($files);

        foreach ($files as $file) {
            $name = basename($file);
            $check = $pdo->prepare('SELECT 1 FROM migrations WHERE filename = ?');
            $check->execute([$name]);
            if ($check->fetch()) {
                $results[] = ['filename' => $name, 'status' => 'skip', 'message' => 'Already applied'];
                continue;
            }

            $sql = file_get_contents($file);
            if ($sql === false) {
                throw new RuntimeException("Cannot read migration file: {$name}");
            }

            try {
                $pdo->exec($sql);
                $ins = $pdo->prepare('INSERT INTO migrations (filename) VALUES (?)');
                $ins->execute([$name]);
                $results[] = ['filename' => $name, 'status' => 'ok', 'message' => 'Applied successfully'];
            } catch (PDOException $e) {
                $results[] = ['filename' => $name, 'status' => 'error', 'message' => $e->getMessage()];
                break;
            }
        }

        return $results;
    }

    public function pendingCount(): int
    {
        if ($this->testConnection() !== null) {
            return -1;
        }

        Database::init($this->cfg);
        $pdo = Database::pdo();

        if (!$this->tableExists($pdo, 'migrations')) {
            return count(glob($this->migrationsDir . '/*.sql') ?: []);
        }

        $files = glob($this->migrationsDir . '/*.sql') ?: [];
        $applied = (int) $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn();

        return max(0, count($files) - $applied);
    }

    private function connect(bool $includeDatabase): PDO
    {
        $dsn = $includeDatabase
            ? sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->cfg['host'],
                $this->cfg['port'],
                $this->cfg['database'],
                $this->cfg['charset'],
            )
            : sprintf(
                'mysql:host=%s;port=%d;charset=%s',
                $this->cfg['host'],
                $this->cfg['port'],
                $this->cfg['charset'],
            );

        return new PDO($dsn, $this->cfg['username'], $this->cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private function tableExists(PDO $pdo, string $table): bool
    {
        $st = $pdo->prepare(
            'SELECT 1 FROM information_schema.tables
             WHERE table_schema = ? AND table_name = ? LIMIT 1'
        );
        $st->execute([$this->cfg['database'], $table]);

        return (bool) $st->fetchColumn();
    }
}
