<?= $this->include('layouts/header') ?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-shop" style="font-size: 3rem; color: var(--secondary-color);"></i>
                        <h2 class="mt-3 fw-bold">SmartKim</h2>
                        <p class="text-muted">김마트에 오신 것을 환영합니다</p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?= esc($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="/auth/login" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> 이메일
                            </label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email"
                                   placeholder="email@example.com" required>
                            <?php if (isset($validation) && $validation->hasError('email')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('email') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i> 비밀번호
                            </label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password"
                                   placeholder="비밀번호를 입력하세요" required>
                            <?php if (isset($validation) && $validation->hasError('password')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('password') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> 로그인
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="mb-0">계정이 없으신가요?
                                <a href="/auth/register" class="text-decoration-none fw-bold">회원가입</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layouts/footer') ?>
