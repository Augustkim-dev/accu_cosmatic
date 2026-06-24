<?php
namespace App\Controllers\Front;

use App\Database;
use App\Helpers;
use App\Lang;

class BrandController
{
    public function index(array $params = []): void
    {
        $brands = Database::all(
            'SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id=b.id AND p.is_active=1) AS cnt
               FROM brands b WHERE b.is_active=1 ORDER BY b.sort,b.id'
        );
        Helpers::view('front/brands', [
            'title' => Lang::t('brands'), 'brands' => $brands,
        ], 'front/layout');
    }
}
