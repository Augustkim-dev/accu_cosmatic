<?php
namespace App\Controllers\Front;

use App\Cart;
use App\Csrf;
use App\Helpers;

class CartController
{
    public function index(array $params = []): void
    {
        Helpers::view('front/cart', [
            'title' => '장바구니',
            'items' => Cart::detailed(),
            'subtotal' => Cart::subtotal(),
        ], 'front/layout');
    }

    public function add(array $params = []): void
    {
        if (Csrf::check()) {
            $pid = (int)($_POST['product_id'] ?? 0);
            $qty = max(1, (int)($_POST['qty'] ?? 1));
            if ($pid) Cart::add($pid, $qty);
        }
        Helpers::redirect('/cart');
    }

    public function update(array $params = []): void
    {
        if (Csrf::check()) {
            foreach (($_POST['qty'] ?? []) as $pid => $qty) {
                Cart::set((int)$pid, (int)$qty);
            }
        }
        Helpers::redirect('/cart');
    }

    public function remove(array $params = []): void
    {
        if (Csrf::check()) {
            $pid = (int)($_POST['product_id'] ?? 0);
            if ($pid) Cart::remove($pid);
        }
        Helpers::redirect('/cart');
    }
}
