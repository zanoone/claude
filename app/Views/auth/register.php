<?= $this->include('layouts/header') ?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100 py-5">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus" style="font-size: 3rem; color: var(--secondary-color);"></i>
                        <h2 class="mt-3 fw-bold">회원가입</h2>
                        <p class="text-muted">SmartKim에 가입하세요</p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?= esc($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="/auth/register" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person"></i> 사용자명
                            </label>
                            <input type="text" class="form-control" id="username" name="username"
                                   placeholder="사용자명을 입력하세요" required>
                            <?php if (isset($validation) && $validation->hasError('username')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('username') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> 이메일
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="email@example.com" required>
                            <?php if (isset($validation) && $validation->hasError('email')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('email') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">
                                <i class="bi bi-card-text"></i> 이름
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   placeholder="이름을 입력하세요" required>
                            <?php if (isset($validation) && $validation->hasError('full_name')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('full_name') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone"></i> 전화번호 (선택)
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   placeholder="010-1234-5678">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i> 비밀번호
                            </label>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="비밀번호를 입력하세요" required>
                            <?php if (isset($validation) && $validation->hasError('password')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('password') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">
                                <i class="bi bi-lock-fill"></i> 비밀번호 확인
                            </label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                   placeholder="비밀번호를 다시 입력하세요" required>
                            <?php if (isset($validation) && $validation->hasError('password_confirm')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('password_confirm') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus"></i> 회원가입
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="mb-0">이미 계정이 있으신가요?
                                <a href="/auth/login" class="text-decoration-none fw-bold">로그인</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layouts/footer') ?>
