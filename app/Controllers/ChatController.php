<?php

namespace App\Controllers;

use App\Models\ChatRoomModel;
use App\Models\ChatMessageModel;
use App\Models\ChatRoomUserModel;
use CodeIgniter\Controller;

class ChatController extends Controller
{
    public function room($roomId)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $chatRoomModel = new ChatRoomModel();
        $chatMessageModel = new ChatMessageModel();
        $chatRoomUserModel = new ChatRoomUserModel();

        $room = $chatRoomModel->find($roomId);
        if (!$room) {
            return redirect()->to('/')->with('error', '채팅방을 찾을 수 없습니다.');
        }

        // 사용자를 방에 추가
        $userId = session()->get('user_id');
        $chatRoomUserModel->joinRoom($roomId, $userId);

        $data = [
            'room' => $room,
            'messages' => $chatMessageModel->getRoomMessages($roomId, 50),
            'room_users' => $chatRoomUserModel->getRoomUsers($roomId),
        ];

        return view('chat/room', $data);
    }

    public function sendMessage()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => '로그인이 필요합니다.']);
        }

        $roomId = $this->request->getPost('room_id');
        $message = $this->request->getPost('message');
        $userId = session()->get('user_id');

        if (empty($message)) {
            return $this->response->setJSON(['success' => false, 'message' => '메시지를 입력해주세요.']);
        }

        $chatMessageModel = new ChatMessageModel();
        $data = [
            'room_id' => $roomId,
            'user_id' => $userId,
            'message' => $message,
        ];

        if ($chatMessageModel->insert($data)) {
            $newMessage = $chatMessageModel->select('chat_messages.*, users.username, users.avatar')
                                           ->join('users', 'users.id = chat_messages.user_id')
                                           ->find($chatMessageModel->getInsertID());

            return $this->response->setJSON([
                'success' => true,
                'message' => $newMessage
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => '메시지 전송 실패']);
        }
    }

    public function getMessages($roomId)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => '로그인이 필요합니다.']);
        }

        $lastMessageId = $this->request->getGet('last_id') ?? 0;
        $chatMessageModel = new ChatMessageModel();

        $messages = $chatMessageModel->getRecentMessages($roomId, $lastMessageId);

        return $this->response->setJSON([
            'success' => true,
            'messages' => $messages
        ]);
    }

    public function createRoom()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name' => 'required|min_length[3]|max_length[255]',
                'type' => 'required|in_list[public,private]',
            ];

            if (!$this->validate($rules)) {
                return view('chat/create_room', ['validation' => $this->validator]);
            }

            $chatRoomModel = new ChatRoomModel();
            $data = [
                'name' => $this->request->getPost('name'),
                'type' => $this->request->getPost('type'),
                'created_by' => session()->get('user_id'),
            ];

            if ($chatRoomModel->insert($data)) {
                $roomId = $chatRoomModel->getInsertID();

                // 생성자를 방에 자동 추가
                $chatRoomUserModel = new ChatRoomUserModel();
                $chatRoomUserModel->joinRoom($roomId, session()->get('user_id'));

                session()->setFlashdata('success', '채팅방이 생성되었습니다.');
                return redirect()->to('/chat/room/' . $roomId);
            } else {
                return view('chat/create_room', ['error' => '채팅방 생성 실패']);
            }
        }

        return view('chat/create_room');
    }

    public function deleteRoom($roomId)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', '권한이 없습니다.');
        }

        $chatRoomModel = new ChatRoomModel();
        if ($chatRoomModel->delete($roomId)) {
            session()->setFlashdata('success', '채팅방이 삭제되었습니다.');
        } else {
            session()->setFlashdata('error', '삭제 중 오류가 발생했습니다.');
        }

        return redirect()->to('/admin/chat-rooms');
    }
}
