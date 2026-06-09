<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\OrderRepository;

class NullOrderRepository extends OrderRepository
{
    public function __construct()
    {
    }
}
