<?php
namespace App\Controllers\Front;

use App\Cart;
use App\Csrf;
use App\Database;
use App\Flash;
use App\Helpers;
use App\Lang;
use App\MemberAuth;

class CheckoutController
{
    public function form(array $params = []): void
    {
        $items = Cart::detailed();
        if (!$items) { Flash::error(Lang::t('empty_cart')); Helpers::redirect('/cart'); }
        $banks = Database::all('SELECT * FROM bank_accounts WHERE is_active=1 ORDER BY sort,id');
        $member = MemberAuth::user();
        Helpers::view('front/checkout', [
            'title' => '주문하기', 'items' => $items, 'subtotal' => Cart::subtotal(),
            'banks' => $banks, 'member' => $member,
        ], 'front/layout');
    }

    public function submit(array $params = []): void
    {
        if (!Csrf::check()) { Flash::error('세션이 만료되었습니다.'); Helpers::redirect('/checkout'); }
        $items = Cart::detailed();
        if (!$items) { Flash::error(Lang::t('empty_cart')); Helpers::redirect('/cart'); }

        $receiver = trim($_POST['receiver_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $depositor= trim($_POST['depositor_name'] ?? '');
        $bankId   = ($_POST['bank_account_id'] ?? '') !== '' ? (int)$_POST['bank_account_id'] : null;

        if ($receiver === '' || $phone === '') {
            Flash::error('받는 분과 연락처는 필수입니다.');
            Helpers::redirect('/checkout');
        }

        $subtotal = Cart::subtotal();
        $shipping = 0.0;
        $total    = $subtotal + $shipping;
        $orderNo  = 'A' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $orderId = Database::exec(
                'INSERT INTO orders (order_no,member_id,receiver_name,phone,address,depositor_name,bank_account_id,subtotal,shipping_fee,total,status)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                [$orderNo, MemberAuth::id(), $receiver, $phone, $address, $depositor, $bankId, $subtotal, $shipping, $total, 'pending']
            );
            foreach ($items as $it) {
                Database::exec(
                    'INSERT INTO order_items (order_id,product_id,product_name,price,qty) VALUES (?,?,?,?,?)',
                    [$orderId, $it['id'], $it['name_ko'], $it['price'], $it['qty']]
                );
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Flash::error('주문 처리 중 오류가 발생했습니다.');
            Helpers::redirect('/checkout');
        }

        Cart::clear();
        Helpers::log('order_create', 'order:' . $orderNo);
        Helpers::redirect('/order/complete/' . $orderNo);
    }

    public function complete(array $params = []): void
    {
        $o = Database::one('SELECT * FROM orders WHERE order_no=?', [$params['orderNo'] ?? '']);
        if (!$o) { Helpers::redirect('/'); }
        $items = Database::all('SELECT * FROM order_items WHERE order_id=?', [$o['id']]);
        $bank  = $o['bank_account_id'] ? Database::one('SELECT * FROM bank_accounts WHERE id=?', [$o['bank_account_id']]) : null;
        Helpers::view('front/order_complete', [
            'title' => '주문 완료', 'o' => $o, 'items' => $items, 'bank' => $bank,
        ], 'front/layout');
    }
}
