<nav class="navbar navbar-expand-lg navbar-custom navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="/">
            <i class="bi bi-shop"></i> SmartKim
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/">
                        <i class="bi bi-house-door"></i> 홈
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/chat/create-room">
                        <i class="bi bi-chat-dots"></i> 채팅
                    </a>
                </li>
                <?php if (session()->get('role') === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/dashboard">
                        <i class="bi bi-gear"></i> 관리자
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= esc(session()->get('username')) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile">
                            <i class="bi bi-person"></i> 프로필
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/auth/logout">
                            <i class="bi bi-box-arrow-right"></i> 로그아웃
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
