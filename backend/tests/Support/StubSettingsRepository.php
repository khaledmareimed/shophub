<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\SettingsRepository;

class StubSettingsRepository extends SettingsRepository
{
    /** @param array<string, mixed> $site */
    public function __construct(private array $site)
    {
    }

    /** @return array<string, mixed> */
    public function get(string $key, array $default = []): array
    {
        if ($key === 'site') {
            return $this->site !== [] ? $this->site : $default;
        }
        return $default;
    }
}
