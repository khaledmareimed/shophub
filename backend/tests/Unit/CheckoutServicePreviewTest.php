<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\CheckoutService;
use App\Services\CouponEngine;
use App\Tests\Support\NullCouponRepository;
use App\Tests\Support\NullInventoryRepository;
use App\Tests\Support\NullOrderRepository;
use App\Tests\Support\NullProductImageRepository;
use App\Tests\Support\NullProductRepositoryForCheckout;
use App\Tests\Support\StubCartRepository;
use App\Tests\Support\StubCouponRepository;
use App\Tests\Support\StubProductRepository;
use App\Tests\Support\StubSettingsRepository;
use PHPUnit\Framework\TestCase;

final class CheckoutServicePreviewTest extends TestCase
{
    /** @return array<string, mixed> */
    private function couponRow(): array
    {
        return [
            'id' => 7,
            'code' => 'PCT10',
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
        ];
    }

    public function testPreviewTotalsAndPercentCoupon(): void
    {
        $items = [
            [
                'product_id' => 1,
                'qty' => 2,
                'price_snapshot' => '10.00',
                'status' => 'active',
            ],
        ];
        $cart = new StubCartRepository(1, $items);
        $couponEngine = new CouponEngine(
            new StubCouponRepository($this->couponRow()),
            new StubProductRepository(),
        );
        $svc = new CheckoutService(
            $cart,
            new NullProductRepositoryForCheckout(),
            new NullOrderRepository(),
            new NullProductImageRepository(),
            $couponEngine,
            new StubSettingsRepository(['default_shipping_fee' => '5.99']),
            new NullInventoryRepository(),
            new NullCouponRepository(),
        );
        $p = $svc->preview(1, 'PCT10');
        $this->assertSame('20.00', $p['subtotal']);
        $this->assertSame('5.99', $p['shipping_fee']);
        $this->assertSame('2.00', $p['discount_total']);
        $this->assertSame('23.99', $p['grand_total']);
        $this->assertNull($p['coupon_error']);
    }

    public function testPreviewSkipsInactiveLines(): void
    {
        $items = [
            [
                'product_id' => 1,
                'qty' => 1,
                'price_snapshot' => '50.00',
                'status' => 'inactive',
            ],
        ];
        $cart = new StubCartRepository(3, $items);
        $svc = new CheckoutService(
            $cart,
            new NullProductRepositoryForCheckout(),
            new NullOrderRepository(),
            new NullProductImageRepository(),
            new CouponEngine(new StubCouponRepository(null), new StubProductRepository()),
            new StubSettingsRepository(['default_shipping_fee' => '1.00']),
            new NullInventoryRepository(),
            new NullCouponRepository(),
        );
        $p = $svc->preview(1, null);
        $this->assertSame('0.00', $p['subtotal']);
        $this->assertSame('1.00', $p['grand_total']);
    }
}
