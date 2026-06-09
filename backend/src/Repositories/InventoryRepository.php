<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class InventoryRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function log(int $productId, int $delta, string $reason, ?string $refType, ?int $refId): void
    {
        $st = $this->pdo->prepare(
            'INSERT INTO inventory_movements (product_id, delta, reason, ref_type, ref_id) VALUES (?,?,?,?,?)'
        );
        $st->execute([$productId, $delta, $reason, $refType, $refId]);
    }
}
