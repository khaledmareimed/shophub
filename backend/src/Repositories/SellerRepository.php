<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class SellerRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function createProfile(int $userId, string $businessName, string $slug): void
    {
        $st = $this->pdo->prepare(
            'INSERT INTO seller_profiles (user_id, business_name, slug, status) VALUES (?,?,?,"pending")'
        );
        $st->execute([$userId, $businessName, $slug]);
    }

    /** @return array<string, mixed>|null */
    public function findByUserId(int $userId): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM seller_profiles WHERE user_id = ?');
        $st->execute([$userId]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public function updateProfileDetails(int $userId, string $businessName, ?string $description): void
    {
        $st = $this->pdo->prepare(
            'UPDATE seller_profiles SET business_name = ?, description = ? WHERE user_id = ?'
        );
        $st->execute([$businessName, $description, $userId]);
    }

    public function updateStatus(int $userId, string $status, ?int $approvedBy): void
    {
        if ($status === 'approved') {
            $st = $this->pdo->prepare(
                'UPDATE seller_profiles SET status = ?, approved_at = UTC_TIMESTAMP(), approved_by = ? WHERE user_id = ?'
            );
            $st->execute([$status, $approvedBy, $userId]);
        } else {
            $st = $this->pdo->prepare('UPDATE seller_profiles SET status = ?, approved_by = NULL WHERE user_id = ?');
            $st->execute([$status, $userId]);
        }
    }

    /** @return list<array<string, mixed>> */
    public function listForAdmin(?string $status, int $page, int $per): array
    {
        $off = max(0, ($page - 1) * $per);
        $where = '1=1';
        $params = [];
        if ($status !== null && $status !== 'all') {
            $where .= ' AND sp.status = ?';
            $params[] = $status;
        }
        $sql = "SELECT SQL_CALC_FOUND_ROWS u.id user_id, u.email, u.name, sp.business_name, sp.slug, sp.status
                FROM seller_profiles sp JOIN users u ON u.id = sp.user_id
                WHERE $where ORDER BY sp.user_id DESC LIMIT " . (int) $per . ' OFFSET ' . (int) $off;
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll();
        $total = (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return [$rows, $total];
    }
}
