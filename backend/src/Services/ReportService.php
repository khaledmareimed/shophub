<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class ReportService
{
    /** @return array<string, mixed> */
    public function adminSummary(): array
    {
        $pdo = \App\Core\Database::pdo();
        $o = $pdo->query(
            'SELECT status, COUNT(*) c FROM orders GROUP BY status'
        )->fetchAll(PDO::FETCH_KEY_PAIR);
        $rev = (string) ($pdo->query(
            'SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE status IN ("completed","processing","pending")'
        )->fetchColumn());
        return [
            'orders_by_status' => $o,
            'revenue_all_open' => $rev,
        ];
    }

    /** @return list<array{date: string, total: string}> */
    public function salesLastDays(int $days, ?int $sellerId = null): array
    {
        $pdo = \App\Core\Database::pdo();
        if ($sellerId === null) {
            $st = $pdo->prepare(
                'SELECT DATE(placed_at) d, COALESCE(SUM(grand_total),0) t FROM orders
                 WHERE placed_at >= DATE_SUB(UTC_DATE(), INTERVAL ? DAY) AND status != "cancelled"
                 GROUP BY DATE(placed_at) ORDER BY d ASC'
            );
            $st->execute([$days]);
        } else {
            $st = $pdo->prepare(
                'SELECT DATE(o.placed_at) d, COALESCE(SUM(oi.line_total),0) t
                 FROM order_items oi JOIN orders o ON o.id = oi.order_id
                 WHERE oi.seller_id = ? AND o.placed_at >= DATE_SUB(UTC_DATE(), INTERVAL ? DAY) AND o.status != "cancelled"
                 GROUP BY DATE(o.placed_at) ORDER BY d ASC'
            );
            $st->execute([$sellerId, $days]);
        }
        $out = [];
        foreach ($st->fetchAll() as $row) {
            $out[] = ['date' => (string) $row['d'], 'total' => (string) $row['t']];
        }
        return $out;
    }

    /** @return list<array{name: string, qty: int}> */
    public function topProducts(int $limit, ?int $sellerId = null): array
    {
        $pdo = \App\Core\Database::pdo();
        if ($sellerId === null) {
            $st = $pdo->prepare(
                'SELECT name_snapshot name, SUM(qty) q FROM order_items GROUP BY product_id, name_snapshot ORDER BY q DESC LIMIT ' . (int) $limit
            );
            $st->execute();
        } else {
            $st = $pdo->prepare(
                'SELECT name_snapshot name, SUM(qty) q FROM order_items WHERE seller_id = ? GROUP BY product_id, name_snapshot ORDER BY q DESC LIMIT ' . (int) $limit
            );
            $st->execute([$sellerId]);
        }
        $out = [];
        foreach ($st->fetchAll() as $row) {
            $out[] = ['name' => (string) $row['name'], 'qty' => (int) $row['q']];
        }
        return $out;
    }
}
