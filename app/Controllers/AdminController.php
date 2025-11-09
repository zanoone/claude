<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ChatRoomModel;
use App\Models\ChatMessageModel;
use CodeIgniter\Controller;

class AdminController extends Controller
{
    protected $helpers = ['form'];

    public function __construct()
    {
        // 관리자 권한 체크는 필터에서 처리
    }

    public function dashboard()
    {
        $userModel = new UserModel();
        $chatRoomModel = new ChatRoomModel();
        $chatMessageModel = new ChatMessageModel();

        $data = [
            'total_users' => $userModel->countAll(),
            'active_users' => $userModel->where('status', 'active')->countAllResults(),
            'total_rooms' => $chatRoomModel->countAll(),
            'total_messages' => $chatMessageModel->countAll(),
            'recent_users' => $userModel->orderBy('created_at', 'DESC')->limit(5)->findAll(),
        ];

        return view('admin/dashboard', $data);
    }

    public function users()
    {
        $userModel = new UserModel();
        $data = [
            'users' => $userModel->getAllUsersWithStats(),
        ];

        return view('admin/users', $data);
    }

    public function userEdit($id = null)
    {
        $userModel = new UserModel();

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'username' => "required|min_length[3]|max_length[100]|is_unique[users.username,id,{$id}]",
                'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
                'full_name' => 'required|min_length[2]|max_length[255]',
                'role' => 'required|in_list[admin,user]',
                'status' => 'required|in_list[active,inactive,banned]',
            ];

            if (!$this->validate($rules)) {
                $data = [
                    'user' => $userModel->find($id),
                    'validation' => $this->validator
                ];
                return view('admin/user_edit', $data);
            }

            $updateData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'full_name' => $this->request->getPost('full_name'),
                'phone' => $this->request->getPost('phone'),
                'role' => $this->request->getPost('role'),
                'status' => $this->request->getPost('status'),
            ];

            // 비밀번호가 입력된 경우만 업데이트
            if ($this->request->getPost('password')) {
                $updateData['password'] = $this->request->getPost('password');
            }

            if ($userModel->update($id, $updateData)) {
                session()->setFlashdata('success', '사용자 정보가 업데이트되었습니다.');
                return redirect()->to('/admin/users');
            } else {
                session()->setFlashdata('error', '업데이트 중 오류가 발생했습니다.');
            }
        }

        $data = [
            'user' => $userModel->find($id),
        ];

        return view('admin/user_edit', $data);
    }

    public function userDelete($id)
    {
        $userModel = new UserModel();

        if ($userModel->delete($id)) {
            session()->setFlashdata('success', '사용자가 삭제되었습니다.');
        } else {
            session()->setFlashdata('error', '삭제 중 오류가 발생했습니다.');
        }

        return redirect()->to('/admin/users');
    }

    public function chatRooms()
    {
        $chatRoomModel = new ChatRoomModel();
        $data = [
            'rooms' => $chatRoomModel->getRoomsWithUserCount(),
        ];

        return view('admin/chat_rooms', $data);
    }

    public function settings()
    {
        if ($this->request->getMethod() === 'post') {
            // 설정 저장 로직
            session()->setFlashdata('success', '설정이 저장되었습니다.');
        }

        return view('admin/settings');
    }
}
