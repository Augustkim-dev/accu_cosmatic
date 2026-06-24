<?php
namespace App\Controllers\Front;

use App\Database;
use App\Helpers;
use App\Lang;

class ProductController
{
    public function index(array $params = []): void
    {
        $brandId = ($_GET['brand'] ?? '') !== '' ? (int)$_GET['brand'] : null;
        $catId   = ($_GET['category'] ?? '') !== '' ? (int)$_GET['category'] : null;

        $where = 'p.is_active=1';
        $args  = [];
        if ($brandId) { $where .= ' AND p.brand_id=?'; $args[] = $brandId; }
        if ($catId)   { $where .= ' AND p.category_id=?'; $args[] = $catId; }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = 12;
        $off  = ($page - 1) * $per;
        $total = (int)Database::scalar("SELECT COUNT(*) FROM products p WHERE $where", $args);

        $rows = Database::all(
            "SELECT p.*, b.name_ko AS brand_name_ko, b.name_vi AS brand_name_vi, b.name_en AS brand_name_en,
                    (SELECT path FROM product_images i WHERE i.product_id=p.id ORDER BY i.sort,i.id LIMIT 1) AS thumb
               FROM products p LEFT JOIN brands b ON b.id=p.brand_id
              WHERE $where ORDER BY p.sort,p.id DESC LIMIT $per OFFSET $off",
            $args
        );

        $heading = Lang::t('all_products');
        if ($brandId) {
            $b = Database::one('SELECT * FROM brands WHERE id=?', [$brandId]);
            if ($b) $heading = Lang::pick($b, 'name');
        }

        Helpers::view('front/products', [
            'title' => $heading, 'rows' => $rows, 'heading' => $heading,
            'page' => $page, 'pages' => max(1, (int)ceil($total / $per)),
            'brandId' => $brandId, 'catId' => $catId, 'total' => $total,
        ], 'front/layout');
    }

    public function show(array $params = []): void
    {
        $p = Database::one(
            'SELECT p.*, b.name_ko AS brand_name_ko, b.name_vi AS brand_name_vi, b.name_en AS brand_name_en, b.id AS bid
               FROM products p LEFT JOIN brands b ON b.id=p.brand_id
              WHERE p.id=? AND p.is_active=1',
            [(int)$params['id']]
        );
        if (!$p) {
            http_response_code(404);
            Helpers::view('front/notfound', ['title' => 'Not found'], 'front/layout');
            return;
        }
        $images = Database::all('SELECT * FROM product_images WHERE product_id=? ORDER BY sort,id', [$p['id']]);
        $related = Database::all(
            "SELECT p.*, (SELECT path FROM product_images i WHERE i.product_id=p.id ORDER BY i.sort,i.id LIMIT 1) AS thumb
               FROM products p WHERE p.is_active=1 AND p.brand_id=? AND p.id<>? ORDER BY p.sort,p.id DESC LIMIT 4",
            [$p['brand_id'], $p['id']]
        );
        $stores = Database::all(
            "SELECT s.* FROM stores s JOIN product_stores ps ON ps.store_id=s.id
              WHERE ps.product_id=? AND s.is_active=1 ORDER BY s.sort,s.id",
            [$p['id']]
        );

        Helpers::view('front/product', [
            'title' => Lang::pick($p, 'name'),
            'metaDescription' => Lang::pick($p, 'summary') ?: Lang::pick($p, 'name'),
            'ogImage' => $images[0]['path'] ?? '',
            'p' => $p, 'images' => $images, 'related' => $related, 'stores' => $stores,
        ], 'front/layout');
    }
}
