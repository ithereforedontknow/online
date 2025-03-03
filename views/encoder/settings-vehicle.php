<?php
include_once('../../includes/header/header-encoder.php');
?>
<div class="content" id="content">
    <div class="container-fluid">
        <div class="col-12 mb-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-5 me-auto fw-bold mb-0">Manage Vehicles</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="settings.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Vehicles</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div> <button class="btn btn-primary float-end mb-2 ms-2" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i class="fa-solid fa-plus fa-lg me-2" style="color: #ffffff;"></i> New
        </button>
        <a href="settings.php" class="text-decoration-none" style="color:inherit">
            <button class="btn btn-primary mb-2">
                <i class="fa-solid fa-arrow-left fa-lg me-2" style="color: #ffffff;"></i> Back
            </button>
        </a>
        <table class="table table-hover text-center table-light" id="vehicle-table">
            <!-- Add hauler for company wtf -->
            <thead>
                <th class="text-center" scope="col">Hauler</th>
                <th class="text-center" scope="col">Plate Number</th>
                <th class="text-center" scope="col">Truck Type</th>
                <th class="text-center" scope="col">Status</th>
                <th class="text-center" scope="col">...</th>
            </thead>
            <tbody id="vehicle-list">
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Add Vehicle</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-vehicle">
                    <div class="form-floating mb-4">
                        <select name="add-hauler" id="add-hauler" class="form-select" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM hauler");
                            $stmt->execute();
                            $haulers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($haulers as $hauler) {
                                echo "<option value='" . $hauler['hauler_id'] . "'>" . $hauler['hauler_name'] . "</option>";
                            }
                            ?>
                        </select>
                        <label for="add-hauler">Hauler</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-plate-no" name="add-plate-no" required>
                        <label for="add-plate-no">Plate Number</label>
                        <div class="invalid-feedback">Plate Number already exists!</div>
                        <script>
                            document.getElementById('add-plate-no').addEventListener('input', function() {
                                this.value = this.value.toUpperCase();
                            });
                        </script>

                    </div>
                    <div class="form-floating">
                        <select name="truck-type" id="add-truck-type" class="form-select" onchange="showOthersType()" required>
                            <option value="Trailer">Trailer</option>
                            <option value="Ten Wheeler">Ten Wheeler</option>
                            <option value="Forward">Forward</option>
                            <option value="Elf">Elf</option>
                            <option value="Others">Others</option>
                        </select>
                        <label for="truck-type">Truck Type</label>
                    </div>
                    <!-- <div class="form-floating mt-4" id="others-type-container" style="display: none;">
                        <input type="text" class="form-control" id="others-type" name="others-type" required>
                        <label for="others-type">Others</label>
                    </div> -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="add-vehicle">Add</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Vehicle</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-vehicle">
                    <input type="hidden" id="edit-vehicle-id">
                    <div class="form-floating mb-4">
                        <select name="edit-hauler" id="edit-hauler" class="form-select" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM hauler");
                            $stmt->execute();
                            $haulers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($haulers as $hauler) {
                                echo "<option value='" . $hauler['hauler_id'] . "'>" . $hauler['hauler_name'] . "</option>";
                            }
                            ?>
                        </select>
                        <label for="edit-hauler">Hauler</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-plate-no" name="edit-plate-no" required>
                        <label for="edit-plate-no">Plate Number</label>
                        <div class="invalid-feedback">Plate Number already exists!</div>
                    </div>
                    <div class="form-floating">
                        <select name="edit-truck-type" id="edit-truck-type" class="form-select" onchange="showOthersType()" required>
                            <option value="Trailer">Trailer</option>
                            <option value="Ten Wheeler">Ten Wheeler</option>
                            <option value="Forward">Forward</option>
                            <option value="Elf">Elf</option>
                            <option value="Others">Others</option>
                        </select>
                        <label for="edit-truck-type">Truck Type</label>
                    </div>
                    <!-- <div class="form-floating mt-4" id="edit-others-type-container" style="display: none;">
                        <input type="text" class="form-control" id="edit-others-type" name="edit-others-type" required>
                        <label for="edit-others-type">Others</label>
                    </div> -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="edit-vehicle">Save Changes</button>
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