<?php
namespace App\Controllers;

use App\Auth;
use App\Database;
use App\Helpers;
use App\Rbac;

class DashboardController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('dashboard.view');

        // 카운트 위젯 — 빈 DB여도 0으로 안전하게
        $count = function (string $table): int {
            try { return (int) Database::scalar("SELECT COUNT(*) FROM {$table}"); }
            catch (\Throwable $e) { return 0; }
        };

        $stats = [
            'products'  => $count('products'),
            'members'   => $count('members'),
            'orders'    => $count('orders'),
            'inquiries' => $count('inquiries'),
        ];

        // 최근 감사로그 (logs.view 권한자만 표시)
        $logs = [];
        if (Rbac::can('logs.view')) {
            $logs = Database::all(
                'SELECT l.action, l.target, l.ip, l.created_at, a.email
                   FROM activity_logs l
                   LEFT JOIN admin_users a ON a.id = l.admin_id
                  ORDER BY l.id DESC LIMIT 8'
            );
        }

        Helpers::view('admin/dashboard', [
            'title' => '대시보드',
            'stats' => $stats,
            'logs'  => $logs,
        ]);
    }
}
