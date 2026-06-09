<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Core\Hasher;
use App\Services\AuthService;
use App\Services\Mailer\FileMailboxMailer;
use App\Tests\Support\StubPasswordResetRepository;
use App\Tests\Support\StubUserRepository;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    private const PEPPER = 'pepper-test';

    private function service(StubUserRepository $users): AuthService
    {
        return new AuthService(
            $users,
            new StubPasswordResetRepository(),
            new FileMailboxMailer(sys_get_temp_dir() . '/shophub_mail_tests'),
            self::PEPPER,
            'http://localhost',
        );
    }

    public function testRegisterReturnsTakenWhenEmailExists(): void
    {
        $existing = [
            'id' => 1,
            'email' => 'a@b.com',
            'password_hash' => 'x',
            'name' => 'Old',
            'role' => 'customer',
            'status' => 'active',
            'locale' => 'en',
        ];
        $out = $this->service(new StubUserRepository($existing))
            ->register(['email' => 'a@b.com', 'password' => 'Secret123!', 'name' => 'n', 'role' => 'customer']);
        $this->assertSame(['email' => 'taken'], $out);
    }

    public function testRegisterRejectsInvalidRole(): void
    {
        $out = $this->service(new StubUserRepository())
            ->register(['email' => 'b@b.com', 'password' => 'Secret123!', 'name' => 'n', 'role' => 'admin']);
        $this->assertSame(['role' => 'invalid'], $out);
    }

    public function testLoginReturnsUserOnSuccess(): void
    {
        $hash = Hasher::hashPassword('Secret123!', self::PEPPER);
        $user = [
            'id' => 5,
            'email' => 'buyer@example.com',
            'password_hash' => $hash,
            'name' => 'Buyer',
            'role' => 'customer',
            'status' => 'active',
            'locale' => 'en',
        ];
        $out = $this->service(new StubUserRepository($user))->login('buyer@example.com', 'Secret123!');
        $this->assertIsArray($out);
        $this->assertSame(5, $out['id']);
        $this->assertSame('customer', $out['role']);
        $this->assertArrayNotHasKey('password_hash', $out);
    }

    public function testLoginInvalidPasswordReturnsCode(): void
    {
        $hash = Hasher::hashPassword('right-password', self::PEPPER);
        $user = [
            'id' => 5,
            'email' => 'b@b.com',
            'password_hash' => $hash,
            'name' => 'Buyer',
            'role' => 'customer',
            'status' => 'active',
            'locale' => 'en',
        ];
        $out = $this->service(new StubUserRepository($user))->login('b@b.com', 'wrong-password');
        $this->assertSame(['code' => 'invalid_credentials'], $out);
    }

    public function testLoginUnknownEmailReturnsCode(): void
    {
        $out = $this->service(new StubUserRepository())->login('missing@example.com', 'whatever');
        $this->assertSame(['code' => 'invalid_credentials'], $out);
    }

    public function testLoginBannedReturnsCode(): void
    {
        $hash = Hasher::hashPassword('Secret123!', self::PEPPER);
        $user = [
            'id' => 5,
            'email' => 'b@b.com',
            'password_hash' => $hash,
            'name' => 'B',
            'role' => 'customer',
            'status' => 'banned',
            'locale' => 'en',
        ];
        $out = $this->service(new StubUserRepository($user))->login('b@b.com', 'Secret123!');
        $this->assertSame(['code' => 'banned'], $out);
    }
}
