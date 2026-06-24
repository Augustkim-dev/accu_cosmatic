<?php
namespace App\Controllers;

use App\Auth;
use App\Database;
use App\Helpers;
use App\Rbac;

class ReportController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('reports.view');

        $scalar = fn(string $sql) => (function () use ($sql) {
            try { return (int)Database::scalar($sql); } catch (\Throwable $e) { return 0; }
        })();

        $stats = [
            'members'      => $scalar('SELECT COUNT(*) FROM members'),
            'products'     => $scalar('SELECT COUNT(*) FROM products'),
            'orders'       => $scalar('SELECT COUNT(*) FROM orders'),
            'orders_paid'  => $scalar("SELECT COUNT(*) FROM orders WHERE status<>'pending' AND status<>'cancelled'"),
            'revenue'      => $scalar("SELECT COALESCE(SUM(total),0) FROM orders WHERE status IN ('paid','shipping','done')"),
            'pending'      => $scalar("SELECT COUNT(*) FROM orders WHERE status='pending'"),
        ];

        // 최근 14일 일별 주문수
        $daily = [];
        try {
            $daily = Database::all(
                "SELECT DATE(created_at) d, COUNT(*) c, COALESCE(SUM(total),0) amt
                   FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                  GROUP BY DATE(created_at) ORDER BY d"
            );
        } catch (\Throwable $e) {}

        // 베스트 제품(주문 수량 기준)
        $topProducts = [];
        try {
            $topProducts = Database::all(
                "SELECT product_name, SUM(qty) q FROM order_items GROUP BY product_name ORDER BY q DESC LIMIT 5"
            );
        } catch (\Throwable $e) {}

        Helpers::view('admin/reports/index', ['title' => '통계/리포트', 'stats' => $stats, 'daily' => $daily, 'top' => $topProducts]);
    }
}
