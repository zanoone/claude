<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatMessageModel extends Model
{
    protected $table            = 'chat_messages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['room_id', 'user_id', 'message'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    // Validation
    protected $validationRules = [
        'room_id' => 'required|integer',
        'user_id' => 'required|integer',
        'message' => 'required',
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

    public function getRoomMessages($roomId, $limit = 50)
    {
        return $this->select('chat_messages.*, users.username, users.avatar')
                    ->join('users', 'users.id = chat_messages.user_id')
                    ->where('chat_messages.room_id', $roomId)
                    ->orderBy('chat_messages.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getRecentMessages($roomId, $lastMessageId = 0)
    {
        return $this->select('chat_messages.*, users.username, users.avatar')
                    ->join('users', 'users.id = chat_messages.user_id')
                    ->where('chat_messages.room_id', $roomId)
                    ->where('chat_messages.id >', $lastMessageId)
                    ->orderBy('chat_messages.created_at', 'ASC')
                    ->findAll();
    }
}
