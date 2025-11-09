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
- **PHP**: 8.4
- **데이터베이스**: MariaDB
- **프론트엔드**: Bootstrap 5, jQuery
- **서버**: Ubuntu 24.04

## 설치 방법

### 1. 필수 요구사항
- PHP 8.0 이상
- MariaDB 10.3 이상
- Composer

### 2. 프로젝트 클론
```bash
git clone <repository-url>
cd smartkim
```

### 3. 의존성 설치
```bash
composer install
```

### 4. 환경 설정
`.env` 파일을 수정하여 데이터베이스 정보를 입력하세요:

```ini
database.default.hostname = localhost
database.default.database = smartkim_db
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi
```

### 5. 데이터베이스 생성
```bash
mysql -u root -p
CREATE DATABASE smartkim_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 6. 마이그레이션 실행
```bash
php spark migrate
```

### 7. 초기 데이터 삽입
```bash
php spark db:seed InitialSeeder
```

### 8. 개발 서버 실행
```bash
php spark serve
```

브라우저에서 `http://localhost:8080` 접속

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

## 배포 시 주의사항

1. `.env` 파일에서 `CI_ENVIRONMENT`를 `production`으로 변경
2. `app.forceGlobalSecureRequests`를 `true`로 설정 (HTTPS 강제)
3. 강력한 `encryption.key` 생성
4. 데이터베이스 비밀번호 변경
5. 관리자 계정 비밀번호 변경

## 라이선스

MIT License

## 문의

- 도메인: https://smartkim.shop
- 이메일: admin@smartkim.shop

---

**Developed with ❤️ by 김마트**
