<?= view('admin/partials/head') ?>
<?= view('admin/partials/sidebar') ?>
<?= view('admin/partials/header') ?>

<div class="main_content_iner overly_inner">
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="white_card card_height_100 mb_30">
                    <div class="white_card_header">
                        <div class="box_header m-0">
                            <div class="main-title">
                                <h3 class="m-0">Audit Record History</h3>
                            </div>
                            <div class="header_more_tool">
                                <a href="javascript:history.back()" class="btn btn-secondary shadow-sm"><i class="ti-arrow-left"></i> Back to Support</a>
                            </div>
                        </div>
                    </div>
                    <div class="white_card_body p-4">
                        <div class="mb-4 p-4 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); border-left: 5px solid #7c5cfc;">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <p class="mb-1 text-muted fw_800 text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Ticket Identifier</p>
                                    <h4 class="dark_text fw_800 mb-0">LOG ID: #<?= $ticket['id'] ?></h4>
                                    <p class="text-secondary mt-1 mb-0 fw_600" style="font-size: 14px;"><?= htmlspecialchars($ticket['title']) ?></p>
                                </div>
                                <div class="col-md-5 text-md-end d-flex flex-column align-items-md-end gap-2">
                                    <?php 
                                        $status = $ticket['status'] ?? 'Open';
                                        $stBg = '#2ecc71'; 
                                        if($status == 'Open') $stBg = 'var(--bg_color_3)';
                                        if($status == 'In Progress') $stBg = '#f39c12';
                                        if($status == 'Closed') $stBg = '#7f8c8d';
                                    ?>
                                    <span class="badge rounded-pill shadow-sm px-4 py-2 text-white" style="background: <?= $stBg ?>; font-size: 11px;"><?= strtoupper($status) ?></span>
                                    <span class="badge rounded-pill bg-white text-dark shadow-sm px-3 py-2 border fw_700 d-inline-block" style="font-size: 12px;">
                                        <i class="ti-tag me-1 text-primary"></i> <?= htmlspecialchars($ticket['category']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <?php if(!empty($logs)): ?>
                        <div class="timeline-wrapper mt-5" style="position: relative; padding-left: 45px;">
                            <!-- Vertical Track -->
                            <div class="timeline-track" style="position: absolute; left: 16px; top: 0; bottom: 0; width: 4px; background: rgba(124,92,252,0.1); border-radius: 2px;"></div>
                            
                            <?php foreach($logs as $log): 
                                // Mapping action types to clean labels
                                $rawAction = str_replace(['edit_', 'status_change', 'remark_update', 'ticket_creation'], ['', 'status', 'remark', 'creation'], $log['action_type']);
                                $actionLabel = ucwords(str_replace('_', ' ', $rawAction));
                                
                                // Mapping log message titles (Heading refinement)
                                $heading = htmlspecialchars($log['log_message']);
                                if(strpos(strtolower($heading), 'ticket subject updated') !== false) $heading = 'Ticket Subject Updated';
                                if(strpos(strtolower($heading), 'ticket title updated') !== false) $heading = 'Ticket Title Updated';
                            ?>
                            <div class="timeline-block mb-5" style="position: relative;">
                                <!-- Entry Point -->
                                <div class="timeline-marker shadow-sm" style="position: absolute; left: -42px; top: 2px; width: 24px; height: 24px; border-radius: 50%; background: #fff; border: 4px solid #7c5cfc; z-index: 2;"></div>
                                
                                <div class="timeline-card p-4 rounded-4 shadow-sm border-0" style="background: #fff; border-top: 3px solid #7c5cfc !important;">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <span class="text-muted fw_800 text-uppercase d-block mb-1" style="font-size: 9px; letter-spacing: 1.2px;">
                                                <i class="ti-time me-1"></i> <?= date('M d, Y | h:i A', strtotime($log['created_at'])) ?>
                                            </span>
                                            <h5 class="dark_text fw_800 m-0" style="font-size: 16px; color: #2e3440;"><?= $heading ?></h5>
                                        </div>
                                        <span class="badge rounded-pill px-3 py-2 text-primary fw_700" style="font-size: 10px; background: rgba(124, 92, 252, 0.08);">
                                            <?= $actionLabel ?>
                                        </span>
                                    </div>
                                    
                                    <?php if(!empty($log['old_value']) || !empty($log['new_value'])): ?>
                                    <div class="change-detail mt-3 p-3 rounded-3" style="background: #f8f9fa;">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-sm-5">
                                                <small class="text-muted d-block text-uppercase fw_800 mb-2" style="font-size: 9px; letter-spacing: 0.5px;">Previous State</small>
                                                <div class="text-decoration-line-through text-muted small fw_500" style="word-break: break-all; opacity: 0.7;"><?= !empty($log['old_value']) ? htmlspecialchars($log['old_value']) : '--' ?></div>
                                            </div>
                                            <div class="col-sm-2 text-center d-none d-sm-block">
                                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid #dee2e6;">
                                                    <i class="ti-arrow-right text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <small class="text-muted d-block text-uppercase fw_800 mb-2" style="font-size: 9px; letter-spacing: 0.5px;">Applied Update</small>
                                                <div class="text-success fw_700" style="font-size: 13.5px; word-break: break-all;"><?= htmlspecialchars($log['new_value']) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="ti-receipt text-muted d-block mb-3" style="font-size: 60px; opacity: 0.3;"></i>
                            <h5 class="text-muted fw_700">No History Recorded</h5>
                            <p class="text-secondary small">Modification logs will be generated automatically upon any ticket update.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('admin/partials/scripts') ?>
