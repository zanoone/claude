<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatRoomModel extends Model
{
    protected $table            = 'chat_rooms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'type', 'created_by'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'type' => 'required|in_list[public,private]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getRoomsWithUserCount()
    {
        return $this->select('chat_rooms.*, COUNT(DISTINCT chat_room_users.user_id) as user_count, users.username as creator_name')
                    ->join('chat_room_users', 'chat_room_users.room_id = chat_rooms.id', 'left')
                    ->join('users', 'users.id = chat_rooms.created_by', 'left')
                    ->groupBy('chat_rooms.id')
                    ->findAll();
    }

    public function getUserRooms($userId)
    {
        return $this->select('chat_rooms.*, users.username as creator_name')
                    ->join('chat_room_users', 'chat_room_users.room_id = chat_rooms.id')
                    ->join('users', 'users.id = chat_rooms.created_by', 'left')
                    ->where('chat_room_users.user_id', $userId)
                    ->orWhere('chat_rooms.type', 'public')
                    ->groupBy('chat_rooms.id')
                    ->findAll();
    }
}
