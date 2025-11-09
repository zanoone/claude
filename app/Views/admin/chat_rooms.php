<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/admin_navbar') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold"><i class="bi bi-chat-square-text"></i> 채팅방 관리</h1>
            <p class="text-muted">모든 채팅방을 관리하세요</p>
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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>채팅방 이름</th>
                            <th>타입</th>
                            <th>생성자</th>
                            <th>참여자 수</th>
                            <th>생성일</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td><?= esc($room['id']) ?></td>
                                    <td>
                                        <i class="bi bi-chat-left-text"></i>
                                        <?= esc($room['name']) ?>
                                    </td>
                                    <td>
                                        <?php if ($room['type'] === 'public'): ?>
                                            <span class="badge bg-success">공개</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">비공개</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($room['creator_name'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= esc($room['user_count'] ?? 0) ?>명
                                        </span>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($room['created_at'])) ?></td>
                                    <td>
                                        <a href="/chat/room/<?= $room['id'] ?>" class="btn btn-sm btn-info text-white">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/chat/delete-room/<?= $room['id'] ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('정말 삭제하시겠습니까?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">등록된 채팅방이 없습니다.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layouts/footer') ?>
