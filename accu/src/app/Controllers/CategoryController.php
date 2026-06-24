<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;

class CategoryController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('categories.view');
        $cats = Database::all(
            'SELECT c.*, p.name_ko AS parent_name
               FROM categories c LEFT JOIN categories p ON p.id=c.parent_id
              ORDER BY c.sort ASC, c.id ASC'
        );
        Helpers::view('admin/categories/index', ['title' => '카테고리', 'cats' => $cats]);
    }

    public function create(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('categories.create');
        $parents = Database::all('SELECT id,name_ko FROM categories ORDER BY sort,id');
        Helpers::view('admin/categories/form', [
            'title' => '카테고리 등록', 'item' => [], 'isEdit' => false,
            'action' => '/admin/categories', 'parents' => $parents,
        ]);
    }

    public function store(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('categories.create');
        $this->guardCsrf();
        $d = $this->input();
        if ($d['name_ko'] === '') { Flash::error('한국어 이름은 필수입니다.'); Helpers::redirect('/admin/categories/create'); }
        Database::exec(
            'INSERT INTO categories (parent_id,name_ko,name_vi,name_en,sort,is_active) VALUES (?,?,?,?,?,?)',
            [$d['parent_id'],$d['name_ko'],$d['name_vi'],$d['name_en'],$d['sort'],$d['is_active']]
        );
        Helpers::log('create', 'category:' . $d['name_ko']);
        Flash::success('카테고리가 등록되었습니다.');
        Helpers::redirect('/admin/categories');
    }

    public function edit(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('categories.edit');
        $c = Database::one('SELECT * FROM categories WHERE id=?', [(int)$params['id']]);
        if (!$c) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/categories'); }
        $parents = Database::all('SELECT id,name_ko FROM categories WHERE id<>? ORDER BY sort,id', [$c['id']]);
        Helpers::view('admin/categories/form', [
            'title' => '카테고리 수정', 'item' => $c, 'isEdit' => true,
            'action' => '/admin/categories/' . $c['id'], 'parents' => $parents,
        ]);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('categories.edit');
        $this->guardCsrf();
        $id = (int)$params['id'];
        if (!Database::one('SELECT id FROM categories WHERE id=?', [$id])) {
            Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/categories');
        }
        $d = $this->input();
        Database::exec(
            'UPDATE categories SET parent_id=?,name_ko=?,name_vi=?,name_en=?,sort=?,is_active=? WHERE id=?',
            [$d['parent_id'],$d['name_ko'],$d['name_vi'],$d['name_en'],$d['sort'],$d['is_active'],$id]
        );
        Helpers::log('update', 'category:' . $id);
        Flash::success('수정되었습니다.');
        Helpers::redirect('/admin/categories');
    }

    public function destroy(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('categories.delete');
        $this->guardCsrf();
        $id = (int)$params['id'];
        Database::exec('DELETE FROM categories WHERE id=?', [$id]);
        Helpers::log('delete', 'category:' . $id);
        Flash::success('삭제되었습니다.');
        Helpers::redirect('/admin/categories');
    }

    private function input(): array
    {
        $parent = ($_POST['parent_id'] ?? '') !== '' ? (int)$_POST['parent_id'] : null;
        return [
            'parent_id'=> $parent,
            'name_ko'  => trim($_POST['name_ko'] ?? ''),
            'name_vi'  => trim($_POST['name_vi'] ?? ''),
            'name_en'  => trim($_POST['name_en'] ?? ''),
            'sort'     => (int)($_POST['sort'] ?? 0),
            'is_active'=> isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    private function guardCsrf(): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/categories'); }
    }
}
