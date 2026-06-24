<?php
namespace App;

/**
 * 프론트 다국어 (세션/쿠키). 기본 vi.
 * 콘텐츠는 _ko/_vi/_en 컬럼에서 현재 언어값을 고르고, 비면 ko→en→vi 폴백.
 */
class Lang
{
    public const SUPPORTED = ['ko', 'vi', 'en'];
    public const FALLBACK  = 'vi';

    public static function current(): string
    {
        $l = $_SESSION['lang'] ?? $_COOKIE['lang'] ?? self::FALLBACK;
        return in_array($l, self::SUPPORTED, true) ? $l : self::FALLBACK;
    }

    public static function set(string $l): void
    {
        if (in_array($l, self::SUPPORTED, true)) {
            $_SESSION['lang'] = $l;
            setcookie('lang', $l, time() + 86400 * 365, '/');
        }
    }

    /** 다국어 컬럼 픽 (예: pick($row,'name') → name_vi, 비면 폴백) */
    public static function pick(array $row, string $base): string
    {
        $order = array_unique([self::current(), 'ko', 'en', 'vi']);
        foreach ($order as $c) {
            if (!empty($row[$base . '_' . $c])) return (string)$row[$base . '_' . $c];
        }
        return '';
    }

    /** UI 라벨 */
    public static function t(string $key): string
    {
        $d = self::DICT[$key] ?? null;
        if (!$d) return $key;
        return $d[self::current()] ?? $d['vi'] ?? $key;
    }

