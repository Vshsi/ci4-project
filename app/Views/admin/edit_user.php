<?= view('admin/partials/head') ?>
<?= view('admin/partials/sidebar') ?>
<?= view('admin/partials/header') ?>
<div class="main_content_iner overly_inner ">
    <div class="container-fluid p-0 ">
        <!-- page title  -->
        <div class="row">
            <div class="col-12">
                <div class="page_title_box d-flex flex-wrap align-items-center justify-content-between">
                    <div class="page_title_left d-flex align-items-center">
                        <h3 class="f_s_25 f_w_700 dark_text mr_30">Edit User</h3>
                        <ol class="breadcrumb page_bradcam mb-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('home') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= site_url('home/userList') ?>">Users</a></li>
                            <li class="breadcrumb-item active">Edit User</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="white_card card_height_100 mb_30">
                    <div class="white_card_header">
                        <div class="box_header m-0">
                            <div class="main-title">
                                <h3 class="m-0">Edit User Details</h3>
                            </div>
                        </div>
                    </div>
                    <div class="white_card_body">
                        <!-- Success/Error Alert -->
                        <div id="formAlert" class="alert d-none" role="alert"></div>

                        <form id="editUserForm" class="p-4">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted fw-bold mb-2">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" id="username" class="form-control" style="border-radius: 8px; border: 1px solid #dee2e6; padding: 12px;" value="<?= esc($user['username']) ?>" placeholder="Enter username">
                                    <small class="text-danger d-none" id="username_error"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted fw-bold mb-2">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control" style="border-radius: 8px; border: 1px solid #dee2e6; padding: 12px;" value="<?= esc($user['email']) ?>" placeholder="Enter email address">
                                    <small class="text-danger d-none" id="email_error"></small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted fw-bold mb-2">First Name</label>
                                    <input type="text" name="first_name" id="first_name" class="form-control" style="border-radius: 8px; border: 1px solid #dee2e6; padding: 12px;" value="<?= esc($user['first_name'] ?? '') ?>" placeholder="Enter first name">
                                    <small class="text-danger d-none" id="first_name_error"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted fw-bold mb-2">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" class="form-control" style="border-radius: 8px; border: 1px solid #dee2e6; padding: 12px;" value="<?= esc($user['last_name'] ?? '') ?>" placeholder="Enter last name">
                                    <small class="text-danger d-none" id="last_name_error"></small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted fw-bold mb-2">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="phone" class="form-control" style="border-radius: 8px; border: 1px solid #dee2e6; padding: 12px;" value="<?= esc($user['mobile'] ?? '') ?>" placeholder="Enter 10-digit phone" maxlength="10">
                                    <small class="text-danger d-none" id="phone_error"></small>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-muted fw-bold mb-2">Profile Photo (Optional)</label>
                                    <input type="file" name="photo" id="photo" class="form-control" style="border-radius: 8px; border: 1px solid #dee2e6; padding: 9px;" accept="image/*">
                                    <small class="text-danger d-none" id="photo_error"></small>
                                </div>

                                <div class="col-12 text-center mt-3">
                                    <button type="submit" id="submitBtn" class="btn text-white fw-bold px-5 py-3" style="background-color: #7c5cfc; border-radius: 8px; border: none; min-width: 200px; font-size: 16px;">
                                        Update User Info
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
</section>
<?= view('admin/partials/scripts') ?>

<script>
$(document).ready(function() {
    function clearErrors() {
        $('.text-danger').addClass('d-none').text('');
        $('input').css('border-color', '');
        $('#formAlert').addClass('d-none').text('');
    }

    function showError(field, message) {
        $('#' + field).css('border-color', 'red');
        $('#' + field + '_error').removeClass('d-none').text(message);
    }

    function showSuccess(message) {
        $('#formAlert').removeClass('d-none alert-danger').addClass('alert-success').text(message);
    }

    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();

        var formData = new FormData();
        formData.append('username', $('#username').val().trim());
        formData.append('first_name', $('#first_name').val().trim());
        formData.append('last_name', $('#last_name').val().trim());
        formData.append('email', $('#email').val().trim());
        formData.append('phone', $('#phone').val().trim());
        
        var photoFile = $('#photo')[0].files[0];
        if (photoFile) {
            formData.append('photo', photoFile);
        }

        $('#submitBtn').prop('disabled', true).text('Updating...');

        $.ajax({
            url: '<?= site_url("home/saveUserUpdate/" . $user['id']) ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'error') {
                    showError(response.field, response.message);
                    $('#submitBtn').prop('disabled', false).text('Update User');
                } else if (response.status === 'success') {
                    showSuccess('User updated successfully! Redirecting...');
                    setTimeout(function() {
                        window.location.href = '<?= site_url("home/userList") ?>';
                    }, 1500);
                }
            },
            error: function() {
                $('#formAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Something went wrong. Please try again.');
                $('#submitBtn').prop('disabled', false).text('Update User');
            }
        });
    });
});
</script>
