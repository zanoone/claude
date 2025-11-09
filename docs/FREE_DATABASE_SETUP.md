# 무료 클라우드 데이터베이스 설정 가이드

GitHub에서 코드를 사용하려면 MySQL 데이터베이스가 필요합니다. 아래는 무료로 사용할 수 있는 클라우드 데이터베이스 서비스 설정 방법입니다.

## 옵션 1: Railway (추천 ⭐)

**특징:**
- 무료 티어: 매월 $5 크레딧 제공
- 설정이 매우 간단
- GitHub 연동 자동화

**설정 방법:**

1. [Railway](https://railway.app) 접속 및 GitHub 계정으로 가입
2. "New Project" → "Provision MySQL" 클릭
3. MySQL 인스턴스 생성 완료
4. "Variables" 탭에서 연결 정보 확인:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`

5. `.env` 파일 업데이트:
```ini
database.default.hostname = your-mysql-host.railway.app
database.default.database = railway
database.default.username = root
database.default.password = your-password
database.default.DBDriver = MySQLi
database.default.port = 3306
```

6. 마이그레이션 실행:
```bash
php spark migrate
php spark db:seed InitialSeeder
```

---

## 옵션 2: PlanetScale

**특징:**
- MySQL 호환 (serverless)
- 무료 티어: 1개 DB, 5GB 스토리지
- 자동 백업

**설정 방법:**

1. [PlanetScale](https://planetscale.com) 가입
2. "Create a database" 클릭
3. Database name: `smartkim-db`
4. Region 선택 (가까운 지역)
5. "Create database" 클릭
6. "Connect" → "Create password" 클릭
7. 연결 정보 복사

**중요:** PlanetScale은 외래 키(Foreign Key)를 지원하지 않습니다.
마이그레이션 파일에서 `addForeignKey` 부분을 주석 처리하거나 제거해야 합니다.

---

## 옵션 3: Aiven

**특징:**
- 무료 티어: 1개 서비스
- MySQL, PostgreSQL 지원
- 30일 무료 체험

**설정 방법:**

1. [Aiven](https://aiven.io) 가입
2. "Create a service" 클릭
3. "MySQL" 선택
4. Free plan 선택
5. 서비스 생성 후 "Overview"에서 연결 정보 확인

---

## 옵션 4: FreeSQLDatabase.com

**특징:**
- 완전 무료
- 빠른 설정 (1분 이내)
- 제한: 5MB 스토리지

**설정 방법:**

1. [FreeSQLDatabase.com](https://www.freesqldatabase.com) 접속
2. 이메일 입력 후 데이터베이스 생성
3. 이메일로 받은 정보를 `.env`에 입력

⚠️ **주의:** 프로덕션 환경에는 적합하지 않습니다.

---

## 환경 변수 설정

`.env` 파일을 생성하고 다음 내용을 입력하세요:

```ini
CI_ENVIRONMENT = production

app.baseURL = 'https://smartkim.shop/'
app.forceGlobalSecureRequests = true

database.default.hostname = your-database-host
database.default.database = your-database-name
database.default.username = your-username
database.default.password = your-password
database.default.DBDriver = MySQLi
database.default.port = 3306

encryption.key = hex2bin:your-encryption-key-here
```

## 보안 주의사항

⚠️ **중요:**
- `.env` 파일은 절대 GitHub에 커밋하지 마세요
- `.gitignore`에 `.env`가 포함되어 있는지 확인하세요
- 프로덕션 환경에서는 강력한 암호를 사용하세요

## 초기 데이터 삽입

데이터베이스 연결 후:

```bash
# 마이그레이션 실행
php spark migrate

# 초기 데이터 삽입
php spark db:seed InitialSeeder
```

## 기본 관리자 계정

- **이메일**: admin@smartkim.shop
- **비밀번호**: admin123

⚠️ **첫 로그인 후 반드시 비밀번호를 변경하세요!**

---

## 문제 해결

### 연결 오류
- 호스트명, 포트, 비밀번호를 다시 확인
- 방화벽 설정 확인
- IP 화이트리스트 설정 (필요 시)

### 외래 키 오류 (PlanetScale)
마이그레이션 파일에서 `addForeignKey` 제거 또는 주석 처리

### 인코딩 문제
데이터베이스 charset을 `utf8mb4`로 설정

---

## 추천 사항

- 개발: Railway (간편함)
- 프로덕션: PlanetScale 또는 Aiven (안정성)
- 테스트: FreeSQLDatabase (빠른 설정)

---

**더 많은 정보는 [README.md](../README.md)를 참고하세요.**
