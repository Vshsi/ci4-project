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
                                <?php 
                                    $isEdit = isset($ticket);
                                    $ticketId = $isEdit ? $ticket['id'] : '';
                                ?>
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <h3 class="m-0"><?= $isEdit ? 'Edit Ticket Detail' : 'Academic Support System' ?></h3>
                                    <?php if($isEdit): ?>
                                        <h3 class="m-0 text-primary fw_800">#TKT-<?= $ticketId ?></h3>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="header_more_tool">
                                <a href="<?= site_url('admin/user_support') ?>" class="btn btn-secondary shadow-sm"><i class="ti-arrow-left"></i> Back</a>
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
                        
                        <?php 
                            $isEdit = isset($ticket);
                            $ticketId = $isEdit ? $ticket['id'] : '';
                            $action = $isEdit ? site_url('support/updateFullTicket/'.$ticketId) : site_url('admin/submitTicket');
                            $btnText = $isEdit ? 'Update Ticket Info' : 'Submit Ticket Now';
                        ?>

                        <form action="<?= $action ?>" method="POST" enctype="multipart/form-data" class="p-4">
                            <div class="row g-4">
                                <!-- Row 1: Subject & Category (Balanced Split) -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;" for="subject">Title</label>
                                    <input type="text" name="subject" id="subject" class="form-control text-dark fw_600" style="border-radius: 8px; border: 1px solid #dee2e6; padding: 12px; font-size: 14px; background: #fff;" placeholder="e.g. Course Access Problem" value="<?= htmlspecialchars($ticket['subject'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;" for="category">Issue Category</label>
                                    <select name="category" id="category" class="form-select form-control text-dark fw_600" style="border-radius: 8px; border: 1px solid #dee2e6; height: 48px; font-size: 14px; background: #fff;" required>
                                        <option value="">Select Category</option>
                                        <?php 
                                            $categories = ['Academic Issue', 'Fee Payment Issue', 'Login Problem', 'Exam/Result Issue', 'Technical Issue'];
                                            foreach($categories as $cat):
                                                $sel = (isset($ticket) && $ticket['category'] == $cat) ? 'selected' : '';
                                                echo "<option value=\"$cat\" $sel>$cat</option>";
                                            endforeach;
                                        ?>
                                    </select>
                                </div>

                                <!-- Row 2: Priority & Medium (Balanced Split) -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;" for="priority">Priority Level</label>
                                    <select name="priority" id="priority" class="form-select form-control text-dark fw_600" style="border-radius: 8px; border: 1px solid #dee2e6; height: 48px; font-size: 14px; background: #fff;" required>
                                        <option value="">Select Priority</option>
                                        <option value="Low" <?= (isset($ticket) && ucfirst(strtolower($ticket['priority'] ?? '')) == 'Low') ? 'selected' : '' ?>>Low Level</option>
                                        <option value="Medium" <?= (isset($ticket) && ucfirst(strtolower($ticket['priority'] ?? '')) == 'Medium') ? 'selected' : '' ?>>Medium Level</option>
                                        <option value="High" <?= (isset($ticket) && ucfirst(strtolower($ticket['priority'] ?? '')) == 'High') ? 'selected' : '' ?>>High Level</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;" for="medium">Communication Medium</label>
                                    <select name="communication_medium" id="medium" class="form-select form-control text-dark fw_600" style="border-radius: 8px; border: 1px solid #dee2e6; height: 48px; font-size: 14px; background: #fff;" required>
                                        <option value="">Select Medium</option>
                                        <option value="Direct Call" <?= (isset($ticket) && ($ticket['communication_method'] ?? '') == 'Direct Call') ? 'selected' : '' ?>>Direct Call</option>
                                        <option value="Live Chat" <?= (isset($ticket) && ($ticket['communication_method'] ?? '') == 'Live Chat') ? 'selected' : '' ?>>Live Chat</option>
                                        <option value="Email" <?= (isset($ticket) && ($ticket['communication_method'] ?? '') == 'Email') ? 'selected' : '' ?>>Email</option>
                                        <option value="WhatsApp" <?= (isset($ticket) && ($ticket['communication_method'] ?? '') == 'WhatsApp') ? 'selected' : '' ?>>WhatsApp Message</option>
                                        <option value="Online Meeting" <?= (isset($ticket) && ($ticket['communication_method'] ?? '') == 'Online Meeting') ? 'selected' : '' ?>>Online Meeting</option>
                                    </select>
                                </div>

                                <!-- Row 3: Problem Description (Full Width) -->
                                <div class="col-12 mb-2 pt-2">
                                    <label class="form-label text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;" for="description">Problem Description</label>
                                    <textarea name="description" id="description" class="form-control text-dark fw_500" style="border-radius: 8px; border: 1px solid #dee2e6; height: 130px; padding: 15px; font-size: 14px; background: #fff; line-height: 1.6;" placeholder="Describe your concern in detail..." required><?= htmlspecialchars($ticket['description'] ?? '') ?></textarea>
                                </div>

                                <!-- Row 4: Attachment (Full Width) - Restore Field Only -->
                                <div class="col-12 mb-4">
                                    <label class="form-label text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;" for="attachment">Choose Files (Optional)</label>
                                    <input type="file" name="attachment" id="attachment" class="form-control" style="border-radius: 8px; border: 1px solid #dee2e6; padding: 10px; font-size: 13px; background: #fdfdfd;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                                    <?php if($isEdit && !empty($ticket['attachment_name'])): ?>
                                        <div class="mt-2">
                                            <span class="text-muted small fw_700 text-uppercase" style="font-size: 9px;">Current File: </span>
                                            <a href="<?= site_url('media/view/'.$ticket['attachment_name']) ?>" target="_blank" class="text-primary fw_600" style="font-size: 13px;">
                                                <i class="ti-clip me-1"></i><?= htmlspecialchars($ticket['attachment_name']) ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Row 4: Submit (Simplified Tracking) -->
                                <div class="col-12 text-center pt-2">
                                    <button type="submit" class="btn text-white fw_700 px-5 py-3 shadow-sm" style="background: linear-gradient(to right, #7c5cfc, #a88beb); border-radius: 12px; border: none; font-size: 16px; letter-spacing: 0.5px;">
                                        <?= $btnText ?> <i class="ti-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('admin/partials/scripts') ?>
