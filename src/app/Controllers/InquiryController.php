<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;

class InquiryController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('inquiries.view');
        $rows = Database::all('SELECT * FROM inquiries ORDER BY id DESC LIMIT 300');
        Helpers::view('admin/inquiries/index', ['title' => '문의관리', 'rows' => $rows]);
    }

    public function show(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('inquiries.view');
        $q = Database::one('SELECT * FROM inquiries WHERE id=?', [(int)$params['id']]);
        if (!$q) { Flash::error('문의를 찾을 수 없습니다.'); Helpers::redirect('/admin/inquiries'); }
        Helpers::view('admin/inquiries/show', ['title' => '문의 상세', 'q' => $q]);
    }

    /** 답변 + 상태 변경 */
    public function reply(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('inquiries.reply');
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/inquiries'); }
        $id = (int)$params['id'];
        $reply  = trim($_POST['reply'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['received','processing','done'], true) ? $_POST['status'] : 'processing';
        Database::exec('UPDATE inquiries SET reply=?, status=? WHERE id=?', [$reply, $status, $id]);
        Helpers::log('inquiry_reply', 'inquiry:' . $id);
        Flash::success('저장되었습니다.');
        Helpers::redirect('/admin/inquiries/' . $id);
    }

    public function destroy(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('inquiries.delete');
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/inquiries'); }
        $id = (int)$params['id'];
        Database::exec('DELETE FROM inquiries WHERE id=?', [$id]);
        Helpers::log('inquiry_delete', 'inquiry:' . $id);
        Flash::success('삭제되었습니다.');
        Helpers::redirect('/admin/inquiries');
    }
}
