<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Repositories\CouponRepository;

class NullCouponRepository extends CouponRepository
{
    public function __construct()
    {
    }
}
