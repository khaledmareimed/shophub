<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\InventoryRepository;

class NullInventoryRepository extends InventoryRepository
{
    public function __construct()
    {
    }
}
