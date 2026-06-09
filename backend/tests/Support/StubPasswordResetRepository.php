<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\PasswordResetRepository;

class StubPasswordResetRepository extends PasswordResetRepository
{
    public function __construct()
    {
    }

    public function create(int $userId, string $hash, string $expiresAt): void
    {
    }

    /** @return array<string, mixed>|null */
    public function findValid(string $hash): ?array
    {
        return null;
    }

    public function markUsed(int $id): void
    {
    }
}
