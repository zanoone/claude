<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/admin_navbar') ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold"><i class="bi bi-pencil"></i> 사용자 편집</h1>
            <p class="text-muted">사용자 정보를 수정하세요</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-4">
                    <form action="/admin/users/edit/<?= esc($user['id']) ?>" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">사용자명</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?= esc($user['username']) ?>" required>
                            <?php if (isset($validation) && $validation->hasError('username')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('username') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">이메일</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= esc($user['email']) ?>" required>
                            <?php if (isset($validation) && $validation->hasError('email')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('email') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">이름</label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   value="<?= esc($user['full_name']) ?>" required>
                            <?php if (isset($validation) && $validation->hasError('full_name')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('full_name') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">전화번호</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= esc($user['phone'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">역할</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>사용자</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>관리자</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">상태</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>활성</option>
                                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>비활성</option>
                                <option value="banned" <?= $user['status'] === 'banned' ? 'selected' : '' ?>>차단</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                비밀번호 <small class="text-muted">(변경하지 않으려면 비워두세요)</small>
                            </label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> 저장
                            </button>
                            <a href="/admin/users" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> 취소
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layouts/footer') ?>
