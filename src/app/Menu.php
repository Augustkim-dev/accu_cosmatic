<?php
namespace App;

/**
 * 관리자 메뉴 정의.
 * 'perm' 권한을 가진 역할에게만 사이드바에 노출된다(동적 메뉴).
 * M1에서는 라우트가 아직 없는 항목은 '#'(준비중)으로 두되, 권한 게이트는 동작.
 */
class Menu
{
    public static function items(): array
    {
        return [
            ['label' => '대시보드',   'url' => '/admin',              'perm' => 'dashboard.view'],
            ['label' => '제품관리',   'url' => '/admin/products',     'perm' => 'products.view'],
            ['label' => '브랜드관리', 'url' => '/admin/brands',       'perm' => 'brands.view'],
            ['label' => '카테고리',   'url' => '/admin/categories',   'perm' => 'categories.view'],
            ['label' => '구매처/연결','url' => '/admin/stores',                   'perm' => 'stores.view'],
            ['label' => '주문관리',   'url' => '/admin/orders',       'perm' => 'orders.view'],
            ['label' => '문의관리',   'url' => '/admin/inquiries',                   'perm' => 'inquiries.view'],
            ['label' => '콘텐츠/페이지','url' => '/admin/pages',                 'perm' => 'pages.view'],
            ['label' => '소식',       'url' => '/admin/posts',                   'perm' => 'posts.view'],
            ['label' => '프로젝트',   'url' => '/admin/projects',                   'perm' => 'projects.view'],
            ['label' => '회원관리',   'url' => '/admin/members',      'perm' => 'members.view'],
            ['label' => '통계/리포트','url' => '/admin/reports',                   'perm' => 'reports.view'],
            ['label' => '관리자 계정','url' => '/admin/admins',                   'perm' => 'admins.view'],
            ['label' => '권한관리',   'url' => '/admin/roles',                   'perm' => 'roles.view'],
            ['label' => '입금계좌',   'url' => '/admin/bank-accounts','perm' => 'settings.view'],
            ['label' => '시스템 설정','url' => '/admin/settings',                   'perm' => 'settings.view'],
            ['label' => '감사 로그',  'url' => '/admin/logs',                   'perm' => 'logs.view'],
        ];
    }

    /** 현재 로그인 관리자가 볼 수 있는 메뉴만 */
    public static function visible(): array
    {
        return array_values(array_filter(self::items(), fn($m) => Rbac::can($m['perm'])));
    }
}
