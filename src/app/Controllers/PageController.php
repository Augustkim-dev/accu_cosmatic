<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;

class PageController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('pages.view');
        $rows = Database::all('SELECT id,slug,title_ko,is_active FROM pages ORDER BY id');
        Helpers::view('admin/pages/index', ['title' => '콘텐츠/페이지', 'rows' => $rows]);
    }

    public function create(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('pages.edit');
        Helpers::view('admin/pages/form', ['title' => '페이지 등록', 'item' => [], 'isEdit' => false, 'action' => '/admin/pages']);
    }

    public function store(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('pages.edit');
        $this->csrf();
        $d = $this->input();
        if ($d['slug'] === '' || $d['title_ko'] === '') { Flash::error('slug와 한국어 제목은 필수입니다.'); Helpers::redirect('/admin/pages/create'); }
        if (Database::one('SELECT id FROM pages WHERE slug=?', [$d['slug']])) { Flash::error('이미 존재하는 slug입니다.'); Helpers::redirect('/admin/pages/create'); }
        Database::exec('INSERT INTO pages (slug,title_ko,title_vi,title_en,body_ko,body_vi,body_en,is_active) VALUES (?,?,?,?,?,?,?,?)',
            [$d['slug'],$d['title_ko'],$d['title_vi'],$d['title_en'],$d['body_ko'],$d['body_vi'],$d['body_en'],$d['is_active']]);
        Helpers::log('create', 'page:' . $d['slug']);
        Flash::success('등록되었습니다.');
        Helpers::redirect('/admin/pages');
    }

    public function edit(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('pages.edit');
        $p = Database::one('SELECT * FROM pages WHERE id=?', [(int)$params['id']]);
        if (!$p) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/pages'); }
        Helpers::view('admin/pages/form', ['title' => '페이지 수정', 'item' => $p, 'isEdit' => true, 'action' => '/admin/pages/' . $p['id']]);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('pages.edit');
        $this->csrf();
        $id = (int)$params['id'];
        $d = $this->input();
        Database::exec('UPDATE pages SET title_ko=?,title_vi=?,title_en=?,body_ko=?,body_vi=?,body_en=?,is_active=? WHERE id=?',
            [$d['title_ko'],$d['title_vi'],$d['title_en'],$d['body_ko'],$d['body_vi'],$d['body_en'],$d['is_active'],$id]);
        Helpers::log('update', 'page:' . $id);
        Flash::success('수정되었습니다.');
        Helpers::redirect('/admin/pages');
    }

    public function destroy(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('pages.edit');
        $this->csrf();
        $id = (int)$params['id'];
        Database::exec('DELETE FROM pages WHERE id=?', [$id]);
        Helpers::log('delete', 'page:' . $id);
        Flash::success('삭제되었습니다.');
        Helpers::redirect('/admin/pages');
    }

    private function input(): array
    {
        return [
            'slug'     => preg_replace('/[^a-z0-9\-_]/', '', strtolower(trim($_POST['slug'] ?? ''))),
            'title_ko' => trim($_POST['title_ko'] ?? ''),
            'title_vi' => trim($_POST['title_vi'] ?? ''),
            'title_en' => trim($_POST['title_en'] ?? ''),
            'body_ko'  => $_POST['body_ko'] ?? '',
            'body_vi'  => $_POST['body_vi'] ?? '',
            'body_en'  => $_POST['body_en'] ?? '',
            'is_active'=> isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    private function csrf(): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/pages'); }
    }
}