    private const DICT = [
        'home'         => ['ko' => '홈', 'vi' => 'Trang chủ', 'en' => 'Home'],
        'products'     => ['ko' => '제품', 'vi' => 'Sản phẩm', 'en' => 'Products'],
        'brands'       => ['ko' => '브랜드', 'vi' => 'Thương hiệu', 'en' => 'Brands'],
        'all_products' => ['ko' => '전체 제품', 'vi' => 'Tất cả sản phẩm', 'en' => 'All Products'],
        'best_sellers' => ['ko' => '베스트셀러', 'vi' => 'Sản phẩm bán chạy', 'en' => 'Best Sellers'],
        'new_arrivals' => ['ko' => '신상품', 'vi' => 'Sản phẩm mới', 'en' => 'New Arrivals'],
        'view_detail'  => ['ko' => '자세히 보기', 'vi' => 'Xem chi tiết', 'en' => 'View Details'],
        'inquiry'      => ['ko' => '구매 문의', 'vi' => 'Liên hệ mua hàng', 'en' => 'Purchase Inquiry'],
        'no_products'  => ['ko' => '제품이 없습니다.', 'vi' => 'Không có sản phẩm.', 'en' => 'No products.'],
        'brand'        => ['ko' => '브랜드', 'vi' => 'Thương hiệu', 'en' => 'Brand'],
        'category'     => ['ko' => '카테고리', 'vi' => 'Danh mục', 'en' => 'Category'],
        'contact'      => ['ko' => '문의처', 'vi' => 'Liên hệ', 'en' => 'Contact'],
        'description'  => ['ko' => '상세 설명', 'vi' => 'Mô tả', 'en' => 'Description'],
        'cart'         => ['ko' => '장바구니', 'vi' => 'Giỏ hàng', 'en' => 'Cart'],
        'add_to_cart'  => ['ko' => '장바구니 담기', 'vi' => 'Thêm vào giỏ', 'en' => 'Add to Cart'],
        'login'        => ['ko' => '로그인', 'vi' => 'Đăng nhập', 'en' => 'Login'],
        'logout'       => ['ko' => '로그아웃', 'vi' => 'Đăng xuất', 'en' => 'Logout'],
        'register'     => ['ko' => '회원가입', 'vi' => 'Đăng ký', 'en' => 'Sign up'],
        'mypage'       => ['ko' => '마이페이지', 'vi' => 'Tài khoản', 'en' => 'My Page'],
        'my_orders'    => ['ko' => '주문 내역', 'vi' => 'Đơn hàng của tôi', 'en' => 'My Orders'],
        'checkout'     => ['ko' => '주문하기', 'vi' => 'Đặt hàng', 'en' => 'Checkout'],
        'quantity'     => ['ko' => '수량', 'vi' => 'Số lượng', 'en' => 'Qty'],
        'subtotal'     => ['ko' => '상품금액', 'vi' => 'Tạm tính', 'en' => 'Subtotal'],
        'shipping'     => ['ko' => '배송비', 'vi' => 'Phí vận chuyển', 'en' => 'Shipping'],
        'total'        => ['ko' => '합계', 'vi' => 'Tổng cộng', 'en' => 'Total'],
        'empty_cart'   => ['ko' => '장바구니가 비었습니다.', 'vi' => 'Giỏ hàng trống.', 'en' => 'Your cart is empty.'],
        'receiver'     => ['ko' => '받는 분', 'vi' => 'Người nhận', 'en' => 'Receiver'],
        'phone'        => ['ko' => '연락처', 'vi' => 'Số điện thoại', 'en' => 'Phone'],
        'address'      => ['ko' => '주소', 'vi' => 'Địa chỉ', 'en' => 'Address'],
        'depositor'    => ['ko' => '입금자명', 'vi' => 'Tên người chuyển khoản', 'en' => 'Depositor name'],
        'bank_account' => ['ko' => '입금 계좌', 'vi' => 'Tài khoản ngân hàng', 'en' => 'Bank account'],
        'order_no'     => ['ko' => '주문번호', 'vi' => 'Mã đơn hàng', 'en' => 'Order No.'],
        'order_done'   => ['ko' => '주문이 접수되었습니다', 'vi' => 'Đã nhận đơn hàng', 'en' => 'Order received'],
        'bank_guide'   => ['ko' => '아래 계좌로 입금해 주세요. 입금 확인 후 처리됩니다.', 'vi' => 'Vui lòng chuyển khoản vào tài khoản dưới đây. Đơn sẽ được xử lý sau khi xác nhận.', 'en' => 'Please transfer to the account below. We will process after confirming payment.'],
        'email'        => ['ko' => '이메일', 'vi' => 'Email', 'en' => 'Email'],
        'password'     => ['ko' => '비밀번호', 'vi' => 'Mật khẩu', 'en' => 'Password'],
        'name'         => ['ko' => '이름', 'vi' => 'Họ tên', 'en' => 'Name'],
        'order_date'   => ['ko' => '주문일', 'vi' => 'Ngày đặt', 'en' => 'Date'],
        'status'       => ['ko' => '상태', 'vi' => 'Trạng thái', 'en' => 'Status'],
        // 주문 상태
        'st_pending'   => ['ko' => '입금대기', 'vi' => 'Chờ thanh toán', 'en' => 'Pending'],
        'st_paid'      => ['ko' => '결제완료', 'vi' => 'Đã thanh toán', 'en' => 'Paid'],
        'st_shipping'  => ['ko' => '배송중', 'vi' => 'Đang giao', 'en' => 'Shipping'],
        'st_done'      => ['ko' => '배송완료', 'vi' => 'Hoàn tất', 'en' => 'Completed'],
        'st_cancelled' => ['ko' => '취소', 'vi' => 'Đã hủy', 'en' => 'Cancelled'],
        'news'         => ['ko' => '소식', 'vi' => 'Tin tức', 'en' => 'News'],
        'where_to_buy' => ['ko' => '구매처', 'vi' => 'Nơi bán', 'en' => 'Where to Buy'],
        'visit'        => ['ko' => '바로가기', 'vi' => 'Đến nơi', 'en' => 'Visit'],
        'message'      => ['ko' => '문의 내용', 'vi' => 'Nội dung', 'en' => 'Message'],
        'send'         => ['ko' => '보내기', 'vi' => 'Gửi', 'en' => 'Send'],
        'inquiry_sent' => ['ko' => '문의가 접수되었습니다. 감사합니다.', 'vi' => 'Đã gửi liên hệ. Cảm ơn bạn.', 'en' => 'Your inquiry has been sent. Thank you.'],
        'read_more'    => ['ko' => '더 보기', 'vi' => 'Xem thêm', 'en' => 'Read more'],
    ];
}
