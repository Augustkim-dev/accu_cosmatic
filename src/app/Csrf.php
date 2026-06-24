<?php
namespace App;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    public static function check(): bool
    {
        $sent = $_POST['_csrf'] ?? '';
        return is_string($sent) && !empty($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $sent);
    }
}
