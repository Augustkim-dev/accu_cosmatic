<?php
namespace App\Controllers;

use App\Auth;
use App\Database;
use App\Helpers;
use App\Rbac;

class LogController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('logs.view');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 50; $off = ($page - 1) * $per;
        $total = (int)Database::scalar('SELECT COUNT(*) FROM activity_logs');
        $rows = Database::all(
            "SELECT l.*, a.email FROM activity_logs l LEFT JOIN admin_users a ON a.id=l.admin_id
              ORDER BY l.id DESC LIMIT $per OFFSET $off"
        );
        Helpers::view('admin/logs/index', [
            'title' => '감사 로그', 'rows' => $rows,
            'page' => $page, 'pages' => max(1, (int)ceil($total / $per)),
        ]);
    }
}
