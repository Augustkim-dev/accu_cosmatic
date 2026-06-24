-- =====================================================================
--  샘플 데이터 (수동 1회 실행) — 프론트 테스트용
--  * 자동실행(sql/) 아님: 기존 데이터/볼륨에 영향 없음
--  * idempotent: 코드/이름 중복이면 건너뜀 → 이미 넣으신 1건과 충돌 없음
--
--  실행:
--   docker-compose exec -T arno_db \
--     mariadb -uaccu -p'<DB_PASSWORD>' accu_cosmetic < sample_data.sql
-- =====================================================================
SET NAMES utf8mb4;

-- ---- 카테고리 ----
INSERT INTO categories (name_ko,name_vi,name_en,sort,is_active)
SELECT '스킨케어','Chăm sóc da','Skincare',1,1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name_ko='스킨케어');
INSERT INTO categories (name_ko,name_vi,name_en,sort,is_active)
SELECT '클렌징','Làm sạch','Cleansing',2,1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name_ko='클렌징');
INSERT INTO categories (name_ko,name_vi,name_en,sort,is_active)
SELECT '마스크팩','Mặt nạ','Mask',3,1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name_ko='마스크팩');
INSERT INTO categories (name_ko,name_vi,name_en,sort,is_active)
SELECT '선케어','Chống nắng','Suncare',4,1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name_ko='선케어');

-- ---- 브랜드 (code UNIQUE → 중복이면 IGNORE) ----
INSERT IGNORE INTO brands (code,name_ko,name_vi,name_en,sort,is_active,story_ko) VALUES
 ('bowdon','보우돈','BOWDON','BOWDON',1,1,'합리적인 데일리 스킨케어 브랜드.'),
 ('juness','쥬네스','JUNESS','JUNESS',2,1,'순한 성분의 클렌징 전문 브랜드.'),
 ('estella','에스텔라','ESTELLA','ESTELLA',3,1,'프리미엄 안티에이징 라인.'),
 ('drwelly','닥터웰리와이','Dr''s WELLY Y','Dr''s WELLY Y',4,1,'더마 코스메틱 전문.');

-- ---- 제품 (브랜드 code로 연결, name_ko 중복이면 건너뜀) ----
-- 헬퍼 패턴: 브랜드/카테고리를 서브쿼리로 연결
INSERT INTO products (brand_id,category_id,name_ko,name_vi,name_en,summary_ko,summary_vi,summary_en,price,is_active,is_best,sort)
SELECT (SELECT id FROM brands WHERE code='bowdon'),
       (SELECT id FROM categories WHERE name_ko='스킨케어'),
       '보우돈 수분 토너 200ml','Toner cấp ẩm BOWDON 200ml','BOWDON Hydra Toner 200ml',
       '매일 쓰는 수분 토너','Toner cấp ẩm hằng ngày','Daily hydrating toner',
       250000,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name_ko='보우돈 수분 토너 200ml');

INSERT INTO products (brand_id,category_id,name_ko,name_vi,name_en,summary_ko,summary_vi,summary_en,price,is_active,is_best,sort)
SELECT (SELECT id FROM brands WHERE code='bowdon'),
       (SELECT id FROM categories WHERE name_ko='스킨케어'),
       '보우돈 진정 에센스 50ml','Tinh chất làm dịu BOWDON 50ml','BOWDON Calming Essence 50ml',
       '민감 피부 진정','Làm dịu da nhạy cảm','Soothes sensitive skin',
       320000,1,0,2
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name_ko='보우돈 진정 에센스 50ml');

INSERT INTO products (brand_id,category_id,name_ko,name_vi,name_en,summary_ko,summary_vi,summary_en,price,is_active,is_best,sort)
SELECT (SELECT id FROM brands WHERE code='juness'),
       (SELECT id FROM categories WHERE name_ko='클렌징'),
       '쥬네스 마일드 클렌징 폼 150ml','Sữa rửa mặt dịu nhẹ JUNESS 150ml','JUNESS Mild Cleansing Foam 150ml',
       '저자극 약산성 클렌저','Sữa rửa mặt pH cân bằng','Low-irritation mild cleanser',
       180000,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name_ko='쥬네스 마일드 클렌징 폼 150ml');

INSERT INTO products (brand_id,category_id,name_ko,name_vi,name_en,summary_ko,summary_vi,summary_en,price,is_active,is_best,sort)
SELECT (SELECT id FROM brands WHERE code='juness'),
       (SELECT id FROM categories WHERE name_ko='마스크팩'),
       '쥬네스 수분 마스크 (10매)','Mặt nạ cấp ẩm JUNESS (10 miếng)','JUNESS Hydra Mask (10ea)',
       '데일리 수분 마스크','Mặt nạ cấp ẩm hằng ngày','Daily hydrating mask',
       150000,1,0,2
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name_ko='쥬네스 수분 마스크 (10매)');

INSERT INTO products (brand_id,category_id,name_ko,name_vi,name_en,summary_ko,summary_vi,summary_en,price,is_active,is_best,sort)
SELECT (SELECT id FROM brands WHERE code='estella'),
       (SELECT id FROM categories WHERE name_ko='스킨케어'),
       '에스텔라 리프팅 크림 50ml','Kem nâng cơ ESTELLA 50ml','ESTELLA Lifting Cream 50ml',
       '탄력 안티에이징 크림','Kem chống lão hóa','Firming anti-aging cream',
       590000,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name_ko='에스텔라 리프팅 크림 50ml');

INSERT INTO products (brand_id,category_id,name_ko,name_vi,name_en,summary_ko,summary_vi,summary_en,price,is_active,is_best,sort)
SELECT (SELECT id FROM brands WHERE code='estella'),
       (SELECT id FROM categories WHERE name_ko='스킨케어'),
       '에스텔라 비타민 세럼 30ml','Serum vitamin ESTELLA 30ml','ESTELLA Vitamin Serum 30ml',
       '브라이트닝 세럼','Serum làm sáng','Brightening serum',
       450000,1,0,2
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name_ko='에스텔라 비타민 세럼 30ml');

INSERT INTO products (brand_id,category_id,name_ko,name_vi,name_en,summary_ko,summary_vi,summary_en,price,is_active,is_best,sort)
SELECT (SELECT id FROM brands WHERE code='drwelly'),
       (SELECT id FROM categories WHERE name_ko='선케어'),
       '닥터웰리와이 선크림 SPF50+ 50ml','Kem chống nắng Dr''s WELLY Y SPF50+ 50ml','Dr''s WELLY Y Sun Cream SPF50+ 50ml',
       '데일리 자외선 차단','Chống nắng hằng ngày','Daily UV protection',
       280000,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name_ko='닥터웰리와이 선크림 SPF50+ 50ml');

INSERT INTO products (brand_id,category_id,name_ko,name_vi,name_en,summary_ko,summary_vi,summary_en,price,is_active,is_best,sort)
SELECT (SELECT id FROM brands WHERE code='drwelly'),
       (SELECT id FROM categories WHERE name_ko='클렌징'),
       '닥터웰리와이 시카 클렌징 오일 200ml','Dầu tẩy trang Cica Dr''s WELLY Y 200ml','Dr''s WELLY Y Cica Cleansing Oil 200ml',
       '진정 클렌징 오일','Dầu tẩy trang làm dịu','Soothing cleansing oil',
       350000,1,0,2
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name_ko='닥터웰리와이 시카 클렌징 오일 200ml');

-- 결과 확인
SELECT (SELECT COUNT(*) FROM brands) AS brands,
       (SELECT COUNT(*) FROM categories) AS categories,
       (SELECT COUNT(*) FROM products) AS products;
