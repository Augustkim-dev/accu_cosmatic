<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;

class StoreController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('stores.view');
        $rows = Database::all('SELECT * FROM stores ORDER BY sort,id');
        Helpers::view('admin/stores/index', ['title' => '구매처/연결정보', 'rows' => $rows]);
    }

    public function create(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('stores.create');
        Helpers::view('admin/stores/form', ['title' => '구매처 등록', 'item' => [], 'isEdit' => false, 'action' => '/admin/stores']);
    }

    public function store(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('stores.create');
        $this->csrf();
        $d = $this->input();
        if ($d['name'] === '') { Flash::error('이름은 필수입니다.'); Helpers::redirect('/admin/stores/create'); }
        Database::exec('INSERT INTO stores (name,type,url,region,lat,lng,is_active,sort) VALUES (?,?,?,?,?,?,?,?)',
            [$d['name'],$d['type'],$d['url'],$d['region'],$d['lat'],$d['lng'],$d['is_active'],$d['sort']]);
        Helpers::log('create', 'store:' . $d['name']);
        Flash::success('등록되었습니다.');
        Helpers::redirect('/admin/stores');
    }

    public function edit(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('stores.edit');
        $s = Database::one('SELECT * FROM stores WHERE id=?', [(int)$params['id']]);
        if (!$s) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/stores'); }
        Helpers::view('admin/stores/form', ['title' => '구매처 수정', 'item' => $s, 'isEdit' => true, 'action' => '/admin/stores/' . $s['id']]);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('stores.edit');
        $this->csrf();
        $id = (int)$params['id'];
        $d = $this->input();
        Database::exec('UPDATE stores SET name=?,type=?,url=?,region=?,lat=?,lng=?,is_active=?,sort=? WHERE id=?',
            [$d['name'],$d['type'],$d['url'],$d['region'],$d['lat'],$d['lng'],$d['is_active'],$d['sort'],$id]);
        Helpers::log('update', 'store:' . $id);
        Flash::success('수정되었습니다.');
        Helpers::redirect('/admin/stores');
    }

    public function destroy(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('stores.delete');
        $this->csrf();
        $id = (int)$params['id'];
        Database::exec('DELETE FROM stores WHERE id=?', [$id]);
        Helpers::log('delete', 'store:' . $id);
        Flash::success('삭제되었습니다.');
        Helpers::redirect('/admin/stores');
    }

    private function input(): array
    {
        $type = in_array($_POST['type'] ?? '', ['offline','online','sns'], true) ? $_POST['type'] : 'online';
        return [
            'name'   => trim($_POST['name'] ?? ''),
            'type'   => $type,
            'url'    => trim($_POST['url'] ?? ''),
            'region' => trim($_POST['region'] ?? ''),
            'lat'    => ($_POST['lat'] ?? '') !== '' ? (float)$_POST['lat'] : null,
            'lng'    => ($_POST['lng'] ?? '') !== '' ? (float)$_POST['lng'] : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort'   => (int)($_POST['sort'] ?? 0),
        ];
    }

    private function csrf(): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/stores'); }
    }
}
