<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatRoomUserModel extends Model
{
    protected $table            = 'chat_room_users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['room_id', 'user_id', 'joined_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    // Validation
    protected $validationRules = [];
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

    public function joinRoom($roomId, $userId)
    {
        $existing = $this->where('room_id', $roomId)
                        ->where('user_id', $userId)
                        ->first();

        if (!$existing) {
            return $this->insert([
                'room_id' => $roomId,
                'user_id' => $userId,
                'joined_at' => date('Y-m-d H:i:s')
            ]);
        }

        return true;
    }

    public function leaveRoom($roomId, $userId)
    {
        return $this->where('room_id', $roomId)
                    ->where('user_id', $userId)
                    ->delete();
    }

    public function getRoomUsers($roomId)
    {
        return $this->select('users.*')
                    ->join('users', 'users.id = chat_room_users.user_id')
                    ->where('chat_room_users.room_id', $roomId)
                    ->findAll();
    }
}
