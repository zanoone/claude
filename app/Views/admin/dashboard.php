<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/admin_navbar') ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold"><i class="bi bi-speedometer2"></i> 관리자 대시보드</h1>
            <p class="text-muted">시스템 전체 현황을 확인하세요</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- 통계 카드 -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">전체 사용자</h6>
                            <h2 class="card-title mb-0"><?= esc($total_users) ?></h2>
                        </div>
                        <i class="bi bi-people" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">활성 사용자</h6>
                            <h2 class="card-title mb-0"><?= esc($active_users) ?></h2>
                        </div>
                        <i class="bi bi-person-check" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">채팅방</h6>
                            <h2 class="card-title mb-0"><?= esc($total_rooms) ?></h2>
                        </div>
                        <i class="bi bi-chat-square-text" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">전체 메시지</h6>
                            <h2 class="card-title mb-0"><?= esc($total_messages) ?></h2>
                        </div>
                        <i class="bi bi-chat-dots" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 최근 가입 사용자 -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> 최근 가입 사용자</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>사용자명</th>
                                    <th>이메일</th>
                                    <th>이름</th>
                                    <th>역할</th>
                                    <th>상태</th>
                                    <th>가입일</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_users)): ?>
                                    <?php foreach ($recent_users as $user): ?>
                                        <tr>
                                            <td><?= esc($user['id']) ?></td>
                                            <td>
                                                <i class="bi bi-person-circle"></i>
                                                <?= esc($user['username']) ?>
                                            </td>
                                            <td><?= esc($user['email']) ?></td>
                                            <td><?= esc($user['full_name']) ?></td>
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
                                            <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">등록된 사용자가 없습니다.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layouts/footer') ?>
