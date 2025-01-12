<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <div class="d-flex align-items-center">
            <h1 class="display-5 me-auto fw-bold mb-0">User Management</h1>
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
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/user.js"></script>
</body>

</html>