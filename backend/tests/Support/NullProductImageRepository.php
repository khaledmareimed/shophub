<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\ProductImageRepository;

class NullProductImageRepository extends ProductImageRepository
{
    public function __construct()
    {
    }
}
