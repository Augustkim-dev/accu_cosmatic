# ACCU Cosmetic — 관리자 + 프론트 (M1~M6 · 1차 완성)

hellok.vn 벤치마크 기반 한국 화장품 베트남 카탈로그/백오피스.

- **M1** = 격리 Docker 스택 + DB 스키마 전체 + RBAC 3단계 + 로그인/대시보드
- **M2** = 브랜드·카테고리·제품 CRUD + 다중 이미지 업로드 + 비밀번호 변경 + 권한 게이트
- **M3** = 공개 프론트(고객 화면) — 메인·제품목록·상세·브랜드, 세션 다국어(KO/VI/EN, 기본 vi)
- **M4** = 회원·장바구니·은행이체 주문 + 마이페이지 / 관리자 주문관리·입금확인·회원관리·입금계좌
- **M5** = 구매처·문의·페이지(CMS)·소식/프로젝트·통계·시스템설정·감사로그 + 프론트(구매처 연결/문의/소식/페이지)
- **M6** = 관리자 계정 CRUD + 역할별 권한 매트릭스 편집(최종관리자) + SEO(sitemap/robots/canonical/og)

> **접속 경로:** 홈 `/` = 고객 프론트, 관리자 = `/admin`.

---

## 0. 격리 보장
기존 JunggiCall(laravel/nginx/mysql/php)을 전혀 건드리지 않습니다.
프로젝트 `arno` / 컨테이너 `arno_nginx·arno_php·arno_db` / 네트워크 `arno_net` / 볼륨 `./arno_mysql` / **외부 노출 9090 하나만**.

---

## 1. 처음 설치 (M1)
```bash
mkdir -p /home/accu
unzip accu_m2.zip -d /tmp/x && cp -rf /tmp/x/accu/. /home/accu/
cd /home/accu

cp .env.example .env
openssl rand -hex 32           # APP_KEY 용
vi .env                        # DB_PASSWORD / DB_ROOT_PASSWORD / APP_KEY

chmod -R 777 src/public/uploads   # 이미지 업로드 쓰기 권한 (php-fpm www-data)

docker-compose up -d --build
docker-compose ps                  # arno_nginx/php/db 3개 Up 확인
curl -s http://localhost:9090/health ; echo   # → ok
```
접속: `http://<서버IP>:9090/admin` · `admin@accu86.com` / `Accu!2026Admin`
(9090 외부 차단 시: `firewall-cmd --add-port=9090/tcp --permanent && firewall-cmd --reload`)

---

## 2. 이미 돌고 있는 서버에 코드 업데이트 (M2/M3/M4)
**스키마 변경 없음** → DB 볼륨/`.env` 보존, 코드만 덮어씁니다.
```bash
cd /home/accu
unzip -o /root/accu_m6.zip -d /tmp/accu_m6

cp -rf /tmp/accu_m6/accu/src/.      src/
cp -rf /tmp/accu_m6/accu/docker/.   docker/
cp -f  /tmp/accu_m6/accu/compose.yml compose.yml

chmod -R 777 src/public/uploads

docker-compose up -d --build arno_php
curl -s http://localhost:9090/health ; echo   # → ok
```

### 2-3. M4 동작 확인 (고객 주문 한 바퀴)
1. `/register` 회원가입 → 자동 로그인 → 우상단에 마이페이지/로그아웃
2. 제품 상세에서 **장바구니 담기** → 헤더 장바구니 뱃지 증가
3. `/cart` 수량변경/삭제 → **주문하기** → 받는분·연락처·입금계좌 선택 → 주문
4. 주문완료 화면에 **주문번호 + 입금계좌 안내** 표시
5. `/mypage` 에서 내 주문·상태 확인
6. 관리자 `/admin/orders` → 주문 상세 → **입금확인**(→결제완료) → 배송상태 변경 → CSV 내보내기
7. `/admin/members` 회원 목록/수정, `/admin/bank-accounts` 입금계좌 관리(최종관리자)

> 입금계좌가 하나도 없으면 주문서에 계좌 선택이 안 보입니다. M1 시드에 예시 1건이 있고, `/admin/bank-accounts`에서 실제 계좌로 교체/추가하세요.
> 권한: 운영관리자는 주문 **조회·배송상태**만, 경영/최종은 **입금확인·취소·내보내기**까지. 회원·입금계좌는 운영관리자에게 안 보입니다.

### 2-4. M5 동작 확인
- 관리자: **구매처** 등록 → **제품 수정**에서 구매처 체크 → 프론트 제품상세 하단 "구매처"에 노출
- 프론트 `/contact` 문의 작성 → 관리자 **문의관리**에서 확인·답변·상태변경
- 관리자 **콘텐츠/페이지** 에 slug `about` 페이지 등록 → 프론트 `/page/about` 노출
- 관리자 **소식** 등록 → 프론트 `/news`·`/news/{id}`
- **통계/리포트**(경영/최종), **시스템 설정**·**감사 로그**(최종관리자) 확인
### 2-5. M6 동작 확인 (최종관리자 권한 필요)
- **관리자 계정**(`/admin/admins`) — 운영/경영 관리자 계정 등록·역할부여·정지. 자기 자신/마지막 최종관리자 삭제·강등은 자동 차단
- **권한관리**(`/admin/roles`) — 역할×권한 매트릭스 체크 → 저장. 예: 운영관리자에게 `members.view` 체크 후 저장하면, 그 관리자가 **다시 로그인** 시 회원관리 메뉴가 보임(최종관리자 열은 잠김)
- **SEO** — `/sitemap.xml`, `/robots.txt` 접속 확인, 제품 상세 페이지의 og:image/canonical 태그 확인
- 이제 사이드바 전 모듈이 실제 동작합니다.

