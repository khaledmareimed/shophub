<?php

declare(strict_types=1);

namespace App\Web;

use App\Repositories\AddressRepository;
use App\Repositories\CartRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CouponRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PasswordResetRepository;
use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\SellerRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;
use App\Repositories\WishlistRepository;
use App\Services\AuthService;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CouponEngine;
use App\Services\Mailer\FileMailboxMailer;
use App\Services\ReportService;

/**
 * Tiny request-scoped container so pages can do `Container::get(SomeService::class)`
 * without re-wiring constructor graphs each call.
 */
final class Container
{
    /** @var array<class-string, object> */
    private static array $instances = [];

    public static function reset(): void
    {
        self::$instances = [];
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public static function get(string $class): object
    {
        if (isset(self::$instances[$class])) {
            /** @var T */
            return self::$instances[$class];
        }
        $obj = self::build($class);
        self::$instances[$class] = $obj;
        /** @var T */
        return $obj;
    }

    private static function build(string $class): object
    {
        return match ($class) {
            UserRepository::class => new UserRepository(),
            PasswordResetRepository::class => new PasswordResetRepository(),
            SellerRepository::class => new SellerRepository(),
            CategoryRepository::class => new CategoryRepository(),
            ProductRepository::class => new ProductRepository(),
            ProductImageRepository::class => new ProductImageRepository(),
            CartRepository::class => new CartRepository(),
            WishlistRepository::class => new WishlistRepository(),
            OrderRepository::class => new OrderRepository(),
            CouponRepository::class => new CouponRepository(),
            ReviewRepository::class => new ReviewRepository(),
            AddressRepository::class => new AddressRepository(),
            InventoryRepository::class => new InventoryRepository(),
            SettingsRepository::class => new SettingsRepository(),
            CartService::class => new CartService(
                self::get(CartRepository::class),
                self::get(ProductRepository::class),
            ),
            CouponEngine::class => new CouponEngine(
                self::get(CouponRepository::class),
                self::get(ProductRepository::class),
            ),
            CheckoutService::class => new CheckoutService(
                self::get(CartRepository::class),
                self::get(ProductRepository::class),
                self::get(OrderRepository::class),
                self::get(ProductImageRepository::class),
                self::get(CouponEngine::class),
                self::get(SettingsRepository::class),
                self::get(InventoryRepository::class),
                self::get(CouponRepository::class),
            ),
            ReportService::class => new ReportService(),
            FileMailboxMailer::class => new FileMailboxMailer(
                ($_ENV['MAILBOX_DIR'] ?? null)
                    ? (str_starts_with($_ENV['MAILBOX_DIR'], '/')
                        ? $_ENV['MAILBOX_DIR']
                        : dirname(__DIR__, 2) . '/' . $_ENV['MAILBOX_DIR'])
                    : dirname(__DIR__, 2) . '/storage/mailbox'
            ),
            AuthService::class => new AuthService(
                self::get(UserRepository::class),
                self::get(PasswordResetRepository::class),
                self::get(FileMailboxMailer::class),
                (string) ($_ENV['AUTH_PEPPER'] ?? $_ENV['JWT_PEPPER'] ?? ''),
                (string) ($_ENV['APP_URL'] ?? ''),
            ),
            default => throw new \RuntimeException('Unknown service: ' . $class),
        };
    }
}
