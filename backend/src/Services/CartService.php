<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Decimal;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SettingsRepository;

final class CartService
{
    public function __construct(
        private CartRepository $cart,
        private ProductRepository $products,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function getLines(int $userId): array
    {
        $cid = $this->cart->getOrCreateCartId($userId);
        return $this->cart->itemsWithProduct($cid);
    }

    public function add(int $userId, int $productId, int $qty): void
    {
        $p = $this->products->findById($productId);
        if (!$p || $p['status'] !== 'active' || $p['deleted_at'] !== null) {
            throw new \InvalidArgumentException('product_not_available');
        }
        $cid = $this->cart->getOrCreateCartId($userId);
        $this->cart->upsertItem($cid, $productId, $qty, (string) $p['price']);
    }

    public function updateQty(int $userId, int $cartItemId, int $qty): void
    {
        $cid = $this->cart->getOrCreateCartId($userId);
        $items = $this->cart->itemsWithProduct($cid);
        foreach ($items as $it) {
            if ((int) $it['id'] === $cartItemId) {
                $this->cart->setQty($cartItemId, max(1, $qty));
                return;
            }
        }
        throw new \InvalidArgumentException('not_found');
    }

    public function remove(int $userId, int $cartItemId): void
    {
        $cid = $this->cart->getOrCreateCartId($userId);
        $this->cart->deleteItem($cartItemId, $cid);
    }

    public function clear(int $userId): void
    {
        $cid = $this->cart->getOrCreateCartId($userId);
        $this->cart->clear($cid);
    }

    /**
     * @param list<array{id:int,qty:int,price?:string}> $guest snapshot from client
     */
    public function mergeGuest(int $userId, array $guest): void
    {
        $cid = $this->cart->getOrCreateCartId($userId);
        foreach ($guest as $row) {
            $pid = (int) $row['id'];
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $p = $this->products->findById($pid);
            if (!$p || $p['status'] !== 'active') {
                continue;
            }
            $price = (string) ($row['price'] ?? $p['price']);
            $this->cart->upsertItem($cid, $pid, $qty, $price);
        }
    }

    /** @return array{subtotal: string, lines: list<array<string,mixed>>} */
    public function totals(int $userId): array
    {
        $lines = $this->getLines($userId);
        $sub = '0.00';
        foreach ($lines as $ln) {
            if (($ln['status'] ?? '') !== 'active') {
                continue;
            }
            $sub = Decimal::add(
                $sub,
                Decimal::mul((string) $ln['price_snapshot'], (string) $ln['qty'], 2),
                2,
            );
        }
        return ['subtotal' => $sub, 'lines' => $lines];
    }
}
