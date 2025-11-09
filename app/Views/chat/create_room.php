<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/user_navbar') ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold"><i class="bi bi-plus-circle"></i> 채팅방 만들기</h1>
            <p class="text-muted">새로운 채팅방을 생성하세요</p>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= esc($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body p-4">
                    <form action="/chat/create-room" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-chat-left-text"></i> 채팅방 이름
                            </label>
                            <input type="text" class="form-control" id="name" name="name"
                                   placeholder="채팅방 이름을 입력하세요" required>
                            <?php if (isset($validation) && $validation->hasError('name')): ?>
                                <div class="text-danger small mt-1">
                                    <?= $validation->getError('name') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">
                                <i class="bi bi-shield-lock"></i> 공개 설정
                            </label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="public">공개 - 모든 사용자가 볼 수 있습니다</option>
                                <option value="private">비공개 - 초대된 사용자만 참여할 수 있습니다</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> 채팅방 만들기
                            </button>
                            <a href="/" class="btn btn-secondary">
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
