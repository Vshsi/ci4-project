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
                                <h3 class="m-0">Your Support Tickets</h3>
                            </div>
                            <div class="header_more_tool">
                                <a href="<?= site_url('admin/support_create') ?>" class="btn btn-primary" title="Create New Ticket">
                                    <i class="ti-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="white_card_body">
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success">
                                <?= session()->getFlashdata('success') ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="QA_section">
                            <div class="QA_table mb_30">
                                <table class="table lms_table_active" id="userSupportTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">Ticket ID</th>
                                            <th scope="col">Title</th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Priority</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                            <th scope="col">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ticketTableBody">
                                        <!-- Real-time data will be populated here via GraphQL -->
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="spinner-border text-primary py-2" role="status"></div>
                                                <p class="mt-2 text-muted fw_600">Loading your tickets from server...</p>
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

<!-- Unified Dynamic Ticket Detail Modal -->
<div class="modal fade" id="dynamicTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="modalLoaderContent">
            <div class="modal-header tkt-modal-header tkt-border-success">
                <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                    <h5 class="modal-title dark_text fw_700 fs-5"><i class="ti-eye text-success me-2"></i> My Ticket Detail <span id="modalTktIdLabel"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <span id="modalStatusBadge" class="badge rounded-pill px-3 py-2" style="font-size: 10px;"></span>
            </div>
            <div class="modal-body p-4" id="modalBodyDynamic">
                <!-- Content injected via JS -->
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Unified Dynamic History Modal -->
<div class="modal fade" id="dynamicHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header tkt-modal-header tkt-border-info">
                <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                    <h5 class="modal-title dark_text fw_700 fs-5"><i class="ti-time text-info me-2"></i> History Timeline <span id="modalHistTktIdLabel"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4" id="modalHistoryBody">
                <!-- Content injected via JS -->
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close History</button>
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

    window.openViewModal = function(id) {
        $('#modalBodyDynamic').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        $('#dynamicTicketModal').modal('show');
        
        $.get('<?= site_url("support/getTicket/") ?>' + id, function(res) {
            if (res.status === 'success') {
                const t = res.ticket;
                $('#modalTktIdLabel').text('#' + t.id);
                $('#modalStatusBadge').text((t.status || 'Open').toUpperCase()).css('background', getStatusStyle(t.status));
                
                $('#modalBodyDynamic').html(`
                    <div class="mb-4">
                        <div class="mb-3">
                            <label class="tkt-label"><i class="ti-pencil-alt text-primary me-2"></i>Title</label>
                            <span class="dark_text fw_600 d-block">${t.title}</span>
                        </div>
                        <div class="mb-3">
                            <label class="tkt-label"><i class="ti-folder text-primary me-2"></i>Category</label>
                            <span class="dark_text fw_500 d-block">${t.category}</span>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <label class="tkt-label">My Description</label>
                        <p class="mb-0 text-dark fs-13">${t.description}</p>
                    </div>
                    ${t.attachment_name ? `<div class="mt-3 border-top pt-3 mb-3"><label class="tkt-label">Attachment</label><a href="<?= site_url('media/view/') ?>/${t.attachment_name}" target="_blank" class="text-primary fw_600 fs-13"><i class="ti-clip me-1"></i>${t.attachment_name}</a></div>` : ''}
                    ${t.agent_remark ? `<div class="pt-3 border-top"><label class="tkt-label text-success">Team Response</label><p class="mb-0 text-dark fw_600 fs-13">${t.agent_remark}</p></div>` : ''}
                `);
            }
        });
    };

    window.openHistoryModal = function(id) {
        $('#modalHistoryBody').html('<div class="text-center py-5"><div class="spinner-border text-info"></div></div>');
        $('#modalHistTktIdLabel').text('#' + id);
        $('#dynamicHistoryModal').modal('show');
        
        $.get('<?= site_url("support/getHistory/") ?>' + id, function(res) {
            if (res.status === 'success' && res.logs.length > 0) {
                let html = '<div class="timeline-log" style="position: relative; padding-left: 20px; border-left: 2px solid #e9ecef;">';
                res.logs.forEach(log => {
                    const date = new Date(log.created_at).toLocaleString();
                    html += `
                        <div class="log-item mb-4" style="position: relative;">
                            <div style="position: absolute; left: -26px; top: 0; width: 10px; height: 10px; border-radius: 50%; background: #17a2b8; border: 2px solid #fff;"></div>
                            <div class="small fw_700 text-muted text-uppercase" style="font-size: 10px;">${date}</div>
                            <div class="dark_text fw_600 my-1 fs-13">${log.log_message}</div>
                        </div>`;
                });
                html += '</div>';
                $('#modalHistoryBody').html(html);
            } else {
                $('#modalHistoryBody').html('<div class="text-center text-muted py-4">No history records found.</div>');
            }
        });
    };

    function loadTickets() {
        $.ajax({
            url: '<?= site_url("admin/graphql") ?>',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                operation: 'query',
                table: 'support_tickets',
                filter_own: true,
                fields: ['id', 'title', 'category', 'priority', 'status', 'created_at']
            }),
            success: function(response) {
                const tickets = response.data.support_tickets;
                let html = '';
                if (tickets && tickets.length > 0) {
                    tickets.forEach(t => {
                        const priority = (t.priority || 'low').toLowerCase();
                        let bClass = 'bg-info';
                        if(priority == 'high') bClass = 'bg-danger';
                        if(priority == 'medium') bClass = 'bg-warning';
                        
                        const date = new Date(t.created_at).toLocaleDateString();

                        html += `
                        <tr>
                            <td><a href="javascript:void(0)" onclick="openViewModal(${t.id})" class="question_content">#TKT-${t.id}</a></td>
                            <td>${t.title}</td>
                            <td>${t.category}</td>
                            <td><span class="badge ${bClass}">${priority.charAt(0).toUpperCase() + priority.slice(1)}</span></td>
                            <td><a href="javascript:void(0)" onclick="openViewModal(${t.id})" class="status_btn" style="background:${getStatusStyle(t.status)}; color:white; min-width:80px; text-align:center; display:inline-block;">${t.status || 'Open'}</a></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" style="padding: 2px 12px; font-size: 13px; border-radius: 6px;">Action</button>
                                    <div class="dropdown-menu shadow border-0" style="border-radius: 8px; margin-top: 5px; min-width: 150px; padding: 5px 0;">
                                        <a class="dropdown-item py-2 px-3" href="javascript:void(0)" onclick="openViewModal(${t.id})" style="font-size: 13px;"><i class="ti-eye text-primary me-2"></i> View</a>
                                        ${t.status !== 'Closed' ? `<a class="dropdown-item py-2 px-3" href="<?= site_url('admin/support_edit/') ?>${t.id}" style="font-size: 13px;"><i class="ti-pencil-alt text-success me-2"></i> Edit</a>` : ''}
                                        <a class="dropdown-item py-2 px-3" href="javascript:void(0)" onclick="openHistoryModal(${t.id})" style="font-size: 13px;"><i class="ti-time text-info me-2"></i> History</a>
                                    </div>
                                </div>
                            </td>
                            <td>${date}</td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center">No tickets found.</td></tr>';
                }
                $('#ticketTableBody').html(html);
            }
        });
    }
    loadTickets();
});
</script>
