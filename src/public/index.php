<?php
declare(strict_types=1);

/* ---------- 부트스트랩 ---------- */
session_start();

$GLOBALS['config'] = require __DIR__ . '/../config/config.php';

// App\ → src/app/ 오토로더
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
        $rel  = substr($class, strlen($prefix));
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', $rel) . '.php';
        if (is_file($file)) require $file;
    }
});

use App\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\AccountController;
use App\Controllers\BrandController;
use App\Controllers\CategoryController;
use App\Controllers\ProductController;
use App\Controllers\Front\HomeController as FrontHome;
use App\Controllers\Front\ProductController as FrontProduct;
use App\Controllers\Front\BrandController as FrontBrand;
use App\Controllers\Front\MemberController as FrontMember;
use App\Controllers\Front\CartController as FrontCart;
use App\Controllers\Front\CheckoutController as FrontCheckout;
use App\Controllers\Front\ContentController as FrontContent;
use App\Controllers\Front\SeoController as FrontSeo;
use App\Controllers\OrderController;
use App\Controllers\MemberAdminController;
use App\Controllers\BankAccountController;
use App\Controllers\StoreController;
use App\Controllers\InquiryController;
use App\Controllers\PageController;
use App\Controllers\PostController;
use App\Controllers\SettingsController;
use App\Controllers\ReportController;
use App\Controllers\LogController;
use App\Controllers\AdminUserController;
use App\Controllers\RoleController;

/* ---------- 요청 파싱 ---------- */
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$uri    = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/* 헬스체크 (세션 미사용) */
if ($method === 'GET' && $uri === '/health') {
    header('Content-Type: text/plain'); echo 'ok'; exit;
}

/* ---------- 라우트 ---------- */
$r = new Router();

/* ===== 프론트 (공개) ===== */
$r->get('/',                  [FrontHome::class, 'index']);
$r->get('/lang/{code}',       [FrontHome::class, 'lang']);
$r->get('/products',          [FrontProduct::class, 'index']);
$r->get('/products/{id}',     [FrontProduct::class, 'show']);
$r->get('/brands',            [FrontBrand::class, 'index']);

// 회원
$r->get('/register',  [FrontMember::class, 'registerForm']);
$r->post('/register', [FrontMember::class, 'register']);
$r->get('/login',     [FrontMember::class, 'loginForm']);
$r->post('/login',    [FrontMember::class, 'login']);
$r->get('/logout',    [FrontMember::class, 'logout']);
$r->get('/mypage',            [FrontMember::class, 'mypage']);
$r->get('/mypage/orders/{id}',[FrontMember::class, 'order']);

// 장바구니 / 주문
$r->get('/cart',         [FrontCart::class, 'index']);
$r->post('/cart/add',    [FrontCart::class, 'add']);
$r->post('/cart/update', [FrontCart::class, 'update']);
$r->post('/cart/remove', [FrontCart::class, 'remove']);
$r->get('/checkout',     [FrontCheckout::class, 'form']);
$r->post('/checkout',    [FrontCheckout::class, 'submit']);
$r->get('/order/complete/{orderNo}', [FrontCheckout::class, 'complete']);

// 콘텐츠/소식/문의
$r->get('/news',          [FrontContent::class, 'newsList']);
$r->get('/news/{id}',     [FrontContent::class, 'newsShow']);
$r->get('/contact',       [FrontContent::class, 'contactForm']);
$r->post('/contact',      [FrontContent::class, 'contactSubmit']);
$r->get('/page/{slug}',   [FrontContent::class, 'page']);
$r->get('/sitemap.xml',   [FrontSeo::class, 'sitemap']);
$r->get('/robots.txt',    [FrontSeo::class, 'robots']);

/* ===== 관리자 ===== */
$r->get('/admin',           [DashboardController::class, 'index']);
$r->get('/admin/login',     [AuthController::class, 'loginForm']);
$r->post('/admin/login',    [AuthController::class, 'login']);
$r->get('/admin/logout',    [AuthController::class, 'logout']);
$r->post('/admin/logout',   [AuthController::class, 'logout']);

// 내 계정
$r->get('/admin/account/password',  [AccountController::class, 'form']);
$r->post('/admin/account/password', [AccountController::class, 'update']);

// 브랜드
$r->get('/admin/brands',              [BrandController::class, 'index']);
$r->get('/admin/brands/create',       [BrandController::class, 'create']);
$r->post('/admin/brands',             [BrandController::class, 'store']);
$r->get('/admin/brands/{id}/edit',    [BrandController::class, 'edit']);
$r->post('/admin/brands/{id}',        [BrandController::class, 'update']);
$r->post('/admin/brands/{id}/delete', [BrandController::class, 'destroy']);

// 카테고리
$r->get('/admin/categories',              [CategoryController::class, 'index']);
$r->get('/admin/categories/create',       [CategoryController::class, 'create']);
$r->post('/admin/categories',             [CategoryController::class, 'store']);
$r->get('/admin/categories/{id}/edit',    [CategoryController::class, 'edit']);
$r->post('/admin/categories/{id}',        [CategoryController::class, 'update']);
$r->post('/admin/categories/{id}/delete', [CategoryController::class, 'destroy']);

