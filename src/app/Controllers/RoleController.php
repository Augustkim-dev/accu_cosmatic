<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;

class RoleController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('roles.view');

        $roles = Database::all('SELECT * FROM admin_roles ORDER BY level');
        $perms = Database::all('SELECT * FROM permissions ORDER BY FIELD(module,
            "dashboard","products","brands","categories","stores","orders","inquiries",
            "pages","posts","projects","members","reports","admins","roles","settings","logs"), id');

        $map = [];   // [role_id][permission_id] = true
        foreach (Database::all('SELECT * FROM role_permissions') as $x) {
            $map[$x['role_id']][$x['permission_id']] = true;
        }
        // 모듈별 그룹
        $grouped = [];
        foreach ($perms as $p) $grouped[$p['module']][] = $p;

        Helpers::view('admin/roles/index', [
            'title' => '권한관리', 'roles' => $roles, 'grouped' => $grouped, 'map' => $map,
        ]);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('roles.edit');
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/roles'); }

        // 최종관리자(superadmin)는 항상 전체 권한 → 편집 대상에서 제외(잠금)
        $editable = Database::all("SELECT id FROM admin_roles WHERE code <> 'superadmin'");
        $sent = $_POST['perm'] ?? [];

        foreach ($editable as $r) {
            $rid = (int)$r['id'];
            Database::exec('DELETE FROM role_permissions WHERE role_id=?', [$rid]);
            $ids = $sent[$rid] ?? [];
            if (is_array($ids)) {
                foreach ($ids as $pid) {
                    $pid = (int)$pid;
                    if ($pid > 0) Database::exec('INSERT IGNORE INTO role_permissions (role_id,permission_id) VALUES (?,?)', [$rid, $pid]);
                }
            }
        }
        Helpers::log('roles_update');
        Flash::success('권한이 저장되었습니다. (해당 역할 관리자는 다음 로그인 시 반영됩니다)');
        Helpers::redirect('/admin/roles');
    }
}
