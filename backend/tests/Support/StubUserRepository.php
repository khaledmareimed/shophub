<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\UserRepository;

class StubUserRepository extends UserRepository
{
    public function __construct(
        private ?array $emailUser = null,
        private ?array $idUser = null,
    ) {
    }

    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        return $this->emailUser;
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        return $this->idUser;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        return 1;
    }
}
