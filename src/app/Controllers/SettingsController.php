<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;

class SettingsController
{
    /** 편집 가능한 설정 키 화이트리스트 */
    private const KEYS = ['site_name','contact_email','contact_phone','default_lang_front','default_lang_admin','currency','sns_instagram','sns_tiktok','sns_zalo'];

    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('settings.view');
        $map = [];
        foreach (Database::all('SELECT skey,svalue FROM settings') as $r) $map[$r['skey']] = $r['svalue'];
        Helpers::view('admin/settings/index', ['title' => '시스템 설정', 'map' => $map, 'keys' => self::KEYS]);
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('settings.edit');
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/settings'); }
        foreach (self::KEYS as $k) {
            if (!array_key_exists($k, $_POST)) continue;
            $v = trim((string)$_POST[$k]);
            // upsert
            if (Database::one('SELECT id FROM settings WHERE skey=?', [$k])) {
                Database::exec('UPDATE settings SET svalue=? WHERE skey=?', [$v, $k]);
            } else {
                Database::exec('INSERT INTO settings (skey,svalue) VALUES (?,?)', [$k, $v]);
            }
        }
        Helpers::log('settings_update');
        Flash::success('설정이 저장되었습니다.');
        Helpers::redirect('/admin/settings');
    }
}
