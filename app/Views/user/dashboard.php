<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/user_navbar') ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold">안녕하세요, <?= esc($username) ?>님!</h1>
            <p class="text-muted">SmartKim.shop에 오신 것을 환영합니다</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-person-circle" style="font-size: 4rem; color: var(--secondary-color);"></i>
                    <h5 class="card-title mt-3">내 프로필</h5>
                    <p class="card-text text-muted">프로필 정보를 확인하고 수정하세요</p>
                    <a href="/profile" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> 프로필 보기
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-chat-dots-fill" style="font-size: 4rem; color: var(--success-color);"></i>
                    <h5 class="card-title mt-3">채팅</h5>
                    <p class="card-text text-muted">다른 사용자들과 채팅하세요</p>
                    <a href="/chat/create-room" class="btn btn-success">
                        <i class="bi bi-arrow-right"></i> 채팅하기
                    </a>
                </div>
            </div>
        </div>

        <?php if ($role === 'admin'): ?>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-gear-fill" style="font-size: 4rem; color: var(--danger-color);"></i>
                    <h5 class="card-title mt-3">관리자</h5>
                    <p class="card-text text-muted">관리자 대시보드로 이동</p>
                    <a href="/admin/dashboard" class="btn btn-danger">
                        <i class="bi bi-arrow-right"></i> 관리자 페이지
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- 사용 가능한 채팅방 -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-chat-square-text"></i> 사용 가능한 채팅방</h5>
                    <a href="/chat/create-room" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> 채팅방 만들기
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($available_rooms)): ?>
                        <div class="row g-3">
                            <?php foreach ($available_rooms as $room): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-chat-left-text"></i>
                                                <?= esc($room['name']) ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <i class="bi bi-person"></i> 생성자: <?= esc($room['creator_name'] ?? '알 수 없음') ?>
                                            </p>
                                            <p class="card-text small">
                                                <?php if ($room['type'] === 'public'): ?>
                                                    <span class="badge bg-success">공개</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">비공개</span>
                                                <?php endif; ?>
                                            </p>
                                            <a href="/chat/room/<?= $room['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-box-arrow-in-right"></i> 입장
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-square-dots" style="font-size: 4rem;"></i>
                            <p class="mt-3">아직 채팅방이 없습니다. 첫 번째 채팅방을 만들어보세요!</p>
                            <a href="/chat/create-room" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> 채팅방 만들기
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layouts/footer') ?>
