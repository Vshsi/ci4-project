<?php

namespace App\Controllers;

use App\Models\UserModel;

class GraphQL extends BaseController
{
    /**
     * THE SINGLE ENDPOINT
     * All GraphQL requests come here!
     */
    public function index()
    {
        $input = $this->request->getJSON();

        // Error check: If JSON is invalid or empty
        if (!$input) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid JSON input received'
            ]);
        }
        // check operation other default querry 
        $operation = $input->operation ?? 'query';
        
        try {
            switch ($operation) {
                case 'query':
                    return $this->handleQuery($input);
                case 'mutation':
                    return $this->handleMutation($input);
                default:
                    return $this->response->setJSON(['error' => 'Invalid operation']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * HANDLING QUERIES (Reading Data)
     * This mimics: query { users { id, name, email } }
     */
    private function handleQuery($input)
    {
        $db = \Config\Database::connect();
        $table = $input->table ?? 'users'; // Default to users for safety
        
        // Security check: Only allow specific tables
        $allowedTables = ['users', 'support_tickets'];
        if (!in_array($table, $allowedTables)) {
            return $this->response->setJSON(['error' => 'Table not allowed']);
        }

        $builder = $db->table($table);
        
        // Only select the fields requested
        if (isset($input->fields)) {
            // convert array to string implode
            $builder->select(implode(',', $input->fields));
        }

        // If it's a user looking at their OWN tickets
        if ($table === 'support_tickets' && session()->get('user_id') && ($input->filter_own ?? false)) {
            $builder->where('user_id', session()->get('user_id'));
        }

        // Newest at the top
        if ($table === 'support_tickets') {
            $builder->orderBy('id', 'DESC');
        }
            // run sql and sonvert result to array 
        $results = $builder->get()->getResultArray();
        return $this->response->setJSON(['data' => [$table => $results]]);
    }

    private function handleMutation($input)
    {
        $db = \Config\Database::connect();
        $table = $input->table ?? 'users';
        
        $allowedTables = ['users', 'support_tickets'];
        if (!in_array($table, $allowedTables)) {
            return $this->response->setJSON(['error' => 'Table not allowed']);
        }

        $builder = $db->table($table);
        $data = (array)$input->variables;

        // --- SCHEEMA COMPATIBILITY MAPPING (FOR USER TABLE) ---
        if ($table === 'users') {
            // Map 'phone' to 'mobile' if provided
            if (isset($data['phone'])) {
                $data['mobile'] = $data['phone'];
                unset($data['phone']);
            }
            // Map 'password' to 'paswd' and HASH IT
            if (isset($data['password'])) {
                $data['paswd'] = password_hash($data['password'], PASSWORD_DEFAULT);
                unset($data['password']);
            }
            // Sync email_id with email
            if (isset($data['email'])) {
                $data['email_id'] = $data['email'];
            }
            // Ensure status/active is set for new users
            if (!isset($data['active'])) {
                $data['active'] = 1;
            }
            if (!isset($data['status'])) {
                $data['status'] = 'active';
            }
            // Default user_type_id if not set (2 = Student)
            if (!isset($data['user_type_id'])) {
                $data['user_type_id'] = 2;
            }
        }

        if ($builder->insert($data)) {
            return $this->response->setJSON(['data' => ['insert' => ['id' => $db->insertID(), 'status' => 'Success']]]);
        }

        return $this->response->setJSON(['error' => 'Failed to save data']);
    }
}
