<?php

namespace App\Controllers;

use App\Models\SupportTicketModel;
use App\Models\SupportTicketHistoryModel;

class Support extends BaseController
{
    public function userSupport()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $ticketModel = new SupportTicketModel();
        $data = [
            'tickets' => $ticketModel->select('id, user_id, title, category, priority, communication_method, description, attachment_name, status, department_id, assigned_to, agent_remark, created_at, updated_at')
                                     ->where('user_id', session()->get('user_id'))
                                     ->orderBy('id', 'DESC')
                                     ->paginate(10),
            'pager'   => $ticketModel->pager,
            'totalTickets' => $ticketModel->where('user_id', session()->get('user_id'))->countAllResults(false)
        ];

        return view('admin/user_support', $data);
    }

    public function manageTickets()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $ticketModel = new SupportTicketModel();
        // Fetch original tickets with high-end pagination (10 per page)
        $data = [
            'tickets' => $ticketModel->select('id, user_id, title, category, priority, communication_method, description, attachment_name, status, department_id, assigned_to, agent_remark, created_at, updated_at')
                                     ->orderBy('id', 'DESC')->paginate(10),
            'pager'   => $ticketModel->pager,
            'totalTickets' => $ticketModel->countAllResults(false)
        ];

        return view('admin/agent_tickets', $data);
    }

    public function submitTicket()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // --- Server-side Validation Logic ---
        $rules = [
            'subject'              => 'required|min_length[3]|max_length[255]',
            'category'             => 'required',
            'priority'             => 'required',
            'communication_medium' => 'required',
            'description'          => 'required|min_length[10]',
            'attachment'           => 'permit_empty|ext_in[attachment,jpg,jpeg,png,pdf,doc,docx,xls,xlsx]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ticketModel = new SupportTicketModel();

        // Handle attachment upload (INLINE DB STORAGE)
        $attachmentName = null;
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $attachmentName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/tickets', $attachmentName);
        }

        $data = [
            'user_id'              => session()->get('user_id'),
            'title'                => $this->request->getPost('subject'), 
            'category'             => $this->request->getPost('category'),
            'priority'             => ucfirst(strtolower($this->request->getPost('priority'))),
            'communication_method' => $this->request->getPost('communication_medium'),
            'description'          => $this->request->getPost('description'),
            'attachment_name'      => $attachmentName,
            'status'               => 'Open',
            'department_id'        => null,
            'assigned_to'          => null,
        ];

        if ($ticketModel->save($data)) {
            $newTicketId = $ticketModel->insertID();
            $this->logChange($newTicketId, 'ticket_creation', null, 'Open', "Ticket created and prioritized as " . $data['priority']);
            return redirect()->to('/admin/user_support')->with('success', 'Your ticket has been submitted successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to save ticket. Please try again.');
        }
    }

    public function getTicketDetails($id)
    {
        $ticketModel = new SupportTicketModel();
        
        // CRITICAL: Do NOT pull attachment_data here, it is too large for JSON memory
        $ticket = $ticketModel->select('id, user_id, title, category, priority, communication_method, description, attachment_name, status, department_id, assigned_to, agent_remark, created_at, updated_at')
                              ->find($id);

        if ($ticket) {
            return $this->response->setJSON(['status' => 'success', 'ticket' => $ticket]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Ticket not found.']);
    }

    public function updateTicketStatus($id)
    {
        $ticketModel = new SupportTicketModel();
        
        $ticket = $ticketModel->select('id, user_id, title, category, priority, communication_method, description, attachment_name, status, department_id, assigned_to, agent_remark, created_at, updated_at')
                              ->find($id);
        if ($ticket && $ticket['status'] == 'Closed') {
             return redirect()->to('/admin/agent_tickets')->with('error', 'This ticket is closed and cannot be modified.');
        }

        // --- Server-side Validation for Agent Update ---
        $rules = [
            'status'       => 'required',
            'agent_remark' => 'required|min_length[5]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $status = $this->request->getPost('status');
        $remark = $this->request->getPost('agent_remark');

        $updateData = [];
        if ($status) {
            $oldStatus = $ticket['status'] ?? 'Open';
            if ($status != $oldStatus) {
                $this->logChange($id, 'status_change', $oldStatus, $status, "Ticket status updated to {$status}");
            }
            $updateData['status'] = $status;
        }

        if ($remark !== null) {
            $oldRemark = $ticket['agent_remark'] ?? '';
            if ($remark != $oldRemark) {
                $this->logChange($id, 'remark_update', $oldRemark, $remark, "Ticket remark updated");
            }
            $updateData['agent_remark'] = $remark;
        }

        if (!empty($updateData)) {
            $ticketModel->update($id, $updateData);
        }

        return redirect()->to('/admin/agent_tickets')->with('success', 'Ticket updated successfully.');
    }

    public function editTicket($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $ticketModel = new SupportTicketModel();
        $ticket = $ticketModel->select('id, user_id, title, category, priority, communication_method, description, attachment_name, status, department_id, assigned_to, agent_remark, created_at, updated_at')
                              ->find($id);

        if (!$ticket || $ticket['user_id'] != session()->get('user_id')) {
            return redirect()->to('/admin/user_support')->with('error', 'Unauthorized.');
        }

        if ($ticket['status'] == 'Closed') {
            return redirect()->to('/admin/user_support')->with('error', 'Closed tickets cannot be edited.');
        }

        return view('admin/support_ticket', ['ticket' => $ticket]);
    }

    public function updateDescription($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $ticketModel = new SupportTicketModel();
        $ticket = $ticketModel->select('id, user_id, title, category, priority, communication_method, description, attachment_name, status, department_id, assigned_to, agent_remark, created_at, updated_at')
                              ->find($id);

        if (!$ticket || $ticket['user_id'] != session()->get('user_id')) {
            return redirect()->to('/admin/user_support')->with('error', 'Unauthorized.');
        }

        if ($ticket['status'] == 'Closed') {
            return redirect()->to('/admin/user_support')->with('error', 'Closed tickets cannot be edited.');
        }

        $newDesc = $this->request->getPost('description');
        if ($newDesc) {
            if ($newDesc != $ticket['description']) {
                $this->logChange($id, 'description_edit', $ticket['description'], $newDesc, "Student updated the ticket description.");
            }
            $ticketModel->update($id, ['description' => $newDesc]);
        }

        return redirect()->to('/admin/user_support')->with('success', 'Description updated.');
    }

    public function updateFullTicket($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // --- Server-side Validation Logic ---
        $rules = [
            'subject'              => 'required|min_length[3]|max_length[255]',
            'category'             => 'required',
            'priority'             => 'required',
            'communication_medium' => 'required',
            'description'          => 'required|min_length[10]',
            'attachment'           => 'permit_empty|ext_in[attachment,jpg,jpeg,png,pdf,doc,docx,xls,xlsx]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ticketModel = new SupportTicketModel();
        $ticket = $ticketModel->select('id, user_id, title, category, priority, communication_method, description, attachment_name, status, department_id, assigned_to, agent_remark, created_at, updated_at')
                              ->find($id);

        if (!$ticket || $ticket['user_id'] != session()->get('user_id')) {
            return redirect()->to('/admin/user_support')->with('error', 'Unauthorized.');
        }

        if ($ticket['status'] == 'Closed') {
            return redirect()->to('/admin/user_support')->with('error', 'Closed tickets cannot be edited.');
        }

        $fields = [
            'title'                => 'subject', 
            'category'             => 'category',
            'priority'             => 'priority',
            'communication_method' => 'communication_medium',
            'description'          => 'description'
        ];

        $updateData = [];
        $didChange = false;

        foreach ($fields as $dbField => $postField) {
            $postValue = $this->request->getPost($postField);
            
            // Format priority if needed
            if ($dbField === 'priority' && $postValue !== null) {
                $postValue = ucfirst(strtolower($postValue));
            }

            if ($postValue !== null && $postValue != ($ticket[$dbField] ?? '')) {
                $label = str_replace('_', ' ', $dbField);
                $this->logChange($id, "edit_{$dbField}", $ticket[$dbField] ?? '', $postValue, "Ticket " . ucwords($label) . " updated");
                $updateData[$dbField] = $postValue;
                $didChange = true;
            }
        }

        // Handle file upload if any (FILESYSTEM STORAGE)
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $attachmentRef = $file->getRandomName();
            $file->move(FCPATH . 'uploads/tickets', $attachmentRef);
            
            $oldFile = $ticket['attachment_name'] ?? '';
            // Note: We could delete the old file here if desired
            
            $this->logChange($id, 'edit_attachment', $oldFile, $attachmentRef, "Ticket attachment updated");
            $updateData['attachment_name'] = $attachmentRef;
            $didChange = true;
        }

        if ($didChange) {
            $ticketModel->update($id, $updateData);
        }

        return redirect()->to('/admin/user_support')->with('success', 'Ticket information updated and logged.');
    }

    public function deleteTicket($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $ticketModel = new SupportTicketModel();
        $ticketModel->delete($id);

        return redirect()->to('/admin/agent_tickets')->with('success', 'Ticket deleted successfully.');
    }

    private function logChange($ticketId, $actionType, $oldValue, $newValue, $message)
    {
        $historyModel = new SupportTicketHistoryModel();
        $historyModel->save([
            'ticket_id'   => $ticketId,
            'changed_by'  => session()->get('user_id'),
            'action_type' => $actionType,
            'old_value'   => $oldValue,
            'new_value'   => $newValue,
            'log_message' => $message,
        ]);
    }

    public function getHistory($id)
    {
        $historyModel = new SupportTicketHistoryModel();
        $logs = $historyModel->where('ticket_id', $id)->orderBy('created_at', 'DESC')->findAll();
        return $this->response->setJSON(['status' => 'success', 'logs' => $logs]);
    }
}
