<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;
use App\Storage;

class BrandController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('brands.view');
        $brands = Database::all('SELECT * FROM brands ORDER BY sort ASC, id ASC');
        Helpers::view('admin/brands/index', ['title' => '브랜드관리', 'brands' => $brands]);
    }

    public function create(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('brands.create');
        Helpers::view('admin/brands/form', [
            'title' => '브랜드 등록', 'item' => [], 'isEdit' => false,
            'action' => '/admin/brands',
        ]);
    }

    public function store(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('brands.create');
        $this->guardCsrf();

        $d = $this->input();
        if ($d['code'] === '' || $d['name_ko'] === '') {
            Flash::error('코드와 한국어 이름은 필수입니다.');
            Helpers::redirect('/admin/brands/create');
        }
        $logo = Storage::put($_FILES['logo'] ?? []);
        Database::exec(
            'INSERT INTO brands (code,name_ko,name_vi,name_en,logo_path,story_ko,story_vi,story_en,sort,is_active)
             VALUES (?,?,?,?,?,?,?,?,?,?)',
            [$d['code'],$d['name_ko'],$d['name_vi'],$d['name_en'],$logo,
             $d['story_ko'],$d['story_vi'],$d['story_en'],$d['sort'],$d['is_active']]
        );
        Helpers::log('create', 'brand:' . $d['code']);
        Flash::success('브랜드가 등록되었습니다.');
        Helpers::redirect('/admin/brands');
    }

    public function edit(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('brands.edit');
        $b = Database::one('SELECT * FROM brands WHERE id=?', [(int)$params['id']]);
        if (!$b) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/brands'); }
        Helpers::view('admin/brands/form', [
            'title' => '브랜드 수정', 'item' => $b, 'isEdit' => true,
            'action' => '/admin/brands/' . $b['id'],
        ]);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('brands.edit');
        $this->guardCsrf();
        $id = (int)$params['id'];
        $b = Database::one('SELECT * FROM brands WHERE id=?', [$id]);
        if (!$b) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/brands'); }

        $d = $this->input();
        $logo = $b['logo_path'];
        $new = Storage::put($_FILES['logo'] ?? []);
        if ($new) { Storage::delete($b['logo_path']); $logo = $new; }

        Database::exec(
            'UPDATE brands SET code=?,name_ko=?,name_vi=?,name_en=?,logo_path=?,
             story_ko=?,story_vi=?,story_en=?,sort=?,is_active=? WHERE id=?',
            [$d['code'],$d['name_ko'],$d['name_vi'],$d['name_en'],$logo,
             $d['story_ko'],$d['story_vi'],$d['story_en'],$d['sort'],$d['is_active'],$id]
        );
        Helpers::log('update', 'brand:' . $id);
        Flash::success('수정되었습니다.');
        Helpers::redirect('/admin/brands');
    }

    public function destroy(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('brands.delete');
        $this->guardCsrf();
        $id = (int)$params['id'];
        $b = Database::one('SELECT * FROM brands WHERE id=?', [$id]);
        if ($b) {
            Storage::delete($b['logo_path']);
            Database::exec('DELETE FROM brands WHERE id=?', [$id]);
            Helpers::log('delete', 'brand:' . $id);
            Flash::success('삭제되었습니다.');
        }
        Helpers::redirect('/admin/brands');
    }

    private function input(): array
    {
        return [
            'code'     => trim($_POST['code'] ?? ''),
            'name_ko'  => trim($_POST['name_ko'] ?? ''),
            'name_vi'  => trim($_POST['name_vi'] ?? ''),
            'name_en'  => trim($_POST['name_en'] ?? ''),
            'story_ko' => trim($_POST['story_ko'] ?? ''),
            'story_vi' => trim($_POST['story_vi'] ?? ''),
            'story_en' => trim($_POST['story_en'] ?? ''),
            'sort'     => (int)($_POST['sort'] ?? 0),
            'is_active'=> isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    private function guardCsrf(): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다. 다시 시도하세요.'); Helpers::redirect('/admin/brands'); }
    }
}
