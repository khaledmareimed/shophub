<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\ProductRepository;

/** Unused in {@see \App\Services\CheckoutService::preview()} — placeholder for constructor. */
class NullProductRepositoryForCheckout extends ProductRepository
{
    public function __construct()
    {
    }
}
