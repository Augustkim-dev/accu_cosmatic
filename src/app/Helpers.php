<?php
namespace App;

class Helpers
{
    /** HTML 이스케이프 */
    public static function e($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    /** 뷰 렌더 (layout 안에 content 삽입) */
    public static function view(string $view, array $data = [], ?string $layout = 'admin/layout'): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../views/' . $view . '.php';
        $content = ob_get_clean();
        if ($layout) {
            require __DIR__ . '/../views/' . $layout . '.php';
        } else {
            echo $content;
        }
    }

    public static function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    }

    /** 감사 로그 기록 */
    public static function log(string $action, ?string $target = null, ?int $adminId = null): void
    {
        try {
            Database::exec(
                'INSERT INTO activity_logs (admin_id, action, target, ip) VALUES (?,?,?,?)',
                [$adminId ?? ($_SESSION['admin']['id'] ?? null), $action, $target, self::ip()]
            );
        } catch (\Throwable $e) {
            // 로그 실패는 앱 흐름을 막지 않음
        }
    }
}
