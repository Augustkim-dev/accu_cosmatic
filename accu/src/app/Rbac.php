<?php
namespace App;

class Rbac
{
    /**
     * 권한 보유 여부.
     *   can('products.create')  단일
     *   can('orders.confirm')   단일
     * 최종관리자(superadmin)는 항상 통과.
     */
    public static function can(string $permission): bool
    {
        $u = Auth::user();
        if (!$u) return false;
        if ($u['role_code'] === 'superadmin') return true;
        return in_array($permission, $u['perms'] ?? [], true);
    }

    /** 여러 권한 중 하나라도 있으면 true (메뉴 노출 판단용) */
    public static function canAny(array $permissions): bool
    {
        foreach ($permissions as $p) {
            if (self::can($p)) return true;
        }
        return false;
    }

    /** 권한 없으면 403 차단 + 감사로그 */
    public static function require(string $permission): void
    {
        if (!self::can($permission)) {
            Helpers::log('denied', $permission);
            http_response_code(403);
            Helpers::view('admin/403', ['permission' => $permission]);
            exit;
        }
    }
}
