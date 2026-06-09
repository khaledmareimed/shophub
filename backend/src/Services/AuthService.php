<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Hasher;
use App\Repositories\PasswordResetRepository;
use App\Repositories\UserRepository;

/**
 * Session-based auth service. The HTTP layer (login page) is responsible for
 * regenerating the session id and writing $_SESSION on success.
 */
final class AuthService
{
    public function __construct(
        private UserRepository $users,
        private PasswordResetRepository $resets,
        private Mailer\FileMailboxMailer $mailer,
        private string $pepper,
        private string $appUrl,
    ) {
    }

    /**
     * @param array<string, mixed> $in
     * @return int|array{email?:string, role?:string} new user id, or field error map
     */
    public function register(array $in): int|array
    {
        $email = strtolower(trim((string) ($in['email'] ?? '')));
        if ($this->users->findByEmail($email)) {
            return ['email' => 'taken'];
        }
        $role = (string) ($in['role'] ?? 'customer');
        if (!in_array($role, ['customer', 'seller'], true)) {
            return ['role' => 'invalid'];
        }
        $hash = Hasher::hashPassword((string) ($in['password'] ?? ''), $this->pepper);
        return $this->users->create([
            'email' => $email,
            'password_hash' => $hash,
            'name' => $in['name'] ?? '',
            'phone' => $in['phone'] ?? null,
            'locale' => $in['locale'] ?? 'en',
            'role' => $role,
            'status' => 'active',
        ]);
    }

    /**
     * @return array<string, mixed>|array{code:string}
     *         user row (without password_hash) on success;
     *         ['code'=>'invalid_credentials'|'banned'] on failure.
     */
    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);
        if (!$user) {
            return ['code' => 'invalid_credentials'];
        }
        if (!Hasher::verifyPassword($password, (string) $user['password_hash'], $this->pepper)) {
            return ['code' => 'invalid_credentials'];
        }
        if ($user['status'] === 'banned') {
            return ['code' => 'banned'];
        }
        unset($user['password_hash']);
        return $user;
    }

    public function forgotPassword(string $email): void
    {
        $user = $this->users->findByEmail($email);
        if (!$user) {
            return;
        }
        $raw = Hasher::randomToken(32);
        $hash = Hasher::sha256Hex($raw);
        $exp = gmdate('Y-m-d H:i:s', time() + 3600);
        $this->resets->create((int) $user['id'], $hash, $exp);
        $link = rtrim($this->appUrl, '/')
            . '/store/pages/auth/reset-password.php?token=' . urlencode($raw);
        $this->mailer->send(
            (string) $user['email'],
            'Password reset',
            "Use this link (valid 1h): $link\n"
        );
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $h = Hasher::sha256Hex($token);
        $row = $this->resets->findValid($h);
        if (!$row) {
            return false;
        }
        $hash = Hasher::hashPassword($newPassword, $this->pepper);
        $pdo = \App\Core\Database::pdo();
        $st = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $st->execute([$hash, (int) $row['user_id']]);
        $this->resets->markUsed((int) $row['id']);
        return true;
    }

    /** @return array<string, mixed>|null */
    public function me(int $id): ?array
    {
        $u = $this->users->findById($id);
        if (!$u) {
            return null;
        }
        unset($u['password_hash']);
        return $u;
    }
}
