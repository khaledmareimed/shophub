<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Decimal;
use App\Repositories\CartRepository;
use App\Repositories\CouponRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SettingsRepository;

final class CheckoutService
{
    public function __construct(
        private CartRepository $cart,
        private ProductRepository $products,
        private OrderRepository $orders,
        private ProductImageRepository $images,
        private CouponEngine $couponEngine,
        private SettingsRepository $settings,
        private InventoryRepository $inv,
        private CouponRepository $coupons,
    ) {
    }

    /** @return array<string, mixed> */
    public function preview(int $userId, ?string $couponCode): array
    {
        $cid = $this->cart->getOrCreateCartId($userId);
        $items = $this->cart->itemsWithProduct($cid);
        $lines = [];
        $sub = '0.00';
        foreach ($items as $it) {
            if (($it['status'] ?? '') !== 'active') {
                continue;
            }
            $qty = (int) $it['qty'];
            $price = (string) $it['price_snapshot'];
            $line = Decimal::mul($price, (string) $qty, 2);
            $sub = Decimal::add($sub, $line, 2);
            $lines[] = ['product_id' => (int) $it['product_id'], 'qty' => $qty, 'price' => $price];
        }
        $site = $this->settings->get('site', ['default_shipping_fee' => '5.99']);
        $ship = (string) ($site['default_shipping_fee'] ?? '5.99');
        $c = $this->couponEngine->apply($couponCode, $lines, $sub);
        $disc = $c['discount'];
        $grand = Decimal::sub(Decimal::add($sub, $ship, 2), $disc, 2);
        if (Decimal::comp($grand, '0', 2) < 0) {
            $grand = '0.00';
        }
        return [
            'subtotal' => $sub,
            'shipping_fee' => $ship,
            'discount_total' => $disc,
            'grand_total' => $grand,
            'coupon_error' => $c['error'],
        ];
    }

    /**
     * @param array<string, mixed> $address shipping snapshot
     * @return array<string, mixed>
     */
    public function place(int $userId, array $address, ?string $couponCode, ?string $notes): array
    {
        $preview = $this->preview($userId, $couponCode);
        $cid = $this->cart->getOrCreateCartId($userId);
        $items = $this->cart->itemsWithProduct($cid);
        $validItems = array_values(array_filter(
            $items,
            static fn (array $it): bool => ($it['status'] ?? '') === 'active'
        ));
        if ($validItems === []) {
            throw new \InvalidArgumentException('empty_cart');
        }
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            foreach ($validItems as $it) {
                $pid = (int) $it['product_id'];
                $qty = (int) $it['qty'];
                $row = $this->products->lockRowForStock($pid);
                if (!$row || (int) $row['stock'] < $qty) {
                    throw new \InvalidArgumentException('out_of_stock:' . $pid);
                }
            }
            $code = 'ORD-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $ship = $preview['shipping_fee'];
            $disc = $preview['discount_total'];
            $sub = $preview['subtotal'];
            $grand = $preview['grand_total'];
            $couponRow = null;
            if ($couponCode && Decimal::comp($disc, '0', 2) > 0) {
                $lines = array_map(static function ($it) {
                    return [
                        'product_id' => (int) $it['product_id'],
                        'qty' => (int) $it['qty'],
                        'price' => (string) $it['price_snapshot'],
                    ];
                }, $validItems);
                $c = $this->couponEngine->apply($couponCode, $lines, $sub);
                $couponRow = $c['coupon'];
            }
            $oid = $this->orders->insertOrder([
                'code' => $code,
                'customer_id' => $userId,
                'subtotal' => $sub,
                'shipping_fee' => $ship,
                'discount_total' => $disc,
                'grand_total' => $grand,
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'status' => 'pending',
                'shipping_address_json' => json_encode($address, JSON_THROW_ON_ERROR),
                'customer_notes' => $notes,
                'coupon_code' => $couponRow ? (string) $couponRow['code'] : null,
            ]);
            foreach ($validItems as $it) {
                $pid = (int) $it['product_id'];
                $qty = (int) $it['qty'];
                $p = $this->products->findById($pid);
                if (!$p) {
                    throw new \RuntimeException('product_missing');
                }
                $imgs = $this->images->byProduct($pid);
                $thumb = $imgs[0]['path'] ?? null;
                $lineTotal = Decimal::mul((string) $it['price_snapshot'], (string) $qty, 2);
                $this->orders->insertItem([
                    'order_id' => $oid,
                    'product_id' => $pid,
                    'seller_id' => (int) $p['seller_id'],
                    'name_snapshot' => (string) $p['name'],
                    'image_path_snapshot' => $thumb,
                    'price_snapshot' => (string) $it['price_snapshot'],
                    'qty' => $qty,
                    'line_total' => $lineTotal,
                    'fulfillment_status' => 'pending',
                ]);
                $this->products->adjustStock($pid, -$qty);
                $this->products->bumpSold($pid, $qty);
                $this->inv->log($pid, -$qty, 'order_placed', 'order', $oid);
                $st = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
                $st->execute([$pid]);
                $stRow = $st->fetch();
                if ($stRow && (int) $stRow['stock'] === 0) {
                    $this->products->setStatus($pid, 'outofstock', null);
                }
            }
            if ($couponRow && Decimal::comp($disc, '0', 2) > 0) {
                $this->coupons->insertRedemption((int) $couponRow['id'], $oid, $userId, $disc);
                $this->coupons->incrementUsed((int) $couponRow['id']);
            }
            $this->cart->clear($cid);
            $pdo->commit();
            return ['order_code' => $code, 'order_id' => $oid];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
