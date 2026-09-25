# Me9 Market 운영 배포 체크리스트

## 현재 제외 항목

- PG 결제: 운영 PG 계약 정보와 API 자격증명 확정 전에는 실결제 완료로 전환하지 않는다.
- 소셜 로그인: 공급자 앱 키와 콜백 URL 확정 전에는 노출하지 않는다.

## 필수 환경 설정

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`
- `APP_TIMEZONE=Asia/Seoul`, `APP_LOCALE=ko`
- 운영 DB, Redis/캐시, 세션 정보를 환경변수로 설정한다.
- `QUEUE_CONNECTION=database` 또는 운영 큐 드라이버를 사용한다.
- SMTP와 `MAIL_FROM_ADDRESS`를 실제 발신 도메인으로 설정하고 SPF/DKIM/DMARC를 확인한다.
- SMS 공급자 URL과 인증정보, 발신번호를 설정한다.
- 배송 연동을 사용할 경우 모든 `SHIPROCKET_*` 값을 실제 계약 정보로 설정한다.
- `.env`는 저장소에 커밋하지 않고 웹 서버에서 직접 접근할 수 없게 한다.

## 배포 명령

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

`php artisan db:seed`는 샘플 계정이나 상품을 생성하지 않지만, 운영 초기 데이터는 관리자 화면 또는 별도 승인된 이관 절차로만 등록한다.

## 상시 프로세스

- 큐 워커: `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`
- 스케줄러: 매분 `php artisan schedule:run`을 실행한다.
- 정산 생성 일정: 매일 02:10, 중복 실행 방지 및 단일 서버 실행 설정.
- 워커는 Supervisor, systemd 또는 동등한 프로세스 관리자로 자동 재시작한다.

## 배포 전 검증

```bash
php artisan test
composer audit
npm audit
php artisan route:list --except-vendor
php artisan schedule:list
```

- 회원, 채널관리자, 전체관리자, 발주사 계정을 각각 별도로 생성해 권한 분리를 확인한다.
- 상품 등록/수정/삭제, 재고 부족 차단, 주문 생성과 재고 차감, 취소/반품/교환, 송장 등록, 정산 조회를 운영 복제 환경에서 확인한다.
- 메일과 SMS는 실제 테스트 수신처로 발송하여 큐 처리와 실패 재시도를 확인한다.
- 업로드 파일 MIME/용량 제한과 `storage` 파일 제공을 확인한다.
- DB 백업과 업로드 파일 백업을 만든 뒤 복구 절차를 실제로 시험한다.

## 배포 후 확인

- 애플리케이션 로그, 큐 실패 작업, HTTP 5xx, DB 연결 수, 디스크 사용량을 모니터링한다.
- `failed_jobs`를 알림과 연결하고 실패 원인을 확인한 뒤에만 재시도한다.
- 첫 주문은 PG 연동 완료 후 소액 실결제로 승인, 주문, 재고, 취소, 환불까지 전 과정을 확인한다.
