<?php
namespace App;

/** 세션 장바구니: $_SESSION['cart'] = [product_id => qty] */
class Cart
{
    /** @return array<int,int> */
    public static function items(): array { return $_SESSION['cart'] ?? []; }

    public static function add(int $pid, int $qty = 1): void
    {
        $c = self::items();
        $c[$pid] = ($c[$pid] ?? 0) + max(1, $qty);
        $_SESSION['cart'] = $c;
    }

    public static function set(int $pid, int $qty): void
    {
        $c = self::items();
        if ($qty <= 0) unset($c[$pid]); else $c[$pid] = $qty;
        $_SESSION['cart'] = $c;
    }

    public static function remove(int $pid): void
    {
        $c = self::items();
        unset($c[$pid]);
        $_SESSION['cart'] = $c;
    }

    public static function clear(): void { unset($_SESSION['cart']); }

    public static function count(): int { return array_sum(self::items()); }

    /** 제품정보 조인 (활성 제품만). 각 행에 qty, line 추가 */
    public static function detailed(): array
    {
        $items = self::items();
        if (!$items) return [];
        $ids = array_map('intval', array_keys($items));
        $in  = implode(',', $ids);   // intval 처리 → 인젝션 안전
        $rows = Database::all(
            "SELECT p.id,p.name_ko,p.name_vi,p.name_en,p.price,p.is_active,
                    (SELECT path FROM product_images i WHERE i.product_id=p.id ORDER BY i.sort,i.id LIMIT 1) AS thumb
               FROM products p WHERE p.id IN ($in)"
        );
        $out = [];
        foreach ($rows as $r) {
            if (!$r['is_active']) continue;
            $r['qty']  = (int)($items[$r['id']] ?? 0);
            $r['line'] = (float)$r['price'] * $r['qty'];
            $out[] = $r;
        }
        return $out;
    }

    public static function subtotal(): float
    {
        $s = 0.0;
        foreach (self::detailed() as $r) $s += $r['line'];
        return $s;
    }
}
