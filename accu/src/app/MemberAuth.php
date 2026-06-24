<?php
namespace App;

/** 프론트 회원 인증 (관리자 Auth와 세션키 분리: $_SESSION['member']) */
class MemberAuth
{
    public static function attempt(string $email, string $password): bool
    {
        $m = Database::one('SELECT * FROM members WHERE email=? LIMIT 1', [$email]);
        if (!$m || $m['status'] !== 'active' || !password_verify($password, $m['password_hash'])) {
            return false;
        }
        $_SESSION['member'] = ['id' => (int)$m['id'], 'email' => $m['email'], 'name' => $m['name']];
        return true;
    }

    public static function check(): bool { return !empty($_SESSION['member']); }
    public static function user(): ?array { return $_SESSION['member'] ?? null; }
    public static function id(): ?int { return $_SESSION['member']['id'] ?? null; }
    public static function logout(): void { unset($_SESSION['member']); }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
            Helpers::redirect('/login');
        }
    }
}
