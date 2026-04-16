<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SupportSystem extends Migration
{
    public function up()
    {
        // --- 1. ENUM TYPES FOR SUPPORT ---
        $this->db->query("CREATE TYPE ticket_status AS ENUM ('Open', 'In Progress', 'Closed', 'Resolved')");
        $this->db->query("CREATE TYPE ticket_priority AS ENUM ('Low', 'Medium', 'High')");
        $this->db->query("CREATE TYPE communication_medium_type AS ENUM ('Direct Call', 'Live Chat', 'Email', 'Online Meeting', 'WhatsApp')");

        // --- 2. TABLE: support_tickets ---
        $this->db->query('
            CREATE TABLE IF NOT EXISTS support_tickets (
                id SERIAL PRIMARY KEY,
                user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                title VARCHAR(255) NOT NULL,
                category VARCHAR(100),
                priority ticket_priority DEFAULT \'Medium\',
                communication_method communication_medium_type,
                description TEXT NOT NULL,
                attachment_name VARCHAR(255),
                status ticket_status DEFAULT \'Open\',
                department_id VARCHAR(100),
                assigned_to INT REFERENCES users(id) ON DELETE SET NULL,
                agent_remark TEXT,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW(),
                closed_at TIMESTAMP
            )
        ');

        // --- 3. TABLE: support_replies ---
        $this->db->query('
            CREATE TABLE IF NOT EXISTS support_replies (
                id SERIAL PRIMARY KEY,
                ticket_id INT NOT NULL REFERENCES support_tickets(id) ON DELETE CASCADE,
                user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                message TEXT NOT NULL,
                attachment_name VARCHAR(255),
                created_at TIMESTAMP DEFAULT NOW()
            )
        ');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS support_replies');
        $this->db->query('DROP TABLE IF EXISTS support_tickets');
        $this->db->query('DROP TYPE IF EXISTS communication_medium_type');
        $this->db->query('DROP TYPE IF EXISTS ticket_priority');
        $this->db->query('DROP TYPE IF EXISTS ticket_status');
    }
}
