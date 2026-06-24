-- =====================================================================
--  ARNO K-Beauty — 02 RBAC 시드 + 초기 데이터
--  역할 3종 / 권한 / 매트릭스(PRD §6.3) / 최종관리자 / 설정 기본값
-- =====================================================================
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 역할 (level: 운영10 / 경영50 / 최종99)
-- ---------------------------------------------------------------------
INSERT INTO admin_roles (code, name_ko, level) VALUES
  ('operator',   '운영관리자', 10),
  ('manager',    '경영관리자', 50),
  ('superadmin', '최종관리자', 99);

-- ---------------------------------------------------------------------
-- 권한 (module.action)
-- ---------------------------------------------------------------------
INSERT INTO permissions (module, action, label) VALUES
  ('dashboard','view','대시보드 보기'),
  ('products','view','제품 보기'),('products','create','제품 등록'),('products','edit','제품 수정'),('products','delete','제품 삭제'),
  ('brands','view','브랜드 보기'),('brands','create','브랜드 등록'),('brands','edit','브랜드 수정'),('brands','delete','브랜드 삭제'),
  ('categories','view','카테고리 보기'),('categories','create','카테고리 등록'),('categories','edit','카테고리 수정'),('categories','delete','카테고리 삭제'),
  ('stores','view','구매처 보기'),('stores','create','구매처 등록'),('stores','edit','구매처 수정'),('stores','delete','구매처 삭제'),
  ('pages','view','페이지 보기'),('pages','edit','페이지 수정'),
  ('posts','view','소식 보기'),('posts','create','소식 등록'),('posts','edit','소식 수정'),('posts','delete','소식 삭제'),
  ('projects','view','프로젝트 보기'),('projects','create','프로젝트 등록'),('projects','edit','프로젝트 수정'),('projects','delete','프로젝트 삭제'),
  ('inquiries','view','문의 보기'),('inquiries','reply','문의 답변'),('inquiries','delete','문의 삭제'),
  ('orders','view','주문 보기'),('orders','edit','주문 수정(배송상태)'),('orders','confirm','입금확인'),('orders','cancel','주문취소'),('orders','export','주문 내보내기'),
  ('members','view','회원 보기'),('members','edit','회원 수정'),('members','delete','회원 삭제'),('members','export','회원 내보내기'),
  ('reports','view','통계 보기'),('reports','export','통계 내보내기'),
  ('admins','view','관리자 보기'),('admins','create','관리자 등록'),('admins','edit','관리자 수정'),('admins','delete','관리자 삭제'),
  ('roles','view','권한 보기'),('roles','edit','권한 수정'),
  ('settings','view','설정 보기'),('settings','edit','설정 수정'),
  ('logs','view','감사로그 보기');

-- ---------------------------------------------------------------------
-- 매트릭스: 운영관리자 (operator)
--   제품/브랜드/카테고리/소식/프로젝트 = 전체
--   구매처 = view·edit / 페이지 = view·edit / 문의 = view·reply / 주문 = view·edit(배송)
-- ---------------------------------------------------------------------
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM admin_roles r
JOIN permissions p
  ON (p.module, p.action) IN (
    ('dashboard','view'),
    ('products','view'),('products','create'),('products','edit'),('products','delete'),
    ('brands','view'),('brands','create'),('brands','edit'),('brands','delete'),
    ('categories','view'),('categories','create'),('categories','edit'),('categories','delete'),
    ('stores','view'),('stores','edit'),
    ('pages','view'),('pages','edit'),
    ('posts','view'),('posts','create'),('posts','edit'),('posts','delete'),
    ('projects','view'),('projects','create'),('projects','edit'),('projects','delete'),
    ('inquiries','view'),('inquiries','reply'),
    ('orders','view'),('orders','edit')
  )
WHERE r.code = 'operator';

-- ---------------------------------------------------------------------
-- 매트릭스: 경영관리자 (manager) = 운영관리자 전체 + 추가권한
--   구매처 전체 / 문의 삭제 / 주문 입금확인·취소·내보내기 / 회원 view·edit·export / 통계 / 로그 view
-- ---------------------------------------------------------------------
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM admin_roles r
JOIN permissions p
  ON (p.module, p.action) IN (
    -- 운영관리자가 가진 것 동일 포함
    ('dashboard','view'),
    ('products','view'),('products','create'),('products','edit'),('products','delete'),
    ('brands','view'),('brands','create'),('brands','edit'),('brands','delete'),
    ('categories','view'),('categories','create'),('categories','edit'),('categories','delete'),
    ('stores','view'),('stores','create'),('stores','edit'),('stores','delete'),
    ('pages','view'),('pages','edit'),
    ('posts','view'),('posts','create'),('posts','edit'),('posts','delete'),
    ('projects','view'),('projects','create'),('projects','edit'),('projects','delete'),
    ('inquiries','view'),('inquiries','reply'),('inquiries','delete'),
    ('orders','view'),('orders','edit'),('orders','confirm'),('orders','cancel'),('orders','export'),
    ('members','view'),('members','edit'),('members','export'),
    ('reports','view'),('reports','export'),
    ('logs','view')
  )
WHERE r.code = 'manager';

-- ---------------------------------------------------------------------
-- 매트릭스: 최종관리자 (superadmin) = 모든 권한
-- ---------------------------------------------------------------------
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM admin_roles r
JOIN permissions p
WHERE r.code = 'superadmin';

-- ---------------------------------------------------------------------
-- 최종관리자 계정 시드
--   이메일 : admin@accu86.com
--   임시 비번 : Accu!2026Admin   (첫 로그인 후 반드시 변경)
--   해시 : bcrypt(cost 12)
-- ---------------------------------------------------------------------
INSERT INTO admin_users (email, password_hash, name, role_id, status)
SELECT 'admin@accu86.com',
       '$2b$12$HWaRe6NDm8cGH6xdhS9iouOwS6UzMiSC7EtTSPI35lUPynAIcuInC',
       '최종관리자',
       r.id,
       'active'
FROM admin_roles r WHERE r.code = 'superadmin';

-- ---------------------------------------------------------------------
-- 설정 기본값 (프론트 기본 vi / 관리자 입력 기본 ko)
-- ---------------------------------------------------------------------
INSERT INTO settings (skey, svalue) VALUES
  ('site_name',          'ACCU Cosmetic'),
  ('default_lang_front', 'vi'),
  ('default_lang_admin', 'ko'),
  ('currency',           'VND'),
  ('contact_email',      'admin@accu86.com'),
  ('contact_phone',      '');

-- (선택) 입금 계좌 예시 1건 — 실제 값으로 교체하세요
INSERT INTO bank_accounts (bank_name, account_no, holder, is_active, sort) VALUES
  ('우리은행', '0000-000-000000', 'ARNO', 1, 0);
