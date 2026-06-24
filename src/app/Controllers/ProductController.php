<?php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Rbac;
use App\Storage;

class ProductController
{
    public function index(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('products.view');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $per  = 20;
        $off  = ($page - 1) * $per;
        $total = (int)Database::scalar('SELECT COUNT(*) FROM products');
        $rows = Database::all(
            'SELECT p.*, b.name_ko AS brand_name,
                    (SELECT path FROM product_images i WHERE i.product_id=p.id ORDER BY i.sort,i.id LIMIT 1) AS thumb
               FROM products p LEFT JOIN brands b ON b.id=p.brand_id
              ORDER BY p.sort ASC, p.id DESC
              LIMIT ' . $per . ' OFFSET ' . $off
        );
        Helpers::view('admin/products/index', [
            'title' => '제품관리', 'rows' => $rows,
            'page' => $page, 'pages' => max(1, (int)ceil($total / $per)), 'total' => $total,
        ]);
    }

    public function create(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('products.create');
        Helpers::view('admin/products/form', $this->formData([], false, '/admin/products'));
    }

    public function store(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('products.create');
        $this->guardCsrf();
        $d = $this->input();
        if ($d['name_ko'] === '') { Flash::error('한국어 제품명은 필수입니다.'); Helpers::redirect('/admin/products/create'); }

        $id = Database::exec(
            'INSERT INTO products
              (brand_id,category_id,name_ko,name_vi,name_en,summary_ko,summary_vi,summary_en,
               description_ko,description_vi,description_en,price,is_active,is_best,sort)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$d['brand_id'],$d['category_id'],$d['name_ko'],$d['name_vi'],$d['name_en'],
             $d['summary_ko'],$d['summary_vi'],$d['summary_en'],
             $d['description_ko'],$d['description_vi'],$d['description_en'],
             $d['price'],$d['is_active'],$d['is_best'],$d['sort']]
        );
        $this->saveImages((int)$id);
        $this->syncStores((int)$id);
        Helpers::log('create', 'product:' . $id);
        Flash::success('제품이 등록되었습니다.');
        Helpers::redirect('/admin/products/' . $id . '/edit');
    }

    public function edit(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('products.edit');
        $p = Database::one('SELECT * FROM products WHERE id=?', [(int)$params['id']]);
        if (!$p) { Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/products'); }
        Helpers::view('admin/products/form', $this->formData($p, true, '/admin/products/' . $p['id']));
    }

    public function update(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('products.edit');
        $this->guardCsrf();
        $id = (int)$params['id'];
        if (!Database::one('SELECT id FROM products WHERE id=?', [$id])) {
            Flash::error('대상을 찾을 수 없습니다.'); Helpers::redirect('/admin/products');
        }
        $d = $this->input();
        Database::exec(
            'UPDATE products SET brand_id=?,category_id=?,name_ko=?,name_vi=?,name_en=?,
              summary_ko=?,summary_vi=?,summary_en=?,description_ko=?,description_vi=?,description_en=?,
              price=?,is_active=?,is_best=?,sort=? WHERE id=?',
            [$d['brand_id'],$d['category_id'],$d['name_ko'],$d['name_vi'],$d['name_en'],
             $d['summary_ko'],$d['summary_vi'],$d['summary_en'],
             $d['description_ko'],$d['description_vi'],$d['description_en'],
             $d['price'],$d['is_active'],$d['is_best'],$d['sort'],$id]
        );
        $this->saveImages($id);   // 새로 업로드한 이미지 추가
        $this->syncStores($id);
        Helpers::log('update', 'product:' . $id);
        Flash::success('수정되었습니다.');
        Helpers::redirect('/admin/products/' . $id . '/edit');
    }

    public function destroy(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('products.delete');
        $this->guardCsrf();
        $id = (int)$params['id'];
        foreach (Database::all('SELECT path FROM product_images WHERE product_id=?', [$id]) as $img) {
            Storage::delete($img['path']);
        }
        Database::exec('DELETE FROM products WHERE id=?', [$id]);   // images FK CASCADE
        Helpers::log('delete', 'product:' . $id);
        Flash::success('삭제되었습니다.');
        Helpers::redirect('/admin/products');
    }

    public function deleteImage(array $params = []): void
    {
        Auth::requireLogin();
        Rbac::require('products.edit');
        $this->guardCsrf();
        $pid = (int)$params['id'];
        $img = Database::one('SELECT * FROM product_images WHERE id=? AND product_id=?',
            [(int)$params['imageId'], $pid]);
        if ($img) {
            Storage::delete($img['path']);
            Database::exec('DELETE FROM product_images WHERE id=?', [$img['id']]);
            Helpers::log('delete', 'product_image:' . $img['id']);
            Flash::success('이미지를 삭제했습니다.');
        }
        Helpers::redirect('/admin/products/' . $pid . '/edit');
    }

    /** 폼 뷰에 필요한 공통 데이터 */
    private function formData(array $item, bool $isEdit, string $action): array
    {
        $images = [];
        if ($isEdit && !empty($item['id'])) {
            $images = Database::all('SELECT * FROM product_images WHERE product_id=? ORDER BY sort,id', [$item['id']]);
        }
        return [
            'title'   => $isEdit ? '제품 수정' : '제품 등록',
            'item'    => $item, 'isEdit' => $isEdit, 'action' => $action,
            'brands'  => Database::all('SELECT id,name_ko FROM brands ORDER BY sort,id'),
            'cats'    => Database::all('SELECT id,name_ko FROM categories ORDER BY sort,id'),
            'images'  => $images,
            'stores'  => Database::all('SELECT id,name,type FROM stores WHERE is_active=1 ORDER BY sort,id'),
            'linkedStores' => ($isEdit && !empty($item['id']))
                ? array_column(Database::all('SELECT store_id FROM product_stores WHERE product_id=?', [$item['id']]), 'store_id')
                : [],
        ];
    }

    /** 체크된 구매처로 product_stores 동기화 */
    private function syncStores(int $productId): void
    {
        Database::exec('DELETE FROM product_stores WHERE product_id=?', [$productId]);
        $ids = $_POST['stores'] ?? [];
        if (!is_array($ids)) return;
        foreach ($ids as $sid) {
            $sid = (int)$sid;
            if ($sid > 0) {
                Database::exec('INSERT IGNORE INTO product_stores (product_id,store_id) VALUES (?,?)', [$productId, $sid]);
            }
        }
    }

    private function saveImages(int $productId): void
    {
        if (empty($_FILES['images'])) return;
        $paths = Storage::putMany($_FILES['images']);
        $sort = (int)Database::scalar('SELECT COALESCE(MAX(sort),-1)+1 FROM product_images WHERE product_id=?', [$productId]);
        foreach ($paths as $rel) {
            Database::exec('INSERT INTO product_images (product_id,path,sort) VALUES (?,?,?)', [$productId, $rel, $sort++]);
        }
    }

    private function input(): array
    {
        return [
            'brand_id'      => ($_POST['brand_id'] ?? '') !== '' ? (int)$_POST['brand_id'] : null,
            'category_id'   => ($_POST['category_id'] ?? '') !== '' ? (int)$_POST['category_id'] : null,
            'name_ko'       => trim($_POST['name_ko'] ?? ''),
            'name_vi'       => trim($_POST['name_vi'] ?? ''),
            'name_en'       => trim($_POST['name_en'] ?? ''),
            'summary_ko'    => trim($_POST['summary_ko'] ?? ''),
            'summary_vi'    => trim($_POST['summary_vi'] ?? ''),
            'summary_en'    => trim($_POST['summary_en'] ?? ''),
            'description_ko'=> trim($_POST['description_ko'] ?? ''),
            'description_vi'=> trim($_POST['description_vi'] ?? ''),
            'description_en'=> trim($_POST['description_en'] ?? ''),
            'price'         => (float)($_POST['price'] ?? 0),
            'is_active'     => isset($_POST['is_active']) ? 1 : 0,
            'is_best'       => isset($_POST['is_best']) ? 1 : 0,
            'sort'          => (int)($_POST['sort'] ?? 0),
        ];
    }

    private function guardCsrf(): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/admin/products'); }
    }
}
