-- =====================================================================
--  ARNO K-Beauty 카탈로그 — 01 스키마 (MariaDB 11 / MySQL 8 호환)
--  다국어: _ko / _vi / _en 컬럼 방식 (1차 3개 언어 고정)
--  컨테이너 최초 기동 시 자동 실행 (docker-entrypoint-initdb.d)
-- =====================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- RBAC (IAM) : 역할 / 권한 / 매핑 / 관리자
-- ---------------------------------------------------------------------
CREATE TABLE admin_roles (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(32)  NOT NULL UNIQUE,        -- operator / manager / superadmin
  name_ko     VARCHAR(64)  NOT NULL,
  level       INT          NOT NULL DEFAULT 0,     -- 10 / 50 / 99
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
  id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  module  VARCHAR(32) NOT NULL,                    -- products, members, orders ...
  action  VARCHAR(32) NOT NULL,                    -- view, create, edit, delete, export ...
  label   VARCHAR(64) NOT NULL,
  UNIQUE KEY uq_perm (module, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
  role_id        INT UNSIGNED NOT NULL,
  permission_id  INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id)       REFERENCES admin_roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email          VARCHAR(190) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  name           VARCHAR(64)  NOT NULL,
  role_id        INT UNSIGNED NOT NULL,
  status         ENUM('active','suspended') NOT NULL DEFAULT 'active',
  last_login_at  DATETIME     NULL,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_admin_role FOREIGN KEY (role_id) REFERENCES admin_roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 회원 (프론트 사용자)
-- ---------------------------------------------------------------------
CREATE TABLE members (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email          VARCHAR(190) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  name           VARCHAR(64)  NOT NULL,
  phone          VARCHAR(32)  NULL,
  lang           ENUM('ko','vi','en') NOT NULL DEFAULT 'vi',
  status         ENUM('active','suspended') NOT NULL DEFAULT 'active',
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 카탈로그 : 브랜드 / 카테고리 / 제품 / 이미지
-- ---------------------------------------------------------------------
CREATE TABLE brands (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code       VARCHAR(48)  NOT NULL UNIQUE,
  name_ko    VARCHAR(128) NOT NULL,
  name_vi    VARCHAR(128) NULL,
  name_en    VARCHAR(128) NULL,
  logo_path  VARCHAR(255) NULL,
  story_ko   TEXT NULL,
  story_vi   TEXT NULL,
  story_en   TEXT NULL,
  sort       INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parent_id  INT UNSIGNED NULL,
  name_ko    VARCHAR(128) NOT NULL,
  name_vi    VARCHAR(128) NULL,
  name_en    VARCHAR(128) NULL,
  sort       INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT fk_cat_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  brand_id       INT UNSIGNED NULL,
  category_id    INT UNSIGNED NULL,
  name_ko        VARCHAR(190) NOT NULL,
  name_vi        VARCHAR(190) NULL,
  name_en        VARCHAR(190) NULL,
  summary_ko     VARCHAR(255) NULL,
  summary_vi     VARCHAR(255) NULL,
  summary_en     VARCHAR(255) NULL,
  description_ko TEXT NULL,
  description_vi TEXT NULL,
  description_en TEXT NULL,
  price          DECIMAL(12,2) NOT NULL DEFAULT 0,   -- 표시가(VND 등). 통화는 settings
  is_active      TINYINT(1) NOT NULL DEFAULT 1,
  is_best        TINYINT(1) NOT NULL DEFAULT 0,
  sort           INT NOT NULL DEFAULT 0,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_prod_brand FOREIGN KEY (brand_id)    REFERENCES brands(id)     ON DELETE SET NULL,
  CONSTRAINT fk_prod_cat   FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  KEY idx_prod_active (is_active),
  KEY idx_prod_best (is_best)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_images (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  path       VARCHAR(255) NOT NULL,
  sort       INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_img_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 구매처 / 연결정보
-- ---------------------------------------------------------------------
CREATE TABLE stores (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(128) NOT NULL,
  type      ENUM('offline','online','sns') NOT NULL DEFAULT 'online',
  url       VARCHAR(255) NULL,
  region    VARCHAR(128) NULL,
  lat       DECIMAL(10,7) NULL,
  lng       DECIMAL(10,7) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort      INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_stores (
  product_id INT UNSIGNED NOT NULL,
  store_id   INT UNSIGNED NOT NULL,
  PRIMARY KEY (product_id, store_id),
  CONSTRAINT fk_ps_prod  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_ps_store FOREIGN KEY (store_id)   REFERENCES stores(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 주문 (은행이체/무통장입금) — 결제는 관리자 입금확인으로
-- ---------------------------------------------------------------------
CREATE TABLE bank_accounts (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bank_name  VARCHAR(64)  NOT NULL,
  account_no VARCHAR(64)  NOT NULL,
  holder     VARCHAR(64)  NOT NULL,
  is_active  TINYINT(1) NOT NULL DEFAULT 1,
  sort       INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_no        VARCHAR(32)  NOT NULL UNIQUE,
  member_id       INT UNSIGNED NULL,
  receiver_name   VARCHAR(64)  NOT NULL,
  phone           VARCHAR(32)  NOT NULL,
  address         VARCHAR(255) NULL,
  depositor_name  VARCHAR(64)  NULL,                 -- 입금자명
  bank_account_id INT UNSIGNED NULL,                 -- 안내된 입금 계좌
  subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0,
  shipping_fee    DECIMAL(12,2) NOT NULL DEFAULT 0,
  total           DECIMAL(12,2) NOT NULL DEFAULT 0,
  status          ENUM('pending','paid','shipping','done','cancelled') NOT NULL DEFAULT 'pending',
  paid_at         DATETIME NULL,                     -- 입금확인 시각
  memo            VARCHAR(255) NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_member FOREIGN KEY (member_id)       REFERENCES members(id)       ON DELETE SET NULL,
  CONSTRAINT fk_order_bank   FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL,
  KEY idx_order_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id     INT UNSIGNED NOT NULL,
  product_id   INT UNSIGNED NULL,
  product_name VARCHAR(190) NOT NULL,                -- 주문시점 스냅샷
  price        DECIMAL(12,2) NOT NULL DEFAULT 0,     -- 주문시점 스냅샷
  qty          INT NOT NULL DEFAULT 1,
  CONSTRAINT fk_oi_order FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  CONSTRAINT fk_oi_prod  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 문의 / 콘텐츠 / 게시판 / 설정 / 감사로그
-- ---------------------------------------------------------------------
CREATE TABLE inquiries (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_id  INT UNSIGNED NULL,
  name       VARCHAR(64)  NOT NULL,
  contact    VARCHAR(128) NOT NULL,
  message    TEXT NOT NULL,
  status     ENUM('received','processing','done') NOT NULL DEFAULT 'received',
  reply      TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_inq_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pages (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug      VARCHAR(128) NOT NULL UNIQUE,
  title_ko  VARCHAR(190) NOT NULL,
  title_vi  VARCHAR(190) NULL,
  title_en  VARCHAR(190) NULL,
  body_ko   MEDIUMTEXT NULL,
  body_vi   MEDIUMTEXT NULL,
  body_en   MEDIUMTEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE posts (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type         ENUM('news','project') NOT NULL DEFAULT 'news',
  title_ko     VARCHAR(190) NOT NULL,
  title_vi     VARCHAR(190) NULL,
  title_en     VARCHAR(190) NULL,
  body_ko      MEDIUMTEXT NULL,
  body_vi      MEDIUMTEXT NULL,
  body_en      MEDIUMTEXT NULL,
  thumbnail    VARCHAR(255) NULL,
  is_active    TINYINT(1) NOT NULL DEFAULT 1,
  published_at DATETIME NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_post_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  skey    VARCHAR(64) NOT NULL UNIQUE,
  svalue  VARCHAR(512) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id   INT UNSIGNED NULL,
  action     VARCHAR(64)  NOT NULL,                  -- login, denied, create, update, delete ...
  target     VARCHAR(190) NULL,
  ip         VARCHAR(45)  NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_log_admin (admin_id),
  KEY idx_log_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
