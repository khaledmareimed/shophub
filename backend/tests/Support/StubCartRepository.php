<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\CartRepository;

class StubCartRepository extends CartRepository
{
    /**
     * @param list<array<string, mixed>> $items
     */
    public function __construct(
        private int $cartId = 1,
        private array $items = [],
    ) {
    }

    public function getOrCreateCartId(int $userId): int
    {
        return $this->cartId;
    }

    /** @return list<array<string, mixed>> */
    public function itemsWithProduct(int $cartId): array
    {
        return $this->items;
    }
}
