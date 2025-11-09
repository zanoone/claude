<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialSeeder extends Seeder
{
    public function run()
    {
        // 관리자 계정 생성
        $this->db->table('users')->insert([
            'username'   => 'admin',
            'email'      => 'admin@smartkim.shop',
            'password'   => password_hash('admin123', PASSWORD_DEFAULT),
            'full_name'  => '관리자',
            'phone'      => '010-0000-0000',
            'role'       => 'admin',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // 테스트 사용자 계정 생성
        $this->db->table('users')->insert([
            'username'   => 'user1',
            'email'      => 'user1@smartkim.shop',
            'password'   => password_hash('user123', PASSWORD_DEFAULT),
            'full_name'  => '테스트 사용자',
            'phone'      => '010-1111-1111',
            'role'       => 'user',
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // 공개 채팅방 생성
        $this->db->table('chat_rooms')->insert([
            'name'       => '일반 채팅방',
            'type'       => 'public',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('chat_rooms')->insert([
            'name'       => '자유 토론',
            'type'       => 'public',
            'created_by' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
