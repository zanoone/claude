<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/admin_navbar') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold"><i class="bi bi-people"></i> 사용자 관리</h1>
            <p class="text-muted">모든 사용자를 관리하고 편집하세요</p>
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
                            <th>사용자명</th>
                            <th>이메일</th>
                            <th>이름</th>
                            <th>전화번호</th>
                            <th>역할</th>
                            <th>상태</th>
                            <th>메시지 수</th>
                            <th>최근 로그인</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= esc($user['id']) ?></td>
                                    <td>
                                        <i class="bi bi-person-circle"></i>
                                        <?= esc($user['username']) ?>
                                    </td>
                                    <td><?= esc($user['email']) ?></td>
                                    <td><?= esc($user['full_name']) ?></td>
                                    <td><?= esc($user['phone'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger">관리자</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">사용자</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <span class="badge bg-success">활성</span>
                                        <?php elseif ($user['status'] === 'inactive'): ?>
                                            <span class="badge bg-secondary">비활성</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">차단</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($user['message_count'] ?? 0) ?></td>
                                    <td><?= $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : '-' ?></td>
                                    <td>
                                        <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="/admin/users/delete/<?= $user['id'] ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('정말 삭제하시겠습니까?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">등록된 사용자가 없습니다.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layouts/footer') ?>
