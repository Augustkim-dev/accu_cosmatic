<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;

class MemberAdminController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('members.view');
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $rows = Database::all(
                'SELECT m.*, (SELECT COUNT(*) FROM orders o WHERE o.member_id=m.id) AS orders
                   FROM members m WHERE m.email LIKE ? OR m.name LIKE ? ORDER BY m.id DESC LIMIT 300',
                ['%' . $q . '%', '%' . $q . '%']
            );
        } else {
            $rows = Database::all(
                'SELECT m.*, (SELECT COUNT(*) FROM orders o WHERE o.member_id=m.id) AS orders
                   FROM members m ORDER BY m.id DESC LIMIT 300'
            );
        }
        Helpers::view('admin/members/index', ['title' => '회원관리', 'rows' => $rows, 'q' => $q]);
    }

    public function edit(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('members.edit');
        $m = Database::one('SELECT * FROM members WHERE id=?', [(int)$params['id']]);
        if (!$m) { Flash::error('회원을 찾을 수 없습니다.'); Helpers::redirect('/admin/members'); }
        Helpers::view('admin/members/form', ['title' => '회원 수정', 'm' => $m]);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('members.edit');
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/members'); }
        $id = (int)$params['id'];
        $name   = trim($_POST['name'] ?? '');
        $phone  = trim($_POST['phone'] ?? '');
        $status = ($_POST['status'] ?? 'active') === 'suspended' ? 'suspended' : 'active';
        Database::exec('UPDATE members SET name=?, phone=?, status=? WHERE id=?', [$name, $phone, $status, $id]);
        Helpers::log('member_update', 'member:' . $id);
        Flash::success('수정되었습니다.');
        Helpers::redirect('/admin/members');
    }

    public function export(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('members.export');
        $rows = Database::all('SELECT email,name,phone,status,created_at FROM members ORDER BY id DESC');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="members_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['이메일','이름','연락처','상태','가입일']);
        foreach ($rows as $r) fputcsv($out, [$r['email'],$r['name'],$r['phone'],$r['status'],$r['created_at']]);
        fclose($out);
        Helpers::log('member_export');
        exit;
    }
}