// 제품
$r->get('/admin/products',              [ProductController::class, 'index']);
$r->get('/admin/products/create',       [ProductController::class, 'create']);
$r->post('/admin/products',             [ProductController::class, 'store']);
$r->get('/admin/products/{id}/edit',    [ProductController::class, 'edit']);
$r->post('/admin/products/{id}',        [ProductController::class, 'update']);
$r->post('/admin/products/{id}/delete', [ProductController::class, 'destroy']);
$r->post('/admin/products/{id}/images/{imageId}/delete', [ProductController::class, 'deleteImage']);

// 주문관리
$r->get('/admin/orders',               [OrderController::class, 'index']);
$r->get('/admin/orders/export',        [OrderController::class, 'export']);
$r->get('/admin/orders/{id}',          [OrderController::class, 'show']);
$r->post('/admin/orders/{id}/confirm', [OrderController::class, 'confirm']);
$r->post('/admin/orders/{id}/status',  [OrderController::class, 'status']);
$r->post('/admin/orders/{id}/cancel',  [OrderController::class, 'cancel']);

// 회원관리
$r->get('/admin/members',           [MemberAdminController::class, 'index']);
$r->get('/admin/members/export',    [MemberAdminController::class, 'export']);
$r->get('/admin/members/{id}/edit', [MemberAdminController::class, 'edit']);
$r->post('/admin/members/{id}',     [MemberAdminController::class, 'update']);

// 입금계좌 (설정 권한)
$r->get('/admin/bank-accounts',              [BankAccountController::class, 'index']);
$r->get('/admin/bank-accounts/create',       [BankAccountController::class, 'create']);
$r->post('/admin/bank-accounts',             [BankAccountController::class, 'store']);
$r->get('/admin/bank-accounts/{id}/edit',    [BankAccountController::class, 'edit']);
$r->post('/admin/bank-accounts/{id}',        [BankAccountController::class, 'update']);
$r->post('/admin/bank-accounts/{id}/delete', [BankAccountController::class, 'destroy']);

// 구매처
$r->get('/admin/stores',              [StoreController::class, 'index']);
$r->get('/admin/stores/create',       [StoreController::class, 'create']);
$r->post('/admin/stores',             [StoreController::class, 'store']);
$r->get('/admin/stores/{id}/edit',    [StoreController::class, 'edit']);
$r->post('/admin/stores/{id}',        [StoreController::class, 'update']);
$r->post('/admin/stores/{id}/delete', [StoreController::class, 'destroy']);

// 문의
$r->get('/admin/inquiries',           [InquiryController::class, 'index']);
$r->get('/admin/inquiries/{id}',      [InquiryController::class, 'show']);
$r->post('/admin/inquiries/{id}/reply',  [InquiryController::class, 'reply']);
$r->post('/admin/inquiries/{id}/delete', [InquiryController::class, 'destroy']);

// 페이지
$r->get('/admin/pages',              [PageController::class, 'index']);
$r->get('/admin/pages/create',       [PageController::class, 'create']);
$r->post('/admin/pages',             [PageController::class, 'store']);
$r->get('/admin/pages/{id}/edit',    [PageController::class, 'edit']);
$r->post('/admin/pages/{id}',        [PageController::class, 'update']);
$r->post('/admin/pages/{id}/delete', [PageController::class, 'destroy']);

// 소식 (posts type=news)
$r->get('/admin/posts',              [PostController::class, 'index']);
$r->get('/admin/posts/create',       [PostController::class, 'create']);
$r->post('/admin/posts',             [PostController::class, 'store']);
$r->get('/admin/posts/{id}/edit',    [PostController::class, 'edit']);
$r->post('/admin/posts/{id}',        [PostController::class, 'update']);
$r->post('/admin/posts/{id}/delete', [PostController::class, 'destroy']);

// 프로젝트 (posts type=project, 동일 컨트롤러)
$r->get('/admin/projects',              [PostController::class, 'index']);
$r->get('/admin/projects/create',       [PostController::class, 'create']);
$r->post('/admin/projects',             [PostController::class, 'store']);
$r->get('/admin/projects/{id}/edit',    [PostController::class, 'edit']);
$r->post('/admin/projects/{id}',        [PostController::class, 'update']);
$r->post('/admin/projects/{id}/delete', [PostController::class, 'destroy']);

// 설정 / 통계 / 로그
$r->get('/admin/settings',  [SettingsController::class, 'index']);
$r->post('/admin/settings', [SettingsController::class, 'update']);
$r->get('/admin/reports',   [ReportController::class, 'index']);
$r->get('/admin/logs',      [LogController::class, 'index']);

// 관리자 계정
$r->get('/admin/admins',              [AdminUserController::class, 'index']);
$r->get('/admin/admins/create',       [AdminUserController::class, 'create']);
$r->post('/admin/admins',             [AdminUserController::class, 'store']);
$r->get('/admin/admins/{id}/edit',    [AdminUserController::class, 'edit']);
$r->post('/admin/admins/{id}',        [AdminUserController::class, 'update']);
$r->post('/admin/admins/{id}/delete', [AdminUserController::class, 'destroy']);

// 권한 매트릭스
$r->get('/admin/roles',  [RoleController::class, 'index']);
$r->post('/admin/roles', [RoleController::class, 'update']);

if ($r->dispatch($method, $uri)) {
    exit;
}

/* ---------- 404 ---------- */
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><meta charset="utf-8"><title>404</title>'
   . '<div style="font-family:sans-serif;max-width:480px;margin:80px auto;text-align:center">'
   . '<h1 style="font-size:48px;margin:0">404</h1><p>페이지를 찾을 수 없습니다.</p>'
   . '<p><a href="/admin">관리자로 이동</a></p></div>';
