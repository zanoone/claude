# SmartKim.shop - 김마트

CodeIgniter 4로 구축한 관리자/사용자 페이지 및 실시간 채팅 시스템

## 주요 기능

### 사용자 기능
- 회원가입 및 로그인
- 사용자 프로필 관리
- 실시간 채팅 (공개/비공개 채팅방)
- 채팅방 생성 및 참여
- 반응형 디자인 (Bootstrap 5)

### 관리자 기능
- 관리자 대시보드 (통계 표시)
- 사용자 관리 (생성, 수정, 삭제, 상태 변경)
- 채팅방 관리
- 시스템 설정
- 권한 관리

## 기술 스택

- **프레임워크**: CodeIgniter 4.6.3
- **PHP**: 8.0+
- **데이터베이스**: MySQL / MariaDB
- **프론트엔드**: Bootstrap 5, jQuery, Bootstrap Icons
- **서버**: Ubuntu 24.04 (또는 클라우드 호스팅)

## 빠른 시작 (GitHub 사용자용)

### 옵션 1: 무료 클라우드 데이터베이스 사용 (추천 ⭐)

**GitHub에 MySQL 서버가 없어도 됩니다!** 무료 클라우드 DB를 사용하세요.

1. **프로젝트 클론**
```bash
git clone https://github.com/your-username/smartkim.git
cd smartkim
```

2. **의존성 설치**
```bash
composer install
```

3. **무료 데이터베이스 설정**

다음 중 하나를 선택하세요:
- [Railway](https://railway.app) - 추천! 가장 간단 (무료 $5/월 크레딧)
- [PlanetScale](https://planetscale.com) - Serverless MySQL
- [Aiven](https://aiven.io) - 30일 무료 체험

📖 **상세 가이드**: [무료 데이터베이스 설정 가이드](docs/FREE_DATABASE_SETUP.md)

4. **환경 변수 설정**

`.env` 파일을 수정하여 DB 정보를 입력:
```ini
database.default.hostname = your-db-host
database.default.database = your-db-name
database.default.username = your-username
database.default.password = your-password
```

5. **데이터베이스 마이그레이션**
```bash
php spark migrate
php spark db:seed InitialSeeder
```

6. **서버 실행**
```bash
php spark serve
```

브라우저에서 `http://localhost:8080` 접속

---

### 옵션 2: 로컬 MySQL 사용

로컬에 MySQL이 설치되어 있다면:

```bash
# 1. 데이터베이스 생성
mysql -u root -p
CREATE DATABASE smartkim_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# 2. .env 파일 수정
database.default.hostname = localhost
database.default.database = smartkim_db
database.default.username = root
database.default.password = your_password

# 3. 마이그레이션 실행
composer install
php spark migrate
php spark db:seed InitialSeeder
php spark serve
```

## 기본 계정 정보

### 관리자 계정
- **이메일**: admin@smartkim.shop
- **비밀번호**: admin123

### 테스트 사용자 계정
- **이메일**: user1@smartkim.shop
- **비밀번호**: user123

## 프로젝트 구조

```
smartkim/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php      # 인증 관련
│   │   ├── AdminController.php     # 관리자 기능
│   │   ├── Home.php                # 사용자 홈
│   │   └── ChatController.php      # 채팅 기능
│   ├── Models/
│   │   ├── UserModel.php
│   │   ├── ChatRoomModel.php
│   │   ├── ChatMessageModel.php
│   │   └── ChatRoomUserModel.php
│   ├── Views/
│   │   ├── layouts/                # 공통 레이아웃
│   │   ├── auth/                   # 로그인/회원가입
│   │   ├── admin/                  # 관리자 페이지
│   │   ├── user/                   # 사용자 페이지
│   │   └── chat/                   # 채팅 페이지
│   ├── Filters/
│   │   ├── AuthFilter.php          # 인증 필터
│   │   └── AdminFilter.php         # 관리자 권한 필터
│   └── Database/
│       ├── Migrations/             # 데이터베이스 마이그레이션
│       └── Seeds/                  # 초기 데이터
├── public/                         # 공개 디렉토리
├── .env                           # 환경 설정
└── composer.json
```

## 데이터베이스 구조

### users
- 사용자 정보 (ID, 이메일, 비밀번호, 역할, 상태 등)

### chat_rooms
- 채팅방 정보 (이름, 타입, 생성자)

### chat_messages
- 채팅 메시지 (방ID, 사용자ID, 메시지 내용)

### chat_room_users
- 채팅방 참여자 정보

### ci_sessions
- 세션 관리

## 주요 URL

### 인증
- `/auth/login` - 로그인
- `/auth/register` - 회원가입
- `/auth/logout` - 로그아웃

### 사용자
- `/` - 홈 (대시보드)
- `/profile` - 프로필

### 관리자
- `/admin/dashboard` - 관리자 대시보드
- `/admin/users` - 사용자 관리
- `/admin/chat-rooms` - 채팅방 관리
- `/admin/settings` - 설정

### 채팅
- `/chat/create-room` - 채팅방 만들기
- `/chat/room/{id}` - 채팅방 입장

## 보안 기능

- 비밀번호 해시화 (password_hash)
- CSRF 보호
- XSS 필터링
- SQL Injection 방지 (Query Builder)
- 세션 기반 인증
- 역할 기반 권한 관리 (admin/user)

## GitHub에서 사용하기

### GitHub Actions로 테스트 자동화

`.github/workflows/ci.yml` 파일을 추가하면 푸시할 때마다 자동 테스트가 실행됩니다:

```yaml
name: CI

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: smartkim_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s

    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
      - run: composer install
      - run: php spark migrate
      - run: ./vendor/bin/phpunit
```

---

## 배포 시 주의사항

1. `.env` 파일에서 `CI_ENVIRONMENT`를 `production`으로 변경
2. `app.forceGlobalSecureRequests`를 `true`로 설정 (HTTPS 강제)
3. 강력한 `encryption.key` 생성
4. 데이터베이스 비밀번호 변경
5. 관리자 계정 비밀번호 변경
6. ⚠️ **`.env` 파일은 절대 GitHub에 커밋하지 마세요!**

## 라이선스

MIT License

## 문의

- 도메인: https://smartkim.shop
- 이메일: admin@smartkim.shop

---

**Developed with ❤️ by 김마트**
