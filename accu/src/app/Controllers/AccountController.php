<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;

class AccountController
{
    public function form(array $params = []): void
    {
        Auth::requireLogin();
        Helpers::view('admin/account/password', ['title' => '비밀번호 변경']);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/account/password'); }

        $cur = $_POST['current'] ?? '';
        $new = $_POST['new'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $me = Auth::user();

        $row = Database::one('SELECT password_hash FROM admin_users WHERE id=?', [$me['id']]);
        if (!$row || !password_verify($cur, $row['password_hash'])) {
            Flash::error('현재 비밀번호가 올바르지 않습니다.');
            Helpers::redirect('/admin/account/password');
        }
        if (strlen($new) < 10) {
            Flash::error('새 비밀번호는 10자 이상이어야 합니다.');
            Helpers::redirect('/admin/account/password');
        }
        if ($new !== $confirm) {
            Flash::error('새 비밀번호 확인이 일치하지 않습니다.');
            Helpers::redirect('/admin/account/password');
        }

        $hash = password_hash($new, PASSWORD_BCRYPT);
        Database::exec('UPDATE admin_users SET password_hash=? WHERE id=?', [$hash, $me['id']]);
        Helpers::log('password_change', 'admin:' . $me['id']);
        Flash::success('비밀번호가 변경되었습니다.');
        Helpers::redirect('/admin');
    }
}
