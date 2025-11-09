<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'email' => 'required|valid_email',
                'password' => 'required|min_length[6]',
            ];

            if (!$this->validate($rules)) {
                return view('auth/login', ['validation' => $this->validator]);
            }

            $userModel = new UserModel();
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $user = $userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    return view('auth/login', [
                        'error' => '계정이 비활성화되었거나 차단되었습니다.'
                    ]);
                }

                // 세션 설정
                session()->set([
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'logged_in' => true,
                ]);

                // 마지막 로그인 시간 업데이트
                $userModel->updateLastLogin($user['id']);

                // 역할에 따라 리다이렉트
                if ($user['role'] === 'admin') {
                    return redirect()->to('/admin/dashboard');
                } else {
                    return redirect()->to('/');
                }
            } else {
                return view('auth/login', [
                    'error' => '이메일 또는 비밀번호가 올바르지 않습니다.'
                ]);
            }
        }

        return view('auth/login');
    }

    public function register()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
                'email' => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]',
                'password_confirm' => 'required|matches[password]',
                'full_name' => 'required|min_length[2]|max_length[255]',
            ];

            if (!$this->validate($rules)) {
                return view('auth/register', ['validation' => $this->validator]);
            }

            $userModel = new UserModel();
            $data = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'full_name' => $this->request->getPost('full_name'),
                'phone' => $this->request->getPost('phone'),
                'role' => 'user',
                'status' => 'active',
            ];

            if ($userModel->insert($data)) {
                session()->setFlashdata('success', '회원가입이 완료되었습니다. 로그인해주세요.');
                return redirect()->to('/auth/login');
            } else {
                return view('auth/register', [
                    'error' => '회원가입 중 오류가 발생했습니다.'
                ]);
            }
        }

        return view('auth/register');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login');
    }
}
