<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 fw-bold">Manage Demurrage</h1>
        <button class="btn btn-primary float-end mb-2 ms-2" data-bs-toggle="modal" data-bs-target="#settingsDemurrageModal">
            <i class="fa-solid fa-plus fa-lg me-2" style="color: #ffffff;"></i> New
        </button>
        <a href="settings.php" class="text-decoration-none" style="color:inherit">
            <button class="btn btn-primary mb-2">
                <i class="fa-solid fa-arrow-left fa-lg me-2" style="color: #ffffff;"></i> Back
            </button>
        </a>
        <table class="table table-hover text-center table-light" id="demurrage-table">
            <thead>
                <th class="text-center" scope="col">Demurrage Value</th>
                <th class="text-center" scope="col">Updated At</th>
            </thead>
            <tbody id="demurrage-list">

            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="settingsDemurrageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Demurrage</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-demurrage">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="edit-demurrage-value" name="edit-demurrage-value" required>
                        <label for="edit-demurrage-value">Demurrage</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="edit-demurrage">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>