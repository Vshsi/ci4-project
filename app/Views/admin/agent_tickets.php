<?= view('admin/partials/head') ?>
<?= view('admin/partials/sidebar') ?>
<?= view('admin/partials/header') ?>



<div class="main_content_iner">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="white_card card_height_100 mb_30">
                    <div class="white_card_header">
                        <div class="box_header m-0">
                            <div class="main-title">
                                <h3 class="m-0">Manage Support Tickets</h3>
                                <p class="text-muted small mt-1">Review and update student issues</p>
                            </div>
                        </div>
                    </div>
                    <div class="white_card_body">
                        
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success">
                                <?= session()->getFlashdata('success') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger">
                                <?= session()->getFlashdata('error') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="QA_section">
                            <div class="QA_table mb_30">
                                <table class="table lms_table_active" id="agentTicketsTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">Ticket ID</th>
                                            <th scope="col">Title</th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Priority</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                            <th scope="col">Date Requested</th>
                                        </tr>
                                    </thead>
                                    <tbody id="agentTicketTableBody">
                                        <!-- Real-time data will be populated here via GraphQL -->
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="spinner-border text-primary py-2" role="status"></div>
                                                <p class="mt-2 text-muted fw_600">Loading master ticket list...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Unified Ticket Update Modal (Agent) -->
<div class="modal fade" id="agentUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header tkt-modal-header tkt-border-primary">
                <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                    <h5 class="modal-title dark_text fw_700 fs-5"><i class="ti-ticket text-primary me-2"></i> Update Ticket <span id="updateModalTktId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <form id="updateTicketForm" method="POST">
                <div class="modal-body p-4">
                    <div id="updateModalLoader" class="text-center py-3" style="display:none;"><div class="spinner-border text-primary"></div></div>
                    <div id="updateModalContent">
                        <div class="mb-4">
                            <div class="mb-3">
                                <label class="tkt-label">Title</label>
                                <span id="update_subject" class="dark_text fw_600 d-block"></span>
                            </div>
                            <div class="mb-3">
                                <label class="tkt-label">Description</label>
                                <p id="update_description" class="mb-0 text-dark small"></p>
                            </div>
                            <div id="update_attachment_box"></div>
                        </div>
                        
                        <div class="pt-3 border-top">
                            <div class="mb-3">
                                <label class="dark_text fw_700 mb-2 d-block fs-14">Update Status</label>
                                <select name="status" id="update_status_val" class="form-select border-light bg-light text-dark" style="border-radius: 8px; color: #000 !important;">
                                    <option value="Open" style="color: #000;">Open</option>
                                    <option value="In Progress" style="color: #000;">In Progress</option>
                                    <option value="Resolved" style="color: #000;">Resolved</option>
                                    <option value="Closed" style="color: #000;">Closed</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="tkt-label">Response Remark</label>
                                <textarea name="agent_remark" id="update_remark_val" class="form-control border-light bg-light" rows="3" style="border-radius: 10px;" placeholder="Add resolution details..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(to right, #7c5cfc, #a88beb); border: none;">Save Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unified View Modal (Agent) -->
