<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;

class AdminUserController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('admins.view');
        $rows = Database::all(
            'SELECT a.*, r.name_ko AS role_name, r.code AS role_code
               FROM admin_users a JOIN admin_roles r ON r.id=a.role_id ORDER BY a.id'
        );
        Helpers::view('admin/admins/index', ['title' => '관리자 계정', 'rows' => $rows]);
    }

    public function create(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('admins.create');
        Helpers::view('admin/admins/form', [
            'title' => '관리자 등록', 'item' => [], 'isEdit' => false,
            'action' => '/admin/admins', 'roles' => $this->roles(),
        ]);
    }

    public function store(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('admins.create');
        $this->csrf();
        $email = trim($_POST['email'] ?? '');
        $name  = trim($_POST['name'] ?? '');
        $roleId= (int)($_POST['role_id'] ?? 0);
        $pw    = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || !$roleId) {
            Flash::error('이메일·이름·역할은 필수입니다.'); Helpers::redirect('/admin/admins/create');
        }
        if (strlen($pw) < 10) { Flash::error('비밀번호는 10자 이상이어야 합니다.'); Helpers::redirect('/admin/admins/create'); }
        if (Database::one('SELECT id FROM admin_users WHERE email=?', [$email])) {
            Flash::error('이미 존재하는 이메일입니다.'); Helpers::redirect('/admin/admins/create');
        }
        Database::exec(
            'INSERT INTO admin_users (email,password_hash,name,role_id,status) VALUES (?,?,?,?,?)',
            [$email, password_hash($pw, PASSWORD_BCRYPT), $name, $roleId, 'active']
        );
        Helpers::log('admin_create', $email);
        Flash::success('관리자가 등록되었습니다.');
        Helpers::redirect('/admin/admins');
    }

    public function edit(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('admins.edit');
        $a = Database::one('SELECT * FROM admin_users WHERE id=?', [(int)$params['id']]);
        if (!$a) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/admins'); }
        Helpers::view('admin/admins/form', [
            'title' => '관리자 수정', 'item' => $a, 'isEdit' => true,
            'action' => '/admin/admins/' . $a['id'], 'roles' => $this->roles(),
        ]);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('admins.edit');
        $this->csrf();
        $id = (int)$params['id'];
        $a = Database::one('SELECT * FROM admin_users WHERE id=?', [$id]);
        if (!$a) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/admins'); }

        $name   = trim($_POST['name'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? $a['role_id']);
        $status = ($_POST['status'] ?? 'active') === 'suspended' ? 'suspended' : 'active';
        $pw     = $_POST['password'] ?? '';

        // 안전장치: 마지막 활성 최종관리자를 강등/정지하지 못하게
        if ($this->isSuper((int)$a['role_id']) && $a['status'] === 'active') {
            $willLoseSuper = !$this->isSuper($roleId) || $status === 'suspended';
            if ($willLoseSuper && $this->activeSupers() <= 1) {
                Flash::error('마지막 최종관리자는 강등/정지할 수 없습니다.');
                Helpers::redirect('/admin/admins/' . $id . '/edit');
            }
        }

        if ($pw !== '') {
            if (strlen($pw) < 10) { Flash::error('비밀번호는 10자 이상이어야 합니다.'); Helpers::redirect('/admin/admins/' . $id . '/edit'); }
            Database::exec('UPDATE admin_users SET password_hash=? WHERE id=?', [password_hash($pw, PASSWORD_BCRYPT), $id]);
        }
        Database::exec('UPDATE admin_users SET name=?, role_id=?, status=? WHERE id=?', [$name, $roleId, $status, $id]);
        Helpers::log('admin_update', 'admin:' . $id);
        Flash::success('수정되었습니다.');
        Helpers::redirect('/admin/admins');
    }

    public function destroy(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('admins.delete');
        $this->csrf();
        $id = (int)$params['id'];
        $me = Auth::user();

        if ($id === (int)$me['id']) { Flash::error('자기 자신은 삭제할 수 없습니다.'); Helpers::redirect('/admin/admins'); }
        $a = Database::one('SELECT * FROM admin_users WHERE id=?', [$id]);
        if (!$a) { Helpers::redirect('/admin/admins'); }
        if ($this->isSuper((int)$a['role_id']) && $a['status'] === 'active' && $this->activeSupers() <= 1) {
            Flash::error('마지막 최종관리자는 삭제할 수 없습니다.'); Helpers::redirect('/admin/admins');
        }
        Database::exec('DELETE FROM admin_users WHERE id=?', [$id]);
        Helpers::log('admin_delete', 'admin:' . $id);
        Flash::success('삭제되었습니다.');
        Helpers::redirect('/admin/admins');
    }

    private function roles(): array { return Database::all('SELECT id,name_ko,code,level FROM admin_roles ORDER BY level'); }
    private function isSuper(int $roleId): bool { return Database::scalar('SELECT code FROM admin_roles WHERE id=?', [$roleId]) === 'superadmin'; }
    private function activeSupers(): int
    {
        return (int)Database::scalar(
            "SELECT COUNT(*) FROM admin_users a JOIN admin_roles r ON r.id=a.role_id WHERE r.code='superadmin' AND a.status='active'"
        );
    }
    private function csrf(): void { if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/admins'); } }
}
