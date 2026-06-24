# ACCU Cosmetic — 관리자 플랫폼 (M1: 기반 + RBAC)

hellok.vn 벤치마크 기반 한국 화장품 베트남 카탈로그/백오피스.
**M1 = "권한이 실제로 동작하는 골격"** 까지입니다 (DB 스키마 전체 + RBAC 3단계 + 관리자 로그인/대시보드).
제품·주문 등 실제 CRUD 화면은 M2부터 붙입니다.

---

## 0. 격리 보장 (중요)
이 스택은 서버에 이미 떠 있는 **JunggiCall(laravel/nginx/mysql/php)을 전혀 건드리지 않습니다.**

| 항목 | 기존 | 이 프로젝트 |
|------|------|-------------|
| 프로젝트명 | (폴더명) | `arno` (compose `name:`) |
| 컨테이너 | nginx / mysql / php | `arno_nginx` / `arno_db` / `arno_php` |
| 네트워크 | laravel | `arno_net` |
| DB 볼륨 | `./mysql` | `./arno_mysql` |
| 호스트 포트 | 8080 / 4306 / 9999 | **9090 (하나만)** |

`arno_db`·`arno_php`는 호스트에 포트를 열지 않습니다(내부 전용). 외부 노출은 9090뿐.

---

## 1. 설치
사전: 서버에 Docker + Docker Compose v2.

```bash
# 1) 압축을 /home/accu 에 풀기 (폴더 안의 내용물이 /home/accu 바로 아래로 오게)
mkdir -p /home/accu
unzip accu_m1.zip -d /home/accu
#   결과:  /home/accu/compose.yml , /home/accu/src , /home/accu/sql ...

cd /home/accu

# 2) 환경파일 생성 후 값 채우기
cp .env.example .env
vi .env        # DB_PASSWORD / DB_ROOT_PASSWORD / APP_KEY 를 실제 값으로

# 3) 빌드 & 기동
docker compose up -d --build

# 4) 상태 확인
docker compose ps
```

> `sql/`의 스키마+시드는 **최초 기동 시 자동 실행**됩니다(빈 DB 볼륨일 때만).
> 스키마를 다시 깔려면: `docker compose down && rm -rf arno_mysql && docker compose up -d --build`

---

## 2. 접속 & 로그인
- 관리자: `http://<서버IP>:9090/admin`
- 헬스체크: `http://<서버IP>:9090/health` → `ok`

**시드 계정 (최종관리자)**
- 이메일: `admin@accu86.com`
- 임시 비번: `Accu!2026Admin`
- ⚠️ **첫 로그인 후 반드시 변경하세요** (비번 변경 화면은 M2에서 제공 예정 — 그 전엔 DB에서 직접 교체 가능)

앞단에 Cloudflare를 붙일 경우: Cloudflare → `오리진:9090`. SSL/CDN은 Cloudflare에서 처리.

---

## 3. RBAC 검증 시나리오
M1의 핵심은 **역할별 모듈 접근 차등**입니다. 아래로 확인하세요.

현재 시드 계정은 최종관리자 1개뿐이라, 운영/경영 테스트는 계정을 임시로 만들어 보면 됩니다:

```sql
-- 컨테이너 안에서 DB 접속
-- docker compose exec arno_db mariadb -u root -p accu_cosmetic

-- 운영관리자 / 경영관리자 테스트 계정 추가 (비번은 최종관리자와 동일 해시 사용)
INSERT INTO admin_users (email, password_hash, name, role_id, status)
SELECT 'op@accu86.com',  '$2b$12$HWaRe6NDm8cGH6xdhS9iouOwS6UzMiSC7EtTSPI35lUPynAIcuInC', '운영',  id, 'active' FROM admin_roles WHERE code='operator';
INSERT INTO admin_users (email, password_hash, name, role_id, status)
SELECT 'mgr@accu86.com', '$2b$12$HWaRe6NDm8cGH6xdhS9iouOwS6UzMiSC7EtTSPI35lUPynAIcuInC', '경영',  id, 'active' FROM admin_roles WHERE code='manager';
-- 임시 비번 동일: Accu!2026Admin
```

확인 포인트:

| 로그인 | 사이드바에 보여야 함 | 보이면 안 됨 |
|--------|--------------------|-------------|
| **운영관리자** op@ | 제품·브랜드·카테고리·구매처·주문·문의·콘텐츠·소식·프로젝트 | 회원·통계·관리자계정·권한·설정·로그 |
| **경영관리자** mgr@ | 위 + 회원·통계·로그 | 관리자계정·권한·설정 |
| **최종관리자** admin@ | 전부 | — |

- 메뉴는 보유 권한(`module.view`)에 따라 **자동 노출/숨김**.
- 권한 없는 URL 직접 접근 → **403** + `activity_logs`에 `denied` 기록.
- 로그인/로그아웃/거부는 모두 감사 로그에 남고, 최종/경영 관리자 대시보드에서 확인 가능.

---

## 4. 보안 체크리스트 (배포 전)
- [ ] `admin@accu86.com` 비번 변경
- [ ] `.env`의 DB 비번·APP_KEY를 강한 값으로 (예시값 금지)
- [ ] **호스트 본체의 3306(mysqld)이 외부에 열려 있는지 점검** — 이 프로젝트와 별개로 기존 노출 위험:
      `sudo firewall-cmd --list-all | grep 3306` / `sudo iptables -S | grep 3306`
- [ ] 9090은 Cloudflare(혹은 신뢰 IP)만 접근하도록 방화벽 제한 권장
- [ ] `bank_accounts` 예시 계좌를 실제 값으로 교체

---

## 5. 디렉토리 구조
```
/home/accu/
├── compose.yml            # name: arno, 3개 서비스(9090만 노출)
├── .env / .env.example    # DB·앱 환경값 (.env는 git 제외)
├── docker/
│   ├── php/Dockerfile      # php:8.3-fpm-alpine + pdo_mysql
│   └── nginx/default.conf  # 프론트컨트롤러 라우팅
├── sql/
│   ├── 01_schema.sql       # 전체 테이블(다국어 _ko/_vi/_en, 주문, RBAC)
│   └── 02_seed_rbac.sql     # 역할3·권한·매트릭스·최종관리자·설정
├── .github/workflows/deploy.yml   # CI/CD 샘플(수동 트리거 기본)
└── src/
    ├── public/index.php    # 단일 진입점 + 라우터 + 오토로더
    ├── public/assets/css/admin.css
    ├── config/config.php
    ├── app/
    │   ├── Database.php Auth.php Rbac.php Csrf.php Helpers.php Menu.php
    │   └── Controllers/AuthController.php DashboardController.php
    └── views/admin/        # layout · login · dashboard · 403
```

---

## 6. 다음 단계 (M2)
- 제품·브랜드·카테고리·이미지 업로드 CRUD (운영관리자부터 사용)
- 관리자 비번 변경 화면
- 이후 M3(프론트) · M4(회원·주문) · M5(운영기능) · M6(다국어·마감)

> 기본 언어: 프론트 `vi`, 관리자 입력 `ko` (settings 테이블, 변경 가능).
