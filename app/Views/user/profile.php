<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/user_navbar') ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold"><i class="bi bi-person-circle"></i> 내 프로필</h1>
            <p class="text-muted">프로필 정보를 확인하고 수정하세요</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-person-circle" style="font-size: 6rem; color: var(--secondary-color);"></i>
                        </div>
                        <h3><?= esc(session()->get('username')) ?></h3>
                        <p class="text-muted"><?= esc(session()->get('email')) ?></p>
                        <?php if (session()->get('role') === 'admin'): ?>
                            <span class="badge bg-danger">관리자</span>
                        <?php else: ?>
                            <span class="badge bg-primary">사용자</span>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold">사용자명</label>
                        <p class="form-control-plaintext"><?= esc(session()->get('username')) ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">이메일</label>
                        <p class="form-control-plaintext"><?= esc(session()->get('email')) ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">역할</label>
                        <p class="form-control-plaintext">
                            <?= session()->get('role') === 'admin' ? '관리자' : '일반 사용자' ?>
                        </p>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="/" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> 돌아가기
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layouts/footer') ?>
