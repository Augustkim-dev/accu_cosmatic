<?php
namespace App\Controllers\Front;

use App\Database;
use App\Helpers;
use App\Lang;

class HomeController
{
    public function index(array $params = []): void
    {
        $brands = Database::all('SELECT * FROM brands WHERE is_active=1 ORDER BY sort,id');
        $best   = $this->products('AND p.is_best=1', 'p.sort,p.id', 8);
        $latest = $this->products('', 'p.id DESC', 8);

        Helpers::view('front/home', [
            'title'  => null,
            'brands' => $brands, 'best' => $best, 'latest' => $latest,
        ], 'front/layout');
    }

    /** 언어 전환 후 이전 페이지로 */
    public function lang(array $params = []): void
    {
        Lang::set($params['code'] ?? Lang::FALLBACK);
        $back = $_SERVER['HTTP_REFERER'] ?? '/';
        // 외부 도메인 방지: 경로만 허용
        $path = parse_url($back, PHP_URL_PATH) ?: '/';
        Helpers::redirect($path);
    }

    private function products(string $extra, string $order, int $limit): array
    {
        return Database::all(
            "SELECT p.*, b.name_ko AS brand_name_ko, b.name_vi AS brand_name_vi, b.name_en AS brand_name_en,
                    (SELECT path FROM product_images i WHERE i.product_id=p.id ORDER BY i.sort,i.id LIMIT 1) AS thumb
               FROM products p LEFT JOIN brands b ON b.id=p.brand_id
              WHERE p.is_active=1 $extra
              ORDER BY $order LIMIT $limit"
        );
    }
}
