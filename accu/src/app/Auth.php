<?php
namespace App;

class Auth
{
    /** 로그인 시도. 성공하면 세션에 관리자+역할+권한 적재 */
    public static function attempt(string $email, string $password): bool
    {
        $sql = 'SELECT a.id, a.email, a.password_hash, a.name, a.status,
                       r.id AS role_id, r.code AS role_code, r.name_ko AS role_name, r.level
                  FROM admin_users a
                  JOIN admin_roles r ON r.id = a.role_id
                 WHERE a.email = ? LIMIT 1';
        $u = Database::one($sql, [$email]);

        if (!$u || $u['status'] !== 'active' || !password_verify($password, $u['password_hash'])) {
            Helpers::log('login_failed', $email, null);
            return false;
        }

        // 역할의 권한 목록 → "module.action" 문자열 배열
        $perms = Database::all(
            'SELECT p.module, p.action
               FROM role_permissions rp
               JOIN permissions p ON p.id = rp.permission_id
              WHERE rp.role_id = ?',
            [$u['role_id']]
        );
        $permKeys = array_map(fn($p) => $p['module'] . '.' . $p['action'], $perms);

        $_SESSION['admin'] = [
            'id'        => (int)$u['id'],
            'email'     => $u['email'],
            'name'      => $u['name'],
            'role_code' => $u['role_code'],
            'role_name' => $u['role_name'],
            'level'     => (int)$u['level'],
            'perms'     => $permKeys,
        ];

        Database::exec('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?', [$u['id']]);
        Helpers::log('login', $email, (int)$u['id']);
        return true;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['admin']);
    }

    public static function user(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }

    public static function logout(): void
    {
        Helpers::log('logout', Auth::user()['email'] ?? null);
        unset($_SESSION['admin']);
    }

    /** 로그인 강제 */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Helpers::redirect('/admin/login');
        }
    }
}
