<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content bg-light" id="content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 me-auto fw-bold mb-0">User Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">User Management</li>
                            </ol>
                        </nav>
                    </div>
                    <button class="btn btn-primary d-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fa-solid fa-plus me-2"></i>
                        New User
                    </button>
                </div>
            </div>
        </div>


        <!-- User Table Card -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="users-table">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 5%;" class="text-center">ID</th>
                                        <th scope="col" style="width: 15%;">Name</th>
                                        <th scope="col" style="width: 15%;">Username</th>
                                        <th scope="col" style="width: 15%;">User Level</th>
                                        <th scope="col" style="width: 5%;" class="text-center">Status</th>
                                        <th scope="col" style="width: 1%;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="user-list" class="border-top-0">
                                    <!-- Table content will be dynamically loaded -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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