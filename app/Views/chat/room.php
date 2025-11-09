<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/user_navbar') ?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-0">
                        <i class="bi bi-chat-left-text"></i> <?= esc($room['name']) ?>
                    </h2>
                    <p class="text-muted mb-0">
                        <?= $room['type'] === 'public' ? '공개 채팅방' : '비공개 채팅방' ?>
                    </p>
                </div>
                <a href="/" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 나가기
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 채팅 영역 -->
        <div class="col-lg-9">
            <div class="card" style="height: 70vh;">
                <div class="card-body d-flex flex-column p-0">
                    <!-- 메시지 표시 영역 -->
                    <div id="messages-container" class="flex-grow-1 p-3 overflow-auto" style="background-color: #f8f9fa;">
                        <?php if (!empty($messages)): ?>
                            <?php foreach (array_reverse($messages) as $message): ?>
                                <div class="mb-3 <?= $message['user_id'] == session()->get('user_id') ? 'text-end' : '' ?>">
                                    <div class="d-inline-block" style="max-width: 70%;">
                                        <div class="small text-muted mb-1">
                                            <i class="bi bi-person-circle"></i>
                                            <?= esc($message['username']) ?>
                                            <span class="ms-2"><?= date('H:i', strtotime($message['created_at'])) ?></span>
                                        </div>
                                        <div class="p-3 rounded <?= $message['user_id'] == session()->get('user_id') ? 'bg-primary text-white' : 'bg-white' ?>"
                                             style="box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                            <?= nl2br(esc($message['message'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                                <p class="mt-3">아직 메시지가 없습니다. 첫 번째 메시지를 보내보세요!</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 메시지 입력 영역 -->
                    <div class="p-3 border-top bg-white">
                        <form id="message-form" class="d-flex gap-2">
                            <input type="hidden" id="room-id" value="<?= esc($room['id']) ?>">
                            <input type="text" id="message-input" class="form-control"
                                   placeholder="메시지를 입력하세요..." required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> 전송
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 참여자 목록 -->
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="bi bi-people"></i> 참여자 (<?= count($room_users) ?>)
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div class="list-group list-group-flush">
                        <?php foreach ($room_users as $user): ?>
                            <div class="list-group-item border-0">
                                <i class="bi bi-person-circle"></i>
                                <?= esc($user['username']) ?>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-danger ms-1">관리자</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    const roomId = $('#room-id').val();
    const messagesContainer = $('#messages-container');
    let lastMessageId = 0;

    // 메시지 전송
    $('#message-form').on('submit', function(e) {
        e.preventDefault();

        const message = $('#message-input').val().trim();
        if (!message) return;

        $.ajax({
            url: '/chat/send-message',
            method: 'POST',
            data: {
                room_id: roomId,
                message: message
            },
            success: function(response) {
                if (response.success) {
                    $('#message-input').val('');
                    appendMessage(response.message);
                    scrollToBottom();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('메시지 전송에 실패했습니다.');
            }
        });
    });

    // 메시지 추가
    function appendMessage(message) {
        const isMyMessage = message.user_id == <?= session()->get('user_id') ?>;
        const time = new Date(message.created_at).toLocaleTimeString('ko-KR', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const messageHtml = `
            <div class="mb-3 ${isMyMessage ? 'text-end' : ''}">
                <div class="d-inline-block" style="max-width: 70%;">
                    <div class="small text-muted mb-1">
                        <i class="bi bi-person-circle"></i>
                        ${message.username}
                        <span class="ms-2">${time}</span>
                    </div>
                    <div class="p-3 rounded ${isMyMessage ? 'bg-primary text-white' : 'bg-white'}"
                         style="box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        ${message.message.replace(/\n/g, '<br>')}
                    </div>
                </div>
            </div>
        `;

        messagesContainer.append(messageHtml);
        lastMessageId = message.id;
    }

    // 새 메시지 확인 (폴링)
    function checkNewMessages() {
        $.ajax({
            url: `/chat/get-messages/${roomId}?last_id=${lastMessageId}`,
            method: 'GET',
            success: function(response) {
                if (response.success && response.messages.length > 0) {
                    response.messages.forEach(function(message) {
                        appendMessage(message);
                    });
                    scrollToBottom();
                }
            }
        });
    }

    // 하단으로 스크롤
    function scrollToBottom() {
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
    }

    // 초기 스크롤
    scrollToBottom();

    // 3초마다 새 메시지 확인
    setInterval(checkNewMessages, 3000);

    // Enter 키로 전송 (Shift+Enter는 줄바꿈)
    $('#message-input').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#message-form').submit();
        }
    });
});
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>
