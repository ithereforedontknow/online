<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-5 me-auto fw-bold mb-0">Manage Origin</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="settings.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Origin</li>
                    </ol>
                </nav>
            </div>
        </div> <button class="btn btn-primary float-end mb-2 ms-2" data-bs-toggle="modal" data-bs-target="#addOriginModal">
            <i class="fa-solid fa-plus fa-lg me-2" style="color: #ffffff;"></i> New
        </button>
        <a href="settings.php" class="text-decoration-none" style="color:inherit">
            <button class="btn btn-primary mb-2">
                <i class="fa-solid fa-arrow-left fa-lg me-2" style="color: #ffffff;"></i> Back
            </button>
        </a>
        <table class="table table-hover text-center table-light" id="origin-table">
            <!-- Add hauler for company wtf -->
            <thead>
                <th class="text-center" scope="col">Origin Name</th>
                <th class="text-center" scope="col">Code</th>
                <th class="text-center" scope="col">...</th>
            </thead>
            <tbody id="origin-list">

            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="addOriginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Add origin</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-origin">
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-origin-name" name="add-origin-name" required oninput="this.value = this.value.toUpperCase()">
                        <label for="add-origin-name">Origin</label>
                        <div class="invalid-feedback">Origin already exist.</div>
                    </div>
                    <div class="form-floating">
                        <input type="text" class="form-control" id="add-origin-code" name="add-origin-code" required oninput="this.value = this.value.toUpperCase()">
                        <label for="add-origin-code">Code</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="add-origin">Add</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editOriginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Add origin</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-origin">
                    <input type="hidden" id="edit-origin-id">
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-origin-name" name="edit-origin-name" required oninput="this.value = this.value.toUpperCase()">
                        <label for="edit-origin-name">Origin</label>
                        <div class="invalid-feedback">Origin already exist.</div>

                    </div>
                    <div class="form-floating">
                        <input type="text" class="form-control" id="edit-origin-code" name="edit-origin-code" required oninput="this.value = this.value.toUpperCase()">
                        <label for="edit-origin-code">Code</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="edit-origin">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/settings.js"></script>
</body>

</html>