### 2-1. 샘플 데이터 넣기 (프론트 테스트용, 1회)
기존 1건과 충돌하지 않게 **중복이면 건너뛰는** 시드입니다.
```bash
cd /home/accu
docker-compose exec -T arno_db mariadb -uaccu -p'<DB_PASSWORD>' accu_cosmetic < sample_data.sql
# 끝에 brands/categories/products 개수가 출력됩니다.
```

### 2-2. 프론트 확인
- 고객 화면: `http://<서버IP>:9090/`  → 메인(브랜드·베스트·신상품)
- 제품 목록: `/products` , 브랜드별: `/products?brand=<id>` , 상세: `/products/<id>`
- 브랜드: `/brands`
- 우상단 **KO / VI / EN** 클릭 → 즉시 언어 전환(세션·쿠키 유지), 기본은 vi
- 관리자: `/admin`

---

## 3. M2 동작 확인
1. 로그인 → 좌측 **브랜드관리 → + 브랜드 등록** → 코드 `bowdon`, 이름 BOWDON, 로고 업로드 → 목록에 로고/노출 뱃지 확인
2. **카테고리** 1~2개 등록
3. **제품관리 → + 제품 등록** → 브랜드·카테고리 선택, 다국어 이름, 가격, 이미지 여러 장 업로드 → 저장 후 갤러리에 이미지 표시, 개별 삭제 동작
4. **비밀번호 변경**(우상단) → 시드 임시비번 교체
5. **권한 검증**: README 4장 SQL로 운영/경영 계정 만들고 →
   - 운영관리자: 제품·브랜드·카테고리 등록/수정 가능, 회원·설정·권한 메뉴 안 보임
   - 시도: 운영 계정으로 `/admin/account/password`는 되지만, 권한 없는 모듈 URL 직접 접근 시 403 + 감사로그

### 4. RBAC 테스트 계정 (임시)
```bash
docker-compose exec arno_db mariadb -uaccu -p accu_cosmetic
```
```sql
INSERT INTO admin_users (email,password_hash,name,role_id,status)
SELECT 'op@accu86.com','$2b$12$HWaRe6NDm8cGH6xdhS9iouOwS6UzMiSC7EtTSPI35lUPynAIcuInC','운영',id,'active' FROM admin_roles WHERE code='operator';
INSERT INTO admin_users (email,password_hash,name,role_id,status)
SELECT 'mgr@accu86.com','$2b$12$HWaRe6NDm8cGH6xdhS9iouOwS6UzMiSC7EtTSPI35lUPynAIcuInC','경영',id,'active' FROM admin_roles WHERE code='manager';
-- 임시 비번 셋 다: Accu!2026Admin
```

---

## 5. 디렉토리 구조 (M2)
```
/home/accu/
├── compose.yml          # arno 스택 (seccomp 반영)
├── docker/php/Dockerfile (세션경로 반영) · docker/nginx/default.conf
├── sql/01_schema.sql · 02_seed_rbac.sql        # M1에서 적용됨(변경 없음)
└── src/
    ├── public/index.php             # 라우터(파라미터 라우트) + 오토로더
    ├── public/uploads/              # 업로드 이미지 (chmod 777)
    ├── public/assets/css/admin.css
    ├── app/
    │   ├── Database Auth Rbac Csrf Helpers Menu  (M1)
    │   ├── Router Storage Flash                  (M2 신규)
    │   └── Controllers/
    │       ├── Auth Dashboard                    (M1)
    │       └── Brand Category Product Account    (M2 신규)
    └── views/admin/
        ├── layout login dashboard 403            (M1)
        ├── brands/{index,form}
        ├── categories/{index,form}
        ├── products/{index,form}
        └── account/password
```

---

## 6. 1차 완성 — 이후 운영/확장
M1~M6로 **관리자 등록 → 고객 주문 → 입금확인 → 권한 운영**까지 한 바퀴가 모두 닫혔습니다.
사이드바의 모든 모듈이 실제 동작합니다.

추후 확장(선택):
- 이미지 저장 **R2/S3 전환** — `Storage` 클래스만 교체(호출부 불변)
- **결제 PG**(MoMo/VNPay/카드) — 현재 은행이체에 추가
- 다국어 **URL 경로 방식**(/vi, /ko)로 SEO 강화
- **CI/CD push 트리거** 활성화(.github/workflows/deploy.yml)
- 싱가포르 VPS 이전(코드 동일, compose의 seccomp 줄만 제거)

> 기본 언어: 프론트 `vi`, 관리자 입력 `ko` (settings 테이블).
