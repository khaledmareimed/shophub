<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\ProductRepository;

class StubProductRepository extends ProductRepository
{
    /** @param array<int, array<string, mixed>> $byId */
    public function __construct(private array $byId = [])
    {
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        return $this->byId[$id] ?? null;
    }
}
