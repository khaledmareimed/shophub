<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Core\Hasher;
use PHPUnit\Framework\TestCase;

final class CryptoTest extends TestCase
{
    public function testPasswordHashRoundTrip(): void
    {
        $h = Hasher::hashPassword('Secret123', '');
        $this->assertTrue(Hasher::verifyPassword('Secret123', $h, ''));
        $this->assertFalse(Hasher::verifyPassword('wrong', $h, ''));
    }

    public function testPasswordHashWithPepper(): void
    {
        $h = Hasher::hashPassword('Secret123', 'p3pp3r');
        $this->assertTrue(Hasher::verifyPassword('Secret123', $h, 'p3pp3r'));
        $this->assertFalse(Hasher::verifyPassword('Secret123', $h, ''));
    }

    public function testRandomTokenIsHex(): void
    {
        $t = Hasher::randomToken(16);
        $this->assertSame(32, strlen($t));
        $this->assertSame(1, preg_match('/^[a-f0-9]+$/', $t));
    }

    public function testSha256HexIsStable(): void
    {
        $this->assertSame(hash('sha256', 'abc'), Hasher::sha256Hex('abc'));
    }
}
