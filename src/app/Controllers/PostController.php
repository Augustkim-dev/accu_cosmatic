<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;
use App\Storage;

/**
 * 소식(news)·프로젝트(project) 통합 컨트롤러.
 * type에 따라 권한키(posts.* / projects.*)와 URL prefix를 분기.
 */
class PostController
{
    private string $type;
    private string $perm;     // 'posts' | 'projects'
    private string $base;     // url prefix
    private string $label;

    public function __construct()
    {
        // 라우트에서 type 결정: /admin/posts → news, /admin/projects → project
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_starts_with($uri, '/admin/projects')) {
            $this->type = 'project'; $this->perm = 'projects'; $this->base = '/admin/projects'; $this->label = '프로젝트';
        } else {
            $this->type = 'news'; $this->perm = 'posts'; $this->base = '/admin/posts'; $this->label = '소식';
        }
    }

    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require($this->perm . '.view');
        $rows = Database::all('SELECT * FROM posts WHERE type=? ORDER BY id DESC', [$this->type]);
        Helpers::view('admin/posts/index', ['title' => $this->label, 'rows' => $rows, 'base' => $this->base]);
    }

    public function create(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require($this->perm . '.create');
        Helpers::view('admin/posts/form', ['title' => $this->label . ' 등록', 'item' => [], 'isEdit' => false, 'action' => $this->base, 'base' => $this->base]);
    }

    public function store(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require($this->perm . '.create');
        $this->csrf();
        $d = $this->input();
        if ($d['title_ko'] === '') { Flash::error('한국어 제목은 필수입니다.'); Helpers::redirect($this->base . '/create'); }
        $thumb = Storage::put($_FILES['thumbnail'] ?? []);
        Database::exec('INSERT INTO posts (type,title_ko,title_vi,title_en,body_ko,body_vi,body_en,thumbnail,is_active,published_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())',
            [$this->type,$d['title_ko'],$d['title_vi'],$d['title_en'],$d['body_ko'],$d['body_vi'],$d['body_en'],$thumb,$d['is_active']]);
        Helpers::log('create', $this->type . ':' . $d['title_ko']);
        Flash::success('등록되었습니다.');
        Helpers::redirect($this->base);
    }

    public function edit(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require($this->perm . '.edit');
        $p = Database::one('SELECT * FROM posts WHERE id=? AND type=?', [(int)$params['id'], $this->type]);
        if (!$p) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect($this->base); }
        Helpers::view('admin/posts/form', ['title' => $this->label . ' 수정', 'item' => $p, 'isEdit' => true, 'action' => $this->base . '/' . $p['id'], 'base' => $this->base]);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require($this->perm . '.edit');
        $this->csrf();
        $id = (int)$params['id'];
        $cur = Database::one('SELECT * FROM posts WHERE id=? AND type=?', [$id, $this->type]);
        if (!$cur) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect($this->base); }
        $d = $this->input();
        $thumb = $cur['thumbnail'];
        $new = Storage::put($_FILES['thumbnail'] ?? []);
        if ($new) { Storage::delete($cur['thumbnail']); $thumb = $new; }
        Database::exec('UPDATE posts SET title_ko=?,title_vi=?,title_en=?,body_ko=?,body_vi=?,body_en=?,thumbnail=?,is_active=? WHERE id=?',
            [$d['title_ko'],$d['title_vi'],$d['title_en'],$d['body_ko'],$d['body_vi'],$d['body_en'],$thumb,$d['is_active'],$id]);
        Helpers::log('update', $this->type . ':' . $id);
        Flash::success('수정되었습니다.');
        Helpers::redirect($this->base);
    }

    public function destroy(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require($this->perm . '.delete');
        $this->csrf();
        $id = (int)$params['id'];
        $p = Database::one('SELECT thumbnail FROM posts WHERE id=? AND type=?', [$id, $this->type]);
        if ($p) {
            Storage::delete($p['thumbnail']);
            Database::exec('DELETE FROM posts WHERE id=?', [$id]);
            Helpers::log('delete', $this->type . ':' . $id);
            Flash::success('삭제되었습니다.');
        }
        Helpers::redirect($this->base);
    }

    private function input(): array
    {
        return [
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
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect($this->base); }
    }
}
