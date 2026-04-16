<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketReplyModel extends Model
{
    protected $table            = 'ticket_replies';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['ticket_id', 'user_id', 'message', 'attachment', 'created_at', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Get Replies for a Ticket with User Info
    public function getRepliesWithUsers($ticket_id)
    {
        return $this->select('ticket_replies.id, ticket_replies.ticket_id, ticket_replies.user_id, ticket_replies.message, ticket_replies.attachment, ticket_replies.created_at, users.username, users.first_name, users.last_name, users.profile_image') 
                    ->join('users', 'users.id = ticket_replies.user_id')
                    ->where('ticket_replies.ticket_id', $ticket_id)
                    ->orderBy('ticket_replies.created_at', 'ASC')
                    ->findAll();
    }
}
