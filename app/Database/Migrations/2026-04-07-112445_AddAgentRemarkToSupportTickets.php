<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAgentRemarkToSupportTickets extends Migration
{
    public function up()
    {
        $fields = [
            'agent_remark' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'assigned_to',
            ],
        ];
        $this->forge->addColumn('support_tickets', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('support_tickets', 'agent_remark');
    }
}
