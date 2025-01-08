<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 fw-bold">Manage Projects</h1>
        <button class="btn btn-primary float-end mb-2 ms-2" data-bs-toggle="modal" data-bs-target="#addProjectModal">
            <i class="fa-solid fa-plus fa-lg me-2" style="color: #ffffff;"></i> New
        </button>
        <a href="settings.php" class="text-decoration-none" style="color:inherit">
            <button class="btn btn-primary mb-2">
                <i class="fa-solid fa-arrow-left fa-lg me-2" style="color: #ffffff;"></i> Back
            </button>
        </a>
        <table class="table table-hover text-center table-light" id="project-table">
            <!-- Add hauler for company wtf -->
            <thead>
                <th class="text-center" scope="col">Project</th>
                <th class="text-center" scope="col">Description</th>
                <th class="text-center" scope="col">...</th>
            </thead>
            <tbody id="project-list">

            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="addProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Add Project</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-project">
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-project-name" name="add-project-name" required>
                        <label for="add-project-name">Project</label>
                        <div class="invalid-feedback">Project already exists!</div>
                    </div>
                    <div class="form-floating ">
                        <input type="text" class="form-control" id="add-description" name="add-description" required>
                        <label for="add-description">Description</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="add-project">Add</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Project</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-project">
                    <input type="hidden" id="edit-project-id">
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-project-name" name="edit-project-name" required>
                        <label for="edit-project">Project</label>
                        <div class="invalid-feedback">Project already exists!</div>
                    </div>
                    <div class="form-floating">
                        <input type="text" class="form-control" id="edit-description" name="edit-description" required>
                        <label for="edit-description">Description</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="edit-project">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>