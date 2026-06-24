<?php
namespace App;

class Flash
{
    public static function set(string $type, string $msg): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'msg' => $msg];
    }
    public static function success(string $msg): void { self::set('success', $msg); }
    public static function error(string $msg): void   { self::set('error', $msg); }

    /** 한 번 읽고 비움 */
    public static function pull(): array
    {
        $f = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $f;
    }
}
