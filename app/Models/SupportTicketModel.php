<?php

namespace App\Models;

use CodeIgniter\Model;

class SupportTicketModel extends Model
{
    protected $table            = 'support_tickets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'title',
        'category',
        'priority',
        'communication_method',
        'description',
        'attachment_name',
        'image_url',
        'status',
        'department_id',
        'assigned_to',
        'agent_remark',
        'closed_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // UI configuration: Surgically ensure all new tickets start with "Open" status
    protected $beforeInsert = ['initializeStatus'];

    protected function initializeStatus(array $data)
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'Open';
        }
        return $data;
    }
}
