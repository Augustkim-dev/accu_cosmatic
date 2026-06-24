<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;

class OrderController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('orders.view');
        $status = $_GET['status'] ?? '';
        $where = ''; $args = [];
        $valid = ['pending','paid','shipping','done','cancelled'];
        if (in_array($status, $valid, true)) { $where = 'WHERE o.status=?'; $args[] = $status; }

        $rows = Database::all(
            "SELECT o.*, m.email AS member_email
               FROM orders o LEFT JOIN members m ON m.id=o.member_id
               $where ORDER BY o.id DESC LIMIT 200",
            $args
        );
        Helpers::view('admin/orders/index', ['title' => '주문관리', 'rows' => $rows, 'status' => $status]);
    }

    public function show(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('orders.view');
        $o = Database::one('SELECT * FROM orders WHERE id=?', [(int)$params['id']]);
        if (!$o) { Flash::error('주문을 찾을 수 없습니다.'); Helpers::redirect('/admin/orders'); }
        $items  = Database::all('SELECT * FROM order_items WHERE order_id=?', [$o['id']]);
        $member = $o['member_id'] ? Database::one('SELECT * FROM members WHERE id=?', [$o['member_id']]) : null;
        $bank   = $o['bank_account_id'] ? Database::one('SELECT * FROM bank_accounts WHERE id=?', [$o['bank_account_id']]) : null;
        Helpers::view('admin/orders/show', ['title' => '주문 #' . $o['order_no'], 'o' => $o, 'items' => $items, 'member' => $member, 'bank' => $bank]);
    }

    /** 입금확인 → paid */
    public function confirm(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('orders.confirm');
        $this->csrf();
        $id = (int)$params['id'];
        Database::exec("UPDATE orders SET status='paid', paid_at=NOW() WHERE id=? AND status='pending'", [$id]);
        Helpers::log('order_confirm', 'order:' . $id);
        Flash::success('입금확인 처리되었습니다.');
        Helpers::redirect('/admin/orders/' . $id);
    }

    /** 배송상태 변경 */
    public function status(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('orders.edit');
        $this->csrf();
        $id = (int)$params['id'];
        $to = $_POST['status'] ?? '';
        if (in_array($to, ['paid','shipping','done'], true)) {
            Database::exec('UPDATE orders SET status=? WHERE id=?', [$to, $id]);
            Helpers::log('order_status', 'order:' . $id . '→' . $to);
            Flash::success('상태가 변경되었습니다.');
        }
        Helpers::redirect('/admin/orders/' . $id);
    }

    public function cancel(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('orders.cancel');
        $this->csrf();
        $id = (int)$params['id'];
        Database::exec("UPDATE orders SET status='cancelled' WHERE id=?", [$id]);
        Helpers::log('order_cancel', 'order:' . $id);
        Flash::success('주문이 취소되었습니다.');
        Helpers::redirect('/admin/orders/' . $id);
    }

    public function export(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('orders.export');
        $rows = Database::all('SELECT order_no,receiver_name,phone,total,status,created_at FROM orders ORDER BY id DESC');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="orders_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM (엑셀 한글)
        $out = fopen('php://output', 'w');
        fputcsv($out, ['주문번호','받는분','연락처','금액','상태','주문일']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['order_no'],$r['receiver_name'],$r['phone'],$r['total'],$r['status'],$r['created_at']]);
        }
        fclose($out);
        Helpers::log('order_export');
        exit;
    }

    private function csrf(): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/orders'); }
    }
}
