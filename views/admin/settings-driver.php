<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 fw-bold">Manage Driver & Helper</h1>
        <a href="settings.php" class="text-decoration-none " style="color:inherit">
            <button class="btn btn-primary mb-2">
                <i class="fa-solid fa-arrow-left fa-lg me-2" style="color: #ffffff;"></i> Back
            </button>
        </a>
        <div class="row">
            <div class="col">
                <button class="btn btn-primary float-end mb-2 ms-2" data-bs-toggle="modal" data-bs-target="#addDriverModal">
                    <i class="fa-solid fa-plus fa-lg me-2" style="color: #ffffff;"></i> New
                </button>
                <table class="table table-hover table-light text-center" id="driver-table">
                    <thead>
                        <th class="text-center" scope="col">Driver Name</th>
                        <th class="text-center" scope="col">Phone</th>
                        <th class="text-center" scope="col">Branch</th>
                        <th class="text-center" scope="col">Hauler</th>
                        <th class="text-center" scope="col">...</th>
                    </thead>
                    <tbody id="driver-list">

                    </tbody>
                </table>
            </div>
            <div class="col">
                <button class="btn btn-primary float-end mb-2 ms-2" data-bs-toggle="modal" data-bs-target="#addHelperModal">
                    <i class="fa-solid fa-plus fa-lg me-2" style="color: #ffffff;"></i> New
                </button>
                <table class="table table-hover table-light text-center" id="helper-table">
                    <thead>
                        <th class="text-center" scope="col">Helper Name</th>
                        <th class="text-center" scope="col">Phone</th>
                        <th class="text-center" scope="col">Branch</th>
                        <th class="text-center" scope="col">Hauler</th>
                        <th class="text-center" scope="col">...</th>
                    </thead>
                    <tbody id="helper-list">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addDriverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Add Driver</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-driver">
                    <div class="form-floating mb-4">
                        <select class="form-select" id="add-hauler-driver" name="add-hauler-driver" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM hauler");
                            $stmt->execute();

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['hauler_id'] . '">' . $row['hauler_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="add-hauler-driver">Hauler</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-driver-fname" name="add-driver-fname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="driver-fname">First Name</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-driver-mname" name="add-driver-mname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="add-driver-mname">Middle Name</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-driver-lname" name="add-driver-lname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="add-driver-lname">Last Name</label>
                    </div>
                    <div class="form-floating">
                        <input type="tel" class="form-control" id="add-driver-phone" name="add-driver-phone" required maxlength="11" pattern="09\d{9}" oninput="this.setCustomValidity('')" oninvalid="this.setCustomValidity('Please enter a valid phone number.')"><br>
                        <label for="add-driver-phone">Phone Number</label>
                        <div class="invalid-feedback">Please enter a valid phone number.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="add-driver">Add</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addHelperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Add Helper</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-helper">
                    <div class="form-floating mb-4">
                        <select class="form-select" id="add-hauler-helper" name="add-hauler-helper" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM hauler");
                            $stmt->execute();

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['hauler_id'] . '">' . $row['hauler_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="add-hauler-helper">Hauler</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-helper-fname" name="add-helper-fname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="add-helper-fname">First Name</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-helper-mname" name="add-helper-mname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="add-helper-mname">Middle Name</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-helper-lname" name="add-helper-lname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="add-helper-lname">Last Name</label>
                    </div>
                    <div class="form-floating">
                        <input type="tel" class="form-control" id="add-helper-phone" name="add-helper-phone" required pattern="09\d{9}" maxlength="11" oninput="this.setCustomValidity('')" oninvalid="this.setCustomValidity('Please enter a valid phone number.')"><br>
                        <label for="add-helper-phone">Phone Number</label>
                        <div class="invalid-feedback">Please enter a valid phone number.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="add-helper">Add</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editDriverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Add Driver</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-driver">
                    <input type="hidden" id="edit-driver-id" name="edit-driver-id">
                    <div class="form-floating mb-4">
                        <select class="form-select" id="edit-hauler-driver" name="edit-hauler-driver" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM hauler");
                            $stmt->execute();

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['hauler_id'] . '">' . $row['hauler_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="edit-hauler-driver">Hauler</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-driver-fname" name="edit-driver-fname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="edit-driver-fname">First Name</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-driver-mname" name="edit-driver-mname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="edit-driver-mname">Middle Name</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-driver-lname" name="edit-driver-lname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="edit-driver-lname">Last Name</label>
                    </div>
                    <div class="form-floating">
                        <input type="tel" class="form-control" id="edit-driver-phone" name="edit-driver-phone" required pattern="09\d{9}" maxlength="11" oninput="this.setCustomValidity('')" oninvalid="this.setCustomValidity('Please enter a valid phone number.')">
                        <label for="edit-driver-phone">Phone Number</label>
                        <div class="invalid-feedback">Please enter a valid phone number.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="edit-driver">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editHelperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Add Helper</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-helper">
                    <input type="hidden" id="edit-helper-id" name="edit-helper-id">
                    <div class="form-floating mb-4">
                        <select class="form-select" id="edit-hauler-helper" name="edit-hauler-helper" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM hauler");
                            $stmt->execute();

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['hauler_id'] . '">' . $row['hauler_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="edit-hauler-helper">Hauler</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-helper-fname" name="edit-helper-fname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="edit-helper-fname">First Name</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-helper-mname" name="edit-helper-mname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="edit-helper-mname">Middle Name</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-helper-lname" name="edit-helper-lname" required oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');">
                        <label for="edit-helper-lname">Last Name</label>
                    </div>
                    <div class="form-floating">
                        <input type="tel" class="form-control" id="edit-helper-phone" name="edit-helper-phone" required maxlength="11" pattern="09\d{9}" oninput="this.setCustomValidity('')" oninvalid="this.setCustomValidity('Please enter a valid phone number.')"><br>
                        <label for="edit-helper-phone">Phone Number</label>
                        <div class="invalid-feedback">Please enter a valid phone number.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="edit-helper">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>