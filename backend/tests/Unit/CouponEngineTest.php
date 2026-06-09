<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\CouponEngine;
use App\Tests\Support\StubCouponRepository;
use App\Tests\Support\StubProductRepository;
use PHPUnit\Framework\TestCase;

final class CouponEngineTest extends TestCase
{
    /** @return array<string, mixed> */
    private function baseCoupon(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => '10.00',
            'min_subtotal' => null,
            'max_discount' => null,
            'starts_at' => null,
            'expires_at' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'scope' => 'all',
            'scope_id' => null,
            'active' => 1,
        ], $overrides);
    }

    public function testEmptyCodeYieldsNoDiscount(): void
    {
        $eng = new CouponEngine(new StubCouponRepository(null), new StubProductRepository());
        $r = $eng->apply('', [['product_id' => 1, 'qty' => 1, 'price' => '50.00']], '50.00');
        $this->assertSame('0.00', $r['discount']);
        $this->assertNull($r['error']);
    }

    public function testPercentAllScope(): void
    {
        $eng = new CouponEngine(
            new StubCouponRepository($this->baseCoupon()),
            new StubProductRepository(),
        );
        $lines = [['product_id' => 1, 'qty' => 2, 'price' => '25.00']];
        $r = $eng->apply('SAVE10', $lines, '50.00');
        $this->assertSame('5.00', $r['discount']);
        $this->assertNull($r['error']);
    }

    public function testCategoryScopeLimitsEligibleSubtotal(): void
    {
        $coupon = $this->baseCoupon(['scope' => 'category', 'scope_id' => 9, 'type' => 'fixed', 'value' => '3.00']);
        $products = new StubProductRepository([
            1 => ['id' => 1, 'category_id' => 9, 'name' => 'A'],
            2 => ['id' => 2, 'category_id' => 1, 'name' => 'B'],
        ]);
        $eng = new CouponEngine(new StubCouponRepository($coupon), $products);
        $lines = [
            ['product_id' => 1, 'qty' => 1, 'price' => '20.00'],
            ['product_id' => 2, 'qty' => 1, 'price' => '80.00'],
        ];
        $r = $eng->apply('SAVE10', $lines, '100.00');
        $this->assertSame('3.00', $r['discount']);
    }

    public function testBelowMinReturnsError(): void
    {
        $coupon = $this->baseCoupon(['min_subtotal' => '100.00']);
        $eng = new CouponEngine(new StubCouponRepository($coupon), new StubProductRepository());
        $r = $eng->apply('SAVE10', [['product_id' => 1, 'qty' => 1, 'price' => '10.00']], '10.00');
        $this->assertSame('0.00', $r['discount']);
        $this->assertSame('below_min', $r['error']);
    }

    public function testMaxDiscountCap(): void
    {
        $coupon = $this->baseCoupon(['max_discount' => '2.00']);
        $eng = new CouponEngine(new StubCouponRepository($coupon), new StubProductRepository());
        $lines = [['product_id' => 1, 'qty' => 1, 'price' => '100.00']];
        $r = $eng->apply('SAVE10', $lines, '100.00');
        $this->assertSame('2.00', $r['discount']);
    }
}
