<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/admin_navbar') ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold"><i class="bi bi-gear"></i> 시스템 설정</h1>
            <p class="text-muted">시스템 설정을 관리하세요</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">사이트 정보</h5>
                </div>
                <div class="card-body">
                    <form action="/admin/settings" method="post">
                        <div class="mb-3">
                            <label for="site_name" class="form-label">사이트 이름</label>
                            <input type="text" class="form-control" id="site_name" name="site_name"
                                   value="SmartKim - 김마트">
                        </div>

                        <div class="mb-3">
                            <label for="site_description" class="form-label">사이트 설명</label>
                            <textarea class="form-control" id="site_description" name="site_description"
                                      rows="3">SmartKim.shop - 김마트</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="admin_email" class="form-label">관리자 이메일</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email"
                                   value="admin@smartkim.shop">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">기능 설정</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="allow_registration" checked>
                                <label class="form-check-label" for="allow_registration">
                                    회원가입 허용
                                </label>
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="allow_chat" checked>
                                <label class="form-check-label" for="allow_chat">
                                    채팅 기능 활성화
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> 설정 저장
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layouts/footer') ?>
