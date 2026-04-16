<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();

        // Admin Data
        $data = [
            'username'       => 'admin',
            'first_name'     => 'System',
            'last_name'      => 'Admin',
            'email'          => 'admin@gmail.com',
            'email_id'       => 'admin@gmail.com',
            'mobile'         => '9876543210',
            'paswd'          => password_hash('admin123', PASSWORD_DEFAULT),
            'user_type_id'   => 1, // Admin
            'active'         => 1,
            'status'         => 'active',
            'designation_id' => 'Administrator'
        ];

        // Use standard query to bypass potential model hooks if needed, 
        // but save() should work since we updated UserModel.
        $userModel->insert($data);
    }
}
