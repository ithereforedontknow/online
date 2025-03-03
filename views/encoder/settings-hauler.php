<?php
include_once('../../includes/header/header-encoder.php');
?>
<div class="content" id="content">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 me-auto fw-bold mb-0">Manage Haulers</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="settings.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Haulers</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div> <button class="btn btn-primary float-end mb-2 ms-2" data-bs-toggle="modal" data-bs-target="#addHaulerModal">
            <i class="fa-solid fa-plus fa-lg me-2" style="color: #ffffff;"></i> New
        </button>
        <a href="settings.php" class="text-decoration-none" style="color:inherit">
            <button class="btn btn-primary mb-2">
                <i class="fa-solid fa-arrow-left fa-lg me-2" style="color: #ffffff;"></i> Back
            </button>
        </a>
        <table class="table table-hover text-center table-light" id="hauler-table">
            <thead>
                <th class="text-center" scope="col">Hauler</th>
                <th class="text-center" scope="col">Address</th>
                <th class="text-center" scope="col">Branch</th>
                <th class="text-center" scope="col">Status</th>
                <th class="text-center" scope="col">Action</th>
            </thead>
            <tbody id="hauler-list">

            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="addHaulerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Add Hauler</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-hauler">
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-hauler-name" name="add-hauler-name" required>
                        <label for="add-hauler-name">Hauler</label>
                        <div class="invalid-feedback">Hauler already exists!</div>
                    </div>
                    <div class="form-floating mb-4">
                        <select class="form-select" id="add-hauler-branch" name="add-hauler-branch" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM origin");
                            $stmt->execute();
                            $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($branches as $branch) {
                                echo "<option value='" . $branch['origin_id'] . "'>" . $branch['origin_name'] . "</option>";
                            }
                            ?>
                        </select>
                        <label for="add-hauler-branch">Branch</label>
                    </div>
                    <div class="form-floating ">
                        <input type="text" class="form-control" id="add-hauler-address" name="add-hauler-address" required>
                        <label for="add-hauler-address">Address</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="add-hauler">Add</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editHaulerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Hauler</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-hauler">
                    <input type="hidden" id="edit-hauler-id" name="edit-hauler-id">
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-hauler-name" name="edit-hauler-name" required>
                        <label for="edit-hauler-name">Hauler</label>
                        <div class="invalid-feedback">Hauler already exists!</div>
                    </div>
                    <div class="form-floating mb-4">
                        <select class="form-select" id="edit-hauler-branch" name="edit-hauler-branch" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM origin");
                            $stmt->execute();
                            $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($branches as $branch) {
                                echo "<option value='" . $branch['origin_id'] . "'>" . $branch['origin_name'] . "</option>";
                            }
                            ?>
                        </select>
                        <label for="hauler_branch">Branch</label>
                    </div>
                    <div class="form-floating ">
                        <input type="text" class="form-control" id="edit-hauler-address" name="edit-hauler-address" required>
                        <label for="edit-hauler-address">Address</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="edit-hauler">Save Changes</button>
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