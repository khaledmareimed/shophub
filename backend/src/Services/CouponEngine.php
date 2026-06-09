<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Decimal;
use App\Repositories\CouponRepository;
use App\Repositories\ProductRepository;

final class CouponEngine
{
    public function __construct(
        private CouponRepository $coupons,
        private ProductRepository $products,
    ) {
    }

    /**
     * @param list<array{product_id:int, qty:int, price:string}> $lines
     * @return array{discount: string, coupon: array<string, mixed>|null, error: string|null}
     */
    public function apply(?string $code, array $lines, string $subtotal): array
    {
        if ($code === null || $code === '') {
            return ['discount' => '0.00', 'coupon' => null, 'error' => null];
        }
        $c = $this->coupons->findByCode($code);
        if (!$c) {
            return ['discount' => '0.00', 'coupon' => null, 'error' => 'invalid_coupon'];
        }
        if (!empty($c['starts_at']) && strtotime((string) $c['starts_at']) > time()) {
            return ['discount' => '0.00', 'coupon' => null, 'error' => 'coupon_not_started'];
        }
        if (!empty($c['expires_at']) && strtotime((string) $c['expires_at']) < time()) {
            return ['discount' => '0.00', 'coupon' => null, 'error' => 'coupon_expired'];
        }
        if ($c['usage_limit'] !== null && (int) $c['used_count'] >= (int) $c['usage_limit']) {
            return ['discount' => '0.00', 'coupon' => null, 'error' => 'coupon_exhausted'];
        }
        $min = $c['min_subtotal'] !== null ? (string) $c['min_subtotal'] : null;
        if ($min !== null && Decimal::comp($subtotal, $min, 2) < 0) {
            return ['discount' => '0.00', 'coupon' => null, 'error' => 'below_min'];
        }
        $scope = (string) $c['scope'];
        $scopeId = $c['scope_id'] !== null ? (int) $c['scope_id'] : null;
        $eligible = '0.00';
        foreach ($lines as $ln) {
            $pid = (int) $ln['product_id'];
            $lineTotal = Decimal::mul((string) $ln['price'], (string) $ln['qty'], 2);
            if ($scope === 'all') {
                $eligible = Decimal::add($eligible, $lineTotal, 2);
                continue;
            }
            if ($scope === 'product' && $scopeId === $pid) {
                $eligible = Decimal::add($eligible, $lineTotal, 2);
            }
            if ($scope === 'category') {
                $p = $this->products->findById($pid);
                if ($p && (int) $p['category_id'] === $scopeId) {
                    $eligible = Decimal::add($eligible, $lineTotal, 2);
                }
            }
        }
        if (Decimal::comp($eligible, '0', 2) <= 0) {
            return ['discount' => '0.00', 'coupon' => null, 'error' => 'coupon_not_applicable'];
        }
        $type = (string) $c['type'];
        $val = (string) $c['value'];
        $disc = '0.00';
        if ($type === 'percent') {
            $disc = Decimal::mul($eligible, Decimal::div($val, '100', 4), 2);
        } else {
            $disc = $val;
        }
        $maxD = $c['max_discount'] !== null ? (string) $c['max_discount'] : null;
        if ($maxD !== null && Decimal::comp($disc, $maxD, 2) > 0) {
            $disc = $maxD;
        }
        if (Decimal::comp($disc, $subtotal, 2) > 0) {
            $disc = $subtotal;
        }
        return ['discount' => $disc, 'coupon' => $c, 'error' => null];
    }
}