<div class="modal fade" id="agentViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header tkt-modal-header tkt-border-success">
                <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                    <h5 class="modal-title dark_text fw_700 fs-5"><i class="ti-eye text-success me-2"></i> Ticket Detail <span id="viewModalTktId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <span id="viewModalStatusBadge" class="badge rounded-pill px-3 py-2" style="font-size: 10px;"></span>
            </div>
            <div class="modal-body p-4" id="viewModalBody">
                <!-- Loaded via JS -->
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Unified History Modal (Agent) -->
<div class="modal fade" id="agentHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header tkt-modal-header tkt-border-info">
                <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                    <h5 class="modal-title dark_text fw_700 fs-5"><i class="ti-time text-info me-2"></i> Activity History <span id="historyModalTktId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4" id="historyModalBody">
                <!-- Loaded via JS -->
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= view('admin/partials/scripts') ?>
<script>
$(document).ready(function() {
    function getStatusStyle(status) {
        status = status || 'Open';
        let bg = '#2ecc71'; 
        if(status == 'Open') bg = '#0c62ff';
        if(status == 'In Progress') bg = '#f39c12';
        if(status == 'Closed') bg = '#7f8c8d';
        return bg;
    }

    function getPriorityBadge(priority) {
        priority = (priority || 'low').toLowerCase();
        let bClass = 'bg-info';
        if(priority == 'high') bClass = 'bg-danger';
        if(priority == 'medium') bClass = 'bg-warning';
        return `<span class="badge ${bClass}">${priority.charAt(0).toUpperCase() + priority.slice(1)}</span>`;
    }

    window.openAgentUpdateModal = function(id) {
        $('#updateModalContent').hide();
        $('#updateModalLoader').show();
        $('#updateModalTktId').text('#' + id);
        $('#agentUpdateModal').modal('show');
        $('#updateTicketForm').attr('action', '<?= site_url("support/updateStatus/") ?>' + id);

        $.get('<?= site_url("support/getTicket/") ?>' + id, function(res) {
            if (res.status === 'success') {
                const t = res.ticket;
                $('#update_subject').text(t.title);
                $('#update_description').text(t.description);
                $('#update_status_val').val(t.status || 'Open');
                $('#update_remark_val').val(t.agent_remark || '');
                
                if (t.attachment_name) {
                    $('#update_attachment_box').html(`<div class="mb-3 border-top pt-2"><label class="tkt-label">Attachment</label><a href="<?= site_url('media/view/') ?>/${t.attachment_name}" target="_blank" class="text-primary fw_600" style="font-size: 13px;"><i class="ti-clip me-1"></i>${t.attachment_name}</a></div>`);
                } else {
                    $('#update_attachment_box').empty();
                }
                $('#updateModalLoader').hide();
                $('#updateModalContent').show();
            }
        });
    };

    window.openAgentViewModal = function(id) {
        $('#viewModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        $('#viewModalTktId').text('#' + id);
        $('#agentViewModal').modal('show');

        $.get('<?= site_url("support/getTicket/") ?>' + id, function(res) {
            if (res.status === 'success') {
                const t = res.ticket;
                $('#viewModalStatusBadge').text((t.status || 'Open').toUpperCase()).css('background', getStatusStyle(t.status));
                $('#viewModalBody').html(`
                    <div class="mb-4">
                        <div class="mb-3">
                            <label class="text-muted small fw_700 mb-1 d-block text-uppercase" style="font-size: 10px;">Title</label>
                            <span class="dark_text fw_600 d-block">${t.title}</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw_700 mb-1 d-block text-uppercase" style="font-size: 10px;">Category</label>
                            <span class="dark_text fw_500 d-block">${t.category}</span>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <label class="text-muted small fw_700 mb-2 d-block text-uppercase" style="font-size: 10px;">Description</label>
                        <p class="mb-0 text-dark" style="font-size: 13.5px;">${t.description}</p>
                    </div>
                    ${t.attachment_name ? `<div class="mb-3 border-top pt-2"><label class="text-muted small fw_700 mb-1 d-block text-uppercase" style="font-size: 10px;">Attachment</label><a href="<?= site_url('media/view/') ?>/${t.attachment_name}" target="_blank" class="text-primary fw_600" style="font-size: 13px;"><i class="ti-clip me-1"></i>${t.attachment_name}</a></div>` : ''}
                    ${t.agent_remark ? `<div class="pt-3 border-top"><label class="text-success small fw_700 mb-1 d-block text-uppercase" style="font-size: 10px;">Team Response</label><p class="mb-0 text-dark fw_600" style="font-size: 13px;">${t.agent_remark}</p></div>` : ''}
                `);
            }
        });
    };

    window.openAgentHistoryModal = function(id) {
        $('#historyModalBody').html('<div class="text-center py-5"><div class="spinner-border text-info"></div></div>');
        $('#historyModalTktId').text('#' + id);
        $('#agentHistoryModal').modal('show');

        $.get('<?= site_url("support/getHistory/") ?>' + id, function(res) {
            if (res.status === 'success' && res.logs.length > 0) {
                let html = '<div class="timeline-log" style="position: relative; padding-left: 20px; border-left: 1px solid #e9ecef;">';
                res.logs.forEach(log => {
                    const date = new Date(log.created_at).toLocaleString();
                    html += `
                        <div class="log-item mb-4" style="position: relative;">
                            <div style="position: absolute; left: -26px; top: 0; width: 10px; height: 10px; border-radius: 50%; background: #17a2b8; border: 2px solid #fff;"></div>
                            <div class="small fw_700 text-muted text-uppercase" style="font-size: 10px;">${date}</div>
                            <div class="dark_text fw_600 my-1" style="font-size: 13.5px;">${log.log_message}</div>
                        </div>`;
                });
                html += '</div>';
                $('#historyModalBody').html(html);
            } else {
                $('#historyModalBody').html('<div class="text-center text-muted py-4">No activity history found.</div>');
            }
        });
    };

    function loadAgentTickets() {
        $.ajax({
            url: '<?= site_url("admin/graphql") ?>',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                operation: 'query',
                table: 'support_tickets',
                fields: ['id', 'user_id', 'title', 'category', 'priority', 'status', 'created_at', 'attachment_name']
            }),
            success: function(response) {
                const tickets = response.data.support_tickets;
                let html = '';
                if (tickets && tickets.length > 0) {
                    tickets.forEach(t => {
                        const date = new Date(t.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                        html += `
                        <tr>
                            <td><a href="javascript:void(0)" onclick="openAgentViewModal(${t.id})" class="question_content">#TKT-${t.id}</a></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw_600">${t.title}</span>
                                    ${t.attachment_name ? `<a href="<?= site_url('media/view/') ?>/${t.attachment_name}" target="_blank" class="text-primary mt-1 fw_700 fs-11"><i class="ti-clip me-1"></i>${t.attachment_name}</a>` : ''}
                                </div>
                            </td>
                            <td>${t.category}</td>
                            <td>${getPriorityBadge(t.priority)}</td>
                            <td><a href="javascript:void(0)" onclick="openAgentViewModal(${t.id})" class="status_btn tkt-status-btn" style="background:${getStatusStyle(t.status)};">${t.status || 'Open'}</a></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle tkt-action-btn" type="button" data-bs-toggle="dropdown">Action</button>
                                    <div class="dropdown-menu shadow border-0 tkt-dropdown">
                                        <a class="dropdown-item py-2 px-3 fs-13" href="javascript:void(0)" onclick="openAgentViewModal(${t.id})"><i class="ti-eye text-primary me-2"></i> View</a>
                                        ${t.status !== 'Closed' ? `<a class="dropdown-item py-2 px-3 fs-13" href="javascript:void(0)" onclick="openAgentUpdateModal(${t.id})"><i class="ti-pencil-alt text-success me-2"></i> Update</a>` : ''}
                                        <a class="dropdown-item py-2 px-3 fs-13" href="javascript:void(0)" onclick="openAgentHistoryModal(${t.id})"><i class="ti-time text-info me-2"></i> History</a>
                                    </div>
                                </div>
                            </td>
                            <td>${date}</td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center">No tickets found in queue.</td></tr>';
                }
                $('#agentTicketTableBody').html(html);
            }
        });
    }
    loadAgentTickets();
});
</script>
