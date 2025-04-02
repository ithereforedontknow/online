<?php
include_once('../../includes/header/header-main.php');
?>
<div class="content" id="content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 me-auto fw-bold mb-0">Finished Transactions</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Finished Transactions</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="departed-table">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover text-center small-font" id="finished-transactions-table">
                            <thead>
                                <tr>
                                    <th class="text-center" scope="col">To Reference</th>
                                    <th class="text-center" scope="col">Origin</th>
                                    <th class="text-center" scope="col">Demurrage</th>
                                    <th class="text-center" scope="col">Kilos</th>
                                    <th class="text-center" scope="col">Transfer Out Net Weight kg</th>
                                    <th class="text-center" scope="col">Scrap kg</th>
                                    <th class="text-center" scope="col">Remarks</th>
                                    <th class="text-center" scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody id="finished-transactions-list">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editFinishedTransactionModal" tabindex="-1" aria-labelledby="editTransactionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="editTransactionLabel">Edit <span class="fw-bold">Finished</span> transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="finished-transaction-form">
                        <!-- Transaction Form -->
                        <div class="row">
                            <div class="col">
                                <input type="hidden" id="finished-transaction-id" name="finished-transaction-id">
                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="finished-to-reference" name="finished-to-reference" required>
                                    <label for="finished-to-reference">TO Reference #</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="finished-guia" name="finished-guia" required>
                                    <label for="finished-guia">GUIA</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input list="finished-haulers" class="form-control" name="finished-hauler" id="finished-hauler" required autocomplete="off">
                                    <label for="finished-hauler">Hauler</label>
                                    <datalist id="finished-haulers">
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
                                    <input list="finished-plate-numbers" class="form-control" name="finished-plate-number" id="finished-plate-number" required autocomplete="off">
                                    <label for="finished-plate-number">Plate Number</label>
                                    <datalist id="finished-plate-numbers">
                                        <?php
                                        $stmt = $conn->prepare("SELECT * FROM vehicle WHERE status = '1'");
                                        $stmt->execute();
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . $row['plate_number'] . ' : ' . $row['truck_type'] . '" data-id="' . $row['vehicle_id'] . '"></option>';
                                        }
                                        ?>
                                    </datalist>
                                    <div class="invalid-feedback">Plate Number does not exist</div>
                                </div>
                                <div class="form-floating mb-4">
                                    <input list="finished-driver-names" class="form-control" name="finished-driver-name" id="finished-driver-name" required autocomplete="off">
                                    <label for="finished-driver-name">Driver Name</label>
                                    <datalist id="finished-driver-names">
                                        <?php
                                        $stmt = $conn->prepare("SELECT * FROM driver WHERE status = '1'");
                                        $stmt->execute();
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . $row['driver_fname'] . ' ' . $row['driver_lname'] . '" data-id="' . $row['driver_id'] . '"></option>';
                                        }
                                        ?>
                                    </datalist>
                                    <div class="invalid-feedback">Driver Name does not exist</div>
                                </div>
                                <div class="form-floating">
                                    <input list="finished-helper-names" class="form-control" name="finished-helper-name" id="finished-helper-name" required autocomplete="off">
                                    <label for="finished-helper-name">Helper Name</label>
                                    <datalist id="finished-helper-names">
                                        <?php
                                        $stmt = $conn->prepare("SELECT * FROM helper WHERE status = '1'");
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
                                    <select class="form-select" name="finished-project" id="finished-project" required>
                                        <?php
                                        $stmt = $conn->prepare("SELECT * FROM project");
                                        $stmt->execute();
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . $row['project_id'] . '">' . $row['project_name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="finished-project">Project</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="finished-no-of-bales" name="finished-no-of-bales" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    <label for="finished-no-of-bales">No of Bales</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="finished-kilos" name="finished-kilos" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    <label for="finished-kilos">Kilos</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <select name="finished-origin" id="finished-origin" class="form-select" required>
                                        <?php
                                        $stmt = $conn->prepare("SELECT * FROM origin");
                                        $stmt->execute();
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . $row['origin_id'] . '">' . $row['origin_name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="finished-origin">Origin</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="datetime-local" class="form-control" id="finished-time-departure" name="finished-time-departure" required autocomplete="off">
                                    <label for="finished-time-departure">Time of Departure</label>
                                </div>
                                <div class="form-floating">
                                    <input type="datetime-local" class="form-control" id="finished-arrival-time" name="finished-arrival-time" required autocomplete="off">
                                    <label for="finished-arrival-time">Arrival Time</label>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <!-- Queue Form -->
                        <div class="row">
                            <div class="col">
                                <div class="form-floating mb-4">
                                    <select class="form-select" id="finished-transfer-in-line" name="finished-transfer-in-line">
                                        <option value="Line 3">Line 3</option>
                                        <option value="Line 4">Line 4</option>
                                        <option value="Line 5">Line 5</option>
                                        <option value="Line 6">Line 6</option>
                                        <option value="Line 7">Line 7</option>
                                        <option value="GLAD WHSE">GLAD WHSE</option>
                                        <option value="WHSE 2-BAY 2">WHSE 2-BAY 2</option>
                                        <option value="WHSE 2-BAY 3">WHSE 2-BAY 3</option>
                                    </select>
                                    <label for="finished-transfer-in-line">Transfer in Line #</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <select class="form-select" id="finished-queue-ordinal" name="finished-queue-ordinal">
                                        <option value="1st">1st</option>
                                        <option value="2nd">2nd</option>
                                        <option value="3rd">3rd</option>
                                        <option value="3rd/1st">3rd/1st</option>
                                    </select>
                                    <label for="finished-queue-ordinal">Ordinal</label>
                                </div>
                                <div class="form-floating">
                                    <select class="form-select" id="finished-queue-shift" name="finished-queue-shift">
                                        <option value="day">Day</option>
                                        <option value="night">Night</option>
                                        <option value="day/night">Day/Night</option>
                                    </select>
                                    <label for="finished-queue-shift">Shift</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating mb-4">
                                    <select class="form-select" id="finished-queue-schedule" name="finished-queue-schedule">
                                        <option value="6am-2pm">6 am to 2 pm</option>
                                        <option value="2pm-6am">2 pm to 6 am</option>
                                        <option value="6am-2pm/2pm-6am">6 am to 2 pm/2 pm to 6 am</option>
                                    </select>
                                    <label for="finished-queue-schedule">Schedule</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="finished-queue-number" name="finished-queue-number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                    <label for="finished-queue-number">Vehicle Pass</label>
                                    <div class="invalid-feedback">Vehicle Pass already exists.</div>
                                </div>
                                <div class="form-floating">
                                    <select name="finished-queue-priority" class="form-select" id="finished-queue-priority">
                                        <option value="1">High</option>
                                        <option value="0">Low</option>
                                    </select>
                                    <label for="finished-queue-priority">Set Priority</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="row">
                            <div class="col">
                                <div class="form-floating mb-4">
                                    <input type="datetime-local" class="form-control" id="finished-time-entry">
                                    <label for="finished-time-entry" class="form-label">Time of Entry</label>
                                </div>
                                <div class="form-floating">
                                    <input type="datetime-local" class="form-control" id="finished-unloading-start">
                                    <label for="finished-unloading-start" class="form-label">Unloading Time Start</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating mb-4">
                                    <input type="datetime-local" class="form-control" id="finished-unloading-end">
                                    <label for="finished-unloading-end" class="form-label">Unloading Time End</label>
                                </div>
                                <div class="form-floating">
                                    <input type="datetime-local" class="form-control" id="finished-departure">
                                    <label for="finished-departure" class="form-label">Time of Departure</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="form-floating mb-4">
                            <input type="text" step="0.01" class="form-control" id="finished-transfer-out-kilos" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <label for="finished-transfer-out-kilos" class="form-label">Transfer Out Net Weight</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="text" step="0.01" class="form-control" id="finished-scrap" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <label for="finished-scrap" class="form-label">Scrap</label>
                        </div>
                        <div class="form-floating">
                            <textarea class="form-control" id="finished-remarks"></textarea>
                            <label for="finished-remarks" class="form-label">Remarks</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-end p-3 border-top">
                    <button type="button" class="btn btn-dark me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="finished-transaction-form">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/transaction.js"></script>
</body>

</html>