<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Helpers;

class AuthController
{
    public function loginForm(array $params = []): void
    {
        if (Auth::check()) Helpers::redirect('/admin');
        Helpers::view('admin/login', ['error' => null], null);
    }

    public function login(array $params = []): void
    {
        if (!Csrf::check()) {
            Helpers::view('admin/login', ['error' => '세션이 만료되었습니다. 다시 시도하세요.'], null);
            return;
        }
        $email = trim($_POST['email'] ?? '');
        $pw    = $_POST['password'] ?? '';

        if (Auth::attempt($email, $pw)) {
            Helpers::redirect('/admin');
        }
        Helpers::view('admin/login', ['error' => '이메일 또는 비밀번호가 올바르지 않습니다.'], null);
    }

    public function logout(array $params = []): void
    {
        Auth::logout();
        Helpers::redirect('/admin/login');
    }
}
