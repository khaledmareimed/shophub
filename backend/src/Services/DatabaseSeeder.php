<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Hasher;
use PDO;
use Throwable;

final class DatabaseSeeder
{
    public function __construct(
        private readonly string $authPepper = '',
    ) {
    }

    /** @return array{ok:bool,message:string,credentials:array<string,array{email:string,password:string}>} */
    public function run(): array
    {
        $pdo = Database::pdo();

        if (!$this->tableExists($pdo, 'users')) {
            return [
                'ok' => false,
                'message' => 'Users table not found. Run migrations first.',
                'credentials' => [],
            ];
        }

        $adminHash = Hasher::hashPassword('AdminPass123!', $this->authPepper);
        $sellerHash = Hasher::hashPassword('SellerPass123!', $this->authPepper);

        $pdo->beginTransaction();
        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            $pdo->exec("DELETE FROM users WHERE email IN ('admin@shophub.local','seller@shophub.local')");
            $pdo->exec("DELETE FROM categories WHERE slug IN ('electronics','fashion','home')");
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            $st = $pdo->prepare(
                'INSERT INTO users (email, password_hash, name, phone, locale, role, status, email_verified_at)
                 VALUES (?,?,?,?,?,?,?,NOW())'
            );
            $st->execute(['admin@shophub.local', $adminHash, 'Platform Admin', null, 'en', 'admin', 'active']);
            $adminId = (int) $pdo->lastInsertId();

            $st->execute(['seller@shophub.local', $sellerHash, 'Demo Seller', null, 'en', 'seller', 'active']);
            $sellerId = (int) $pdo->lastInsertId();

            $pdo->prepare(
                'INSERT INTO seller_profiles (user_id, business_name, slug, description, status, approved_at, approved_by)
                 VALUES (?,?,?,?,?,?,?)'
            )->execute([
                $sellerId,
                'Demo Shop',
                'demo-shop',
                'Seeded seller',
                'approved',
                gmdate('Y-m-d H:i:s'),
                $adminId,
            ]);

            $cats = [
                ['electronics', 'Electronics', 'إلكترونيات', 1],
                ['fashion', 'Fashion', 'أزياء', 2],
                ['home', 'Home & Living', 'المنزل', 3],
            ];
            $catIds = [];
            $ci = $pdo->prepare(
                'INSERT INTO categories (slug, name, name_ar, position, active) VALUES (?,?,?,?,1)'
            );
            foreach ($cats as $c) {
                $ci->execute([$c[0], $c[1], $c[2], $c[3]]);
                $catIds[$c[0]] = (int) $pdo->lastInsertId();
            }

            $pdo->prepare(
                'INSERT INTO products (seller_id, category_id, slug, name, name_ar, description, price, stock, status)
                 VALUES (?,?,?,?,?,?,?,?,?)'
            )->execute([
                $sellerId,
                $catIds['electronics'],
                'demo-laptop-1',
                'Demo Laptop',
                'لابتوب تجريبي',
                'A seeded product for development.',
                999.99,
                25,
                'active',
            ]);

            $pdo->prepare(
                'INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)'
            )->execute(['site', json_encode(['name' => 'ShopHub', 'default_shipping_fee' => 5.99, 'currency' => 'USD'])]);

            $pdo->prepare(
                'INSERT INTO coupons (code, type, value, active, scope) VALUES (?, ?, ?, 1, ?)'
            )->execute(['WELCOME10', 'percent', 10.00, 'all']);

            $pdo->commit();

            return [
                'ok' => true,
                'message' => 'Demo data seeded successfully.',
                'credentials' => [
                    'admin' => ['email' => 'admin@shophub.local', 'password' => 'AdminPass123!'],
                    'seller' => ['email' => 'seller@shophub.local', 'password' => 'SellerPass123!'],
                ],
            ];
        } catch (Throwable $e) {
            $pdo->rollBack();

            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'credentials' => [],
            ];
        }
    }

    private function tableExists(PDO $pdo, string $table): bool
    {
        $st = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));

        return (bool) $st?->fetchColumn();
    }
}
