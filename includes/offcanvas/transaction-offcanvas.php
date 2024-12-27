<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="addTransactionOffcanvas" aria-labelledby="addTransactionOffcanvas-label">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="addTransactionOffcanvas-label">New <span class="fw-bold">transaction</span> record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="add-transaction">
            <div class="row">
                <div class="col">
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="to-reference" name="to-reference" required pattern="^\d+-[A-Za-z]{2}$" title="0000-XX" maxlength="7" style="text-transform: uppercase;">
                        <label for="to-reference" class="form-label">TO Reference #</label>
                        <div class="invalid-feedback">TO Reference already exist</div>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="guia" name="guia" required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                        <label for="guia">GUIA</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input list="haulers" class="form-control" name="hauler" id="hauler" required autocomplete="off">
                        <label for="hauler">Hauler</label>
                        <datalist id="haulers">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM hauler");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['hauler_name'] . '" data-id="' . $row['hauler_id'] . '"></option>';
                            }
                            ?>
                        </datalist>
                        <div class="invalid-feedback">Hauler does not exist</div>
                    </div>
                    <div class="form-floating mb-4">
                        <input list="plate-numbers" class="form-control" name="plate-number" id="plate-number" required autocomplete="off">
                        <label for="plate-number">Plate Number</label>
                        <datalist id="plate-numbers">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM vehicle");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['plate_number'] . ' : ' . $row['truck_type'] . '" data-id="' . $row['vehicle_id'] . '"></option>';
                            }
                            ?>
                        </datalist>
                        <div class="invalid-feedback">Plate Number does not exist</div>
                    </div>

                    <div class="form-floating mb-4">
                        <input list="driver-names" class="form-control" name="driver-name" id="driver-name" required autocomplete="off">
                        <label for="driver-name">Driver Name</label>
                        <datalist id="driver-names">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM driver");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['driver_fname'] . ' ' . $row['driver_lname'] . '" data-id="' . $row['driver_id'] . '"></option>';
                            }
                            ?>
                        </datalist>
                        <div class="invalid-feedback">Driver Name does not exist</div>
                    </div>

                    <div class="form-floating mb-4">
                        <input list="helper-names" class="form-control" name="helper-name" id="helper-name" required autocomplete="off">
                        <label for="helper-name">Helper Name</label>
                        <datalist id="helper-names">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM helper");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['helper_fname'] . ' ' . $row['helper_lname'] . '" data-id="' . $row['helper_id'] . '"></option>';
                            }
                            ?>
                        </datalist>
                        <div class="invalid-feedback">Helper Name does not exist</div>
                    </div>
                </div>
                <div class="col">
                    <div class="form-floating mb-4">
                        <select class="form-select" name="project" id="project" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM project");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['project_id'] . '">' . $row['project_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="project">Project</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="number" class="form-control" id="no-of-bales" name="no-of-bales" required autocomplete="off">
                        <label for="no-of-bales">No of Bales</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="number" class="form-control" id="kilos" name="kilos" required autocomplete="off">
                        <label for="kilos">Kilos</label>
                    </div>
                    <div class="form-floating mb-4">
                        <select name="origin" id="origin" class="form-select" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM origin");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['origin_id'] . '">' . $row['origin_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="origin">Origin</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="datetime-local" class="form-control" id="time-departure" name="time-departure" required autocomplete="off">
                        <label for="time-departure">Time of Departure</label>
                        <div class="invalid-feedback">Departure time must be earlier than arrival time</div>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="datetime-local" class="form-control" id="arrival-time" name="arrival-time" required autocomplete="off">
                        <label for="arrival-time">Arrival Time</label>
                    </div>
                    <input type="hidden" id="created_by" name="created_by" value="<?= $_SESSION['username']; ?>">
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer d-flex justify-content-end p-3 border-top sticky-bottom bg-white">
        <button type="button" class="btn btn-dark me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary" form="add-transaction">Add to Arrived</button>
    </div>
