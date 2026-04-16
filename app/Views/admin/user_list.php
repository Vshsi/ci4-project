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
                        <h3 class="f_s_25 f_w_700 dark_text mr_30">User Management</h3>
                        <ol class="breadcrumb page_bradcam mb-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('home') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">User List</li>
                        </ol>
                    </div>
                    <div class="page_title_right">
                        <a href="<?= site_url('home/addUser') ?>" class="btn_1">+ Add New User</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="white_card card_height_100 mb_30 pt-4">
                    <div class="white_card_body">
                        <div class="QA_section">
                            <div class="white_box_tittle list_header">
                                <h4>User List</h4>
                            </div>

                            <div class="QA_table mb_30">
                                <!-- table-responsive -->
                                <table class="table lms_table_active3" id="userTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Profile</th>
                                            <th scope="col">Username</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Phone</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="userTableBody">
                                        <!-- Data will be loaded via GraphQL -->
                                        <tr>
                                            <td colspan="7" class="text-center">Loading users...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div id="paginationContainer" class="d-flex justify-content-center"></div>

                        </div>
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
    function loadUsers() {
        const query = {
            operation: 'query',
            fields: ['id', 'username', 'first_name', 'last_name', 'email', 'mobile', 'profile_image']
        };

        $.ajax({
            url: '<?= site_url("admin/graphql") ?>', 
            type: 'POST',
            contentType: 'application/json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>' // Security token
            },
            data: JSON.stringify(query),
            success: function(response) {
                console.log("GraphQL Response:", response); // For debugging
                
                if (response.error || response.status === 'error') {
                    $('#userTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error: ' + (response.error || response.message) + '</td></tr>');
                    return;
                }

                const users = response.data.users;
                let html = '';
                
                if (users && users.length > 0) {
                    users.forEach((user, index) => {
                        html += `
                        <tr>
                            <th scope="row">${index + 1}</th>
                            <td>
                                <img src="${user.profile_image ? '<?= site_url("media/view/") ?>' + user.profile_image : '<?= base_url("img/client_img.png") ?>'}" alt="Profile" style="width:40px; height:40px; object-fit:cover; border-radius:50%;">
                            </td>
                            <td>${user.username}</td>
                            <td>${user.first_name || '-'}</td>
                            <td>${user.last_name || '-'}</td>
                            <td>${user.email}</td>
                            <td>${user.mobile || '-'}</td>
                            <td>
                                <div class="action_btns d-flex">
                                    <a href="<?= site_url('home/edit/') ?>${user.id}" class="action_btn mr_10" title="Edit">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('home/delete_user/') ?>${user.id}" class="action_btn" title="Delete" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center">No users found.</td></tr>';
                }
                
                $('#userTableBody').html(html);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                console.log("Response Text:", xhr.responseText);
                $('#userTableBody').html('<tr><td colspan="7" class="text-center text-danger">Failed to load data (Server Error).</td></tr>');
            }
        });
    }

    loadUsers(); // Run the GraphQL query on page load
});
</script>
