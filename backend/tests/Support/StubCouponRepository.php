<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\CouponRepository;

class StubCouponRepository extends CouponRepository
{
    /** @param array<string, mixed>|null $row */
    public function __construct(private ?array $row)
    {
    }

    /** @return array<string, mixed>|null */
    public function findByCode(string $code): ?array
    {
        return $this->row;
    }
}
