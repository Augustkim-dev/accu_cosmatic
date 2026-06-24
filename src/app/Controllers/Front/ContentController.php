<?php
namespace App\Controllers\Front;

use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Lang;
use App\MemberAuth;

class ContentController
{
    /** CMS 페이지: /page/{slug} */
    public function page(array $params = []): void
    {
        $p = Database::one('SELECT * FROM pages WHERE slug=? AND is_active=1', [$params['slug'] ?? '']);
        if (!$p) {
            http_response_code(404);
            Helpers::view('front/notfound', ['title' => 'Not found'], 'front/layout');
            return;
        }
        Helpers::view('front/page', [
            'title' => Lang::pick($p, 'title'),
            'pageTitle' => Lang::pick($p, 'title'),
            'body' => Lang::pick($p, 'body'),
        ], 'front/layout');
    }

    /** 소식 목록: /news */
    public function newsList(array $params = []): void
    {
        $rows = Database::all("SELECT * FROM posts WHERE type='news' AND is_active=1 ORDER BY COALESCE(published_at,created_at) DESC, id DESC");
        Helpers::view('front/news', ['title' => Lang::t('news'), 'rows' => $rows], 'front/layout');
    }

    /** 소식 상세: /news/{id} */
    public function newsShow(array $params = []): void
    {
        $p = Database::one("SELECT * FROM posts WHERE id=? AND type='news' AND is_active=1", [(int)$params['id']]);
        if (!$p) {
            http_response_code(404);
            Helpers::view('front/notfound', ['title' => 'Not found'], 'front/layout');
            return;
        }
        Helpers::view('front/news_detail', ['title' => Lang::pick($p, 'title'), 'p' => $p], 'front/layout');
    }

    /** 문의 폼: /contact */
    public function contactForm(array $params = []): void
    {
        $m = MemberAuth::user();
        Helpers::view('front/contact', ['title' => Lang::t('contact'), 'member' => $m], 'front/layout');
    }

    public function contactSubmit(array $params = []): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/contact'); }
        $name    = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($name === '' || $contact === '' || $message === '') {
            Flash::error('모든 항목을 입력하세요. / Please fill in all fields.');
            Helpers::redirect('/contact');
        }
        Database::exec(
            'INSERT INTO inquiries (member_id,name,contact,message,status) VALUES (?,?,?,?,?)',
            [MemberAuth::id(), $name, $contact, $message, 'received']
        );
        Flash::success(Lang::t('inquiry_sent'));
        Helpers::redirect('/contact');
    }
}