</div>
<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="editTransactionOffcanvas" aria-labelledby="editTransactionLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title " id="editTransactionLabel">Edit <span class="fw-bold">transaction</span> record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="edit-transaction-actual-form">
            <div class="row">
                <div class="col">
                    <input type="hidden" id="edit-transaction-id-new" name="edit-transaction-id-new">
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-to-reference" name="edit-to-reference" required>
                        <label for="to-reference">TO Reference #</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-guia" name="edit-guia" required>
                        <label for="edit-guia">GUIA</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input list="edit-haulers" class="form-control" name="edit-hauler" id="edit-hauler" required autocomplete="off">
                        <label for="edit-hauler">Hauler</label>
                        <datalist id="edit-haulers">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM hauler");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['hauler_name'] . '" data-id="' . $row['hauler_id'] . '"></option>';
                            }
                            ?>
                        </datalist>
                        <div class="invalid-feedback">Hauler does not exist</div>
                    </div>

                    <div class="form-floating mb-4">
                        <input list="edit-plate-numbers" class="form-control" name="edit-plate-number" id="edit-plate-number" required autocomplete="off">
                        <label for="edit-plate-number">Plate Number</label>
                        <datalist id="edit-plate-numbers">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM vehicle");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['plate_number'] . ' : ' . $row['truck_type'] . '" data-id="' . $row['vehicle_id'] . '"></option>';
                            }
                            ?>
                        </datalist>
                        <div class="invalid-feedback">Plate Number does not exist</div>
                    </div>

                    <div class="form-floating mb-4">
                        <input list="edit-driver-names" class="form-control" name="edit-driver-name" id="edit-driver-name" required autocomplete="off">
                        <label for="edit-driver-name">Driver Name</label>
                        <datalist id="edit-driver-names">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM driver");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['driver_fname'] . ' ' . $row['driver_lname'] . '" data-id="' . $row['driver_id'] . '"></option>';
                            }
                            ?>
                        </datalist>
                        <div class="invalid-feedback">Driver Name does not exist</div>
                    </div>

                    <div class="form-floating mb-4">
                        <input list="edit-helper-names" class="form-control" name="edit-helper-name" id="edit-helper-name" required autocomplete="off">
                        <label for="edit-helper-name">Helper Name</label>
                        <datalist id="edit-helper-names">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM helper");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['helper_fname'] . ' ' . $row['helper_lname'] . '" data-id="' . $row['helper_id'] . '"></option>';
                            }
                            ?>
                        </datalist>
                        <div class="invalid-feedback">Helper Name does not exist</div>
                    </div>
                </div>

                <div class="col">
                    <div class="form-floating mb-4">
                        <select class="form-select" name="project" id="edit-project" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM project");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['project_id'] . '">' . $row['project_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="edit-project">Project</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="number" class="form-control" id="edit-no-of-bales" name="edit-no-of-bales" required>
                        <label for="edit-no-of-bales">No of Bales</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="number" class="form-control" id="edit-kilos" name="edit-kilos" required>
                        <label for="edit-kilos">Kilos</label>
                    </div>
                    <div class="form-floating mb-4">
                        <select name="origin" id="edit-origin" class="form-select" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM origin");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['origin_id'] . '">' . $row['origin_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="edit-origin">Origin</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="datetime-local" class="form-control" id="edit-time-departure" name="edit-time-departure" required autocomplete="off">
                        <label for="edit-time-departure">Time of Departure</label>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer d-flex justify-content-end p-3 border-top">
        <button type="button" class="btn btn-dark me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary" form="edit-transaction-actual-form">Save Changes</button>
    </div>
</div>
<script>
    // Add the mask functionality
    document.getElementById('to-reference').addEventListener('input', function(e) {
        let value = e.target.value;

        // Remove non-alphanumeric characters except the hyphen
        value = value.replace(/[^0-9a-zA-Z-]/g, '');

        // Insert hyphen after digits, if needed
        if (value.length > 4 && value.indexOf('-') === -1) {
            value = value.substring(0, 4) + '-' + value.substring(4);
        }

        // Convert to uppercase before updating the field value
        e.target.value = value.toUpperCase();
    });
</script>