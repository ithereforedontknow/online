<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 fw-bold mb-0">User Management</h1>
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#addUserOffcanvas">
                <i class="fa-solid fa-plus fa-lg me-2" style="color: #ffffff;"></i> New User
            </button>
        </div>
        <table class="table table-hover text-center table-light" id="users-table">
            <thead>
                <tr>
                    <th class="text-center" scope="col" style="width: 5%;">ID</th>
                    <th class="text-center" scope="col" style="width: 15%;">Name</th>
                    <th class="text-center" scope="col" style="width: 15%;">Username</th>
                    <th class="text-center" scope="col" style="width: 15%;">Userlevel</th>
                    <th class="text-center" scope="col" style="width: 5%;">Status</th>
                    <th class="text-center" scope="col" style="width: 1%;">Action</th>
                </tr>
            </thead>
            <tbody id="user-list">

            </tbody>
        </table>
    </div>
</div>


<?php
include_once('../../includes/offcanvas/user-offcanvas.php');
include_once('../../includes/footer/footer-admin.php');
?>