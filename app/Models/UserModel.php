<?php

namespace App\Models;

use CodeIgniter\Model;

// The Model is the "Memory" of your website.
// It is the only file allowed to talk to the Database.

class UserModel extends Model
{
    // 1. Tell the Model which table to look at
    protected $table = 'users';

    // 2. Tell the Model which column is the ID
    protected $primaryKey = 'id';

    // 3. Very Important: Tell the Model which fields it is ALLOWED to save.
    // This is for security so no one can "hack" other fields.
    protected $allowedFields = [
        'username', 'first_name', 'last_name', 'email', 'email_id', 
        'mobile', 'paswd', 'profile_image', 'user_type_id', 
        'active', 'status', 'designation_id'
    ];
}
