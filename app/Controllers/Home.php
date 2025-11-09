<?php

namespace App\Controllers;

use App\Models\ChatRoomModel;

class Home extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $chatRoomModel = new ChatRoomModel();
        $userId = session()->get('user_id');

        $data = [
            'username' => session()->get('username'),
            'role' => session()->get('role'),
            'available_rooms' => $chatRoomModel->getUserRooms($userId),
        ];

        return view('user/dashboard', $data);
    }

    public function profile()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        return view('user/profile');
    }
}
