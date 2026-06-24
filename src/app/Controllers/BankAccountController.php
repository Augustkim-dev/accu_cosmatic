<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;

class BankAccountController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('settings.view');
        $rows = Database::all('SELECT * FROM bank_accounts ORDER BY sort,id');
        Helpers::view('admin/bank_accounts/index', ['title' => '입금계좌', 'rows' => $rows]);
    }

    public function create(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('settings.edit');
        Helpers::view('admin/bank_accounts/form', ['title' => '계좌 등록', 'item' => [], 'isEdit' => false, 'action' => '/admin/bank-accounts']);
    }

    public function store(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('settings.edit');
        $this->csrf();
        $d = $this->input();
        if ($d['bank_name'] === '' || $d['account_no'] === '') { Flash::error('은행명과 계좌번호는 필수입니다.'); Helpers::redirect('/admin/bank-accounts/create'); }
        Database::exec('INSERT INTO bank_accounts (bank_name,account_no,holder,is_active,sort) VALUES (?,?,?,?,?)',
            [$d['bank_name'],$d['account_no'],$d['holder'],$d['is_active'],$d['sort']]);
        Helpers::log('bank_create', $d['bank_name']);
        Flash::success('등록되었습니다.');
        Helpers::redirect('/admin/bank-accounts');
    }

    public function edit(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('settings.edit');
        $b = Database::one('SELECT * FROM bank_accounts WHERE id=?', [(int)$params['id']]);
        if (!$b) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/bank-accounts'); }
        Helpers::view('admin/bank_accounts/form', ['title' => '계좌 수정', 'item' => $b, 'isEdit' => true, 'action' => '/admin/bank-accounts/' . $b['id']]);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('settings.edit');
        $this->csrf();
        $id = (int)$params['id'];
        $d = $this->input();
        Database::exec('UPDATE bank_accounts SET bank_name=?,account_no=?,holder=?,is_active=?,sort=? WHERE id=?',
            [$d['bank_name'],$d['account_no'],$d['holder'],$d['is_active'],$d['sort'],$id]);
        Helpers::log('bank_update', 'bank:' . $id);
        Flash::success('수정되었습니다.');
        Helpers::redirect('/admin/bank-accounts');
    }

    public function destroy(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('settings.edit');
        $this->csrf();
        $id = (int)$params['id'];
        Database::exec('DELETE FROM bank_accounts WHERE id=?', [$id]);
        Helpers::log('bank_delete', 'bank:' . $id);
        Flash::success('삭제되었습니다.');
        Helpers::redirect('/admin/bank-accounts');
    }

    private function input(): array
    {
        return [
            'bank_name'  => trim($_POST['bank_name'] ?? ''),
            'account_no' => trim($_POST['account_no'] ?? ''),
            'holder'     => trim($_POST['holder'] ?? ''),
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
            'sort'       => (int)($_POST['sort'] ?? 0),
        ];
    }

    private function csrf(): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/bank-accounts'); }
    }
}
