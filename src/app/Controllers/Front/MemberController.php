<?php
namespace App\Controllers\Front;

use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\MemberAuth;

class MemberController
{
    public function registerForm(array $params = []): void
    {
        if (MemberAuth::check()) Helpers::redirect('/mypage');
        Helpers::view('front/register', ['title' => '회원가입', 'old' => []], 'front/layout');
    }

    public function register(array $params = []): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/register'); }
        $email = trim($_POST['email'] ?? '');
        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $pw    = $_POST['password'] ?? '';

        $err = null;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = '이메일 형식이 올바르지 않습니다.';
        elseif ($name === '') $err = '이름을 입력하세요.';
        elseif (strlen($pw) < 8) $err = '비밀번호는 8자 이상이어야 합니다.';
        elseif (Database::one('SELECT id FROM members WHERE email=?', [$email])) $err = '이미 가입된 이메일입니다.';

        if ($err) {
            Flash::error($err);
            Helpers::view('front/register', ['title' => '회원가입', 'old' => $_POST], 'front/layout');
            return;
        }
        Database::exec(
            'INSERT INTO members (email,password_hash,name,phone,lang,status) VALUES (?,?,?,?,?,?)',
            [$email, password_hash($pw, PASSWORD_BCRYPT), $name, $phone, \App\Lang::current(), 'active']
        );
        MemberAuth::attempt($email, $pw);
        Flash::success('가입이 완료되었습니다.');
        Helpers::redirect('/mypage');
    }

    public function loginForm(array $params = []): void
    {
        if (MemberAuth::check()) Helpers::redirect('/mypage');
        Helpers::view('front/login', ['title' => '로그인'], 'front/layout');
    }

    public function login(array $params = []): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/login'); }
        $email = trim($_POST['email'] ?? '');
        $pw    = $_POST['password'] ?? '';
        if (MemberAuth::attempt($email, $pw)) {
            $to = $_SESSION['after_login'] ?? '/mypage';
            unset($_SESSION['after_login']);
            Helpers::redirect($to);
        }
        Flash::error('이메일 또는 비밀번호가 올바르지 않습니다.');
        Helpers::view('front/login', ['title' => '로그인'], 'front/layout');
    }

    public function logout(array $params = []): void
    {
        MemberAuth::logout();
        Helpers::redirect('/');
    }

    public function mypage(array $params = []): void
    {
        MemberAuth::requireLogin();
        $orders = Database::all(
            'SELECT * FROM orders WHERE member_id=? ORDER BY id DESC',
            [MemberAuth::id()]
        );
        Helpers::view('front/mypage', ['title' => '마이페이지', 'orders' => $orders], 'front/layout');
    }

    public function order(array $params = []): void
    {
        MemberAuth::requireLogin();
        $o = Database::one('SELECT * FROM orders WHERE id=? AND member_id=?',
            [(int)$params['id'], MemberAuth::id()]);
        if (!$o) { Flash::error('주문을 찾을 수 없습니다.'); Helpers::redirect('/mypage'); }
        $items = Database::all('SELECT * FROM order_items WHERE order_id=?', [$o['id']]);
        $bank  = $o['bank_account_id'] ? Database::one('SELECT * FROM bank_accounts WHERE id=?', [$o['bank_account_id']]) : null;
        Helpers::view('front/mypage_order', ['title' => '주문 상세', 'o' => $o, 'items' => $items, 'bank' => $bank], 'front/layout');
    }
}
