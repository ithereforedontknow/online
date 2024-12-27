<?php
include_once('../../includes/header/header-branch.php');
?>
<div class="content1" id="content">
    <div class="container p-5">
        <h1 class="display-5 mb-3 fw-bold">Transaction Form</h1>
        <form id="add-transaction">
            <div class="row">
                <div class="col">
                    <div class="form-floating mb-4">
                        <?php
                        // Fetch the branch from the current user's record
                        $stmt = $conn->prepare("SELECT `branch` FROM `users` WHERE `id` = :userId");
                        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                        $stmt->execute();
                        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

                        // Fetch the origin code based on the branch
                        $stmt = $conn->prepare("SELECT origin_code FROM origin WHERE origin_id = :branch");
                        $stmt->bindParam(':branch', $userRow['branch'], PDO::PARAM_INT);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $originCode = $row['origin_code'];
                        ?>
                        <input
                            type="text"
                            class="form-control"
                            id="to-reference"
                            name="to-reference"
                            required
                            maxlength="7"
                            oninput="updateToReference(this, '<?= $originCode ?>')">
                        <label for="to-reference" class="form-label">TO Reference #</label>
                    </div>

                    <script>
                        document.getElementById('to-reference').addEventListener('blur', function(event) {
                            const originCode = '<?= $originCode ?>';
                            let value = this.value.toUpperCase();
                            if (!value.endsWith(`-${originCode}`)) {
                                value = value.replace(`-${originCode}`, '');
                                this.value = value + `-${originCode}`;
                            }
                        });
                    </script>


                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="guia" name="guia" required oninput="this.value = this.value.toUpperCase();">
                        <label for=" guia" class="form-label">GUIA</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input list="haulers" class="form-control" name="hauler" id="hauler" required autocomplete="off">
                        <label for="haulers">Hauler</label>
                        <datalist id="haulers">
                            <?php
                            // Get the branch from the current user's record
                            $stmt = $conn->prepare("SELECT `branch` FROM `users` WHERE `id` = :userId");
                            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                            $stmt->execute();
                            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

                            $stmt = $conn->prepare("SELECT * FROM `hauler` WHERE `branch` = :branch");
                            $stmt->bindParam(':branch', $userRow['branch'], PDO::PARAM_STR);
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
                        <label for="plate-numbers">Plate Number</label>
                        <datalist id="plate-numbers">
                            <?php
                            // Get the branch from the current user's record
                            $stmt = $conn->prepare("SELECT `branch` FROM `users` WHERE `id` = :userId");
                            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                            $stmt->execute();
                            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

                            $stmt = $conn->prepare("SELECT * FROM vehicle INNER JOIN hauler on vehicle.hauler_id = hauler.hauler_id WHERE hauler.branch = :branch AND vehicle.status = '1'");
                            $stmt->bindParam(':branch', $userRow['branch'], PDO::PARAM_INT);
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
                        <label for="driver-names">Driver Name</label>
                        <datalist id="driver-names">
                            <?php
                            // Get the branch from the current user's record
                            $stmt = $conn->prepare("SELECT `branch` FROM `users` WHERE `id` = :userId");
                            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                            $stmt->execute();
                            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

                            $stmt = $conn->prepare("SELECT * FROM `driver` where `branch` = :branch and `status` = '1'");
                            $stmt->bindParam(':branch', $userRow['branch'], PDO::PARAM_INT);

                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['driver_fname'] . ' ' . $row['driver_lname'] . '" data-id="' . $row['driver_id'] . '"></option>';
                            }
                            ?>
                        </datalist>
                        <div class="invalid-feedback">Driver currently in transaction</div>
                    </div>
                    <div class="form-floating mb-4">
                        <input list="helper-names" class="form-control" name="helper-name" id="helper-name" required autocomplete="off">
                        <label for="helper-names">Helper Name</label>
                        <datalist id="helper-names">

                            <?php
                            // Get the branch from the current user's record
                            $stmt = $conn->prepare("SELECT `branch` FROM `users` WHERE `id` = :userId");
                            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                            $stmt->execute();
                            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

                            $stmt = $conn->prepare("SELECT * FROM `helper` where `branch` = :branch and `status` = '1'");
                            $stmt->bindParam(':branch', $userRow['branch'], PDO::PARAM_INT);
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['helper_fname'] . '  ' . $row['helper_lname'] . '" data-id="' . $row['helper_id'] . '"></option>';
                            }
                            ?>
                        </datalist>
                        <div class="invalid-feedback">Helper currently in transaction</div>
                    </div>
                </div>
                <div class="col">
                    <div class="form-floating mb-4">
                        <select class="form-select" name="project" id="project" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM `project`");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['project_id'] . '">' . $row['project_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="project">Project</label>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="no-of-bales" name="no-of-bales" required>
                        <label for="no-of-bales" class="form-label">No of Bales (kg)</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="kilos" name="kilos" required>
                        <label for="kilos" class="form-label">Kilos</label>
                    </div>
                    <div class="form-floating mb-4">
                        <select name="origin" id="origin_id" class="form-select" required>
                            <?php
                            // Get the branch from the current user's record
                            $stmt = $conn->prepare("SELECT `branch` FROM `users` WHERE `id` = :userId");
                            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                            $stmt->execute();
                            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

                            // Use the user's branch to select the corresponding origin
                            $stmt = $conn->prepare("SELECT * FROM `origin` WHERE `origin_id` = :branch");
                            $stmt->bindParam(':branch', $userRow['branch'], PDO::PARAM_INT);
                            $stmt->execute();

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['origin_id'] . '">' . $row['origin_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="origin" class="form-label">Origin</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="datetime-local" class="form-control" name="time-departure" id="time-departure" required>
                        <label for="time-departure" class="form-label">Time Of Departure</label>
                        <div class="invalid-feedback">Time of Departure cannot be in the past.</div>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary float-end">Save</button>
                    <button type="button" class="btn btn-secondary float-end me-2" id="clear">Clear</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="help-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-1" id="exampleModalLabel">Help</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h2>Adding Transactions:</h2>
                <ol>
                    <li>Enter the transaction details, including vehicle information, date, and any relevant notes.</li>
                    <li> Click <button class="btn btn-primary">Save</button></li>
                </ol>
                <h2>Editing Profile:</h2>
                <ol>
                    <li>
                        Enter your <strong>Username</strong>. This is how others will identify you on the platform.
                    </li>
                    <li>
                        Fill in your <strong>First Name</strong>, <strong>Middle Name</strong>, and <strong>Last Name</strong>. Make sure all names are spelled correctly.
                    </li>
                    <li>
                        Enter a <strong>New Password</strong> that meets security requirements (at least 8 characters, including upper/lowercase and numbers).
                    </li>
                    <li>
                        Confirm your new password by re-entering it in the <strong>Confirm New Password</strong> field.
                    </li>
                    <li>
                        Click the <button class="btn btn-primary">Change Password</button> button to save your changes. If any validation fails, appropriate messages will guide you to correct the entries.
                    </li>
                </ol>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
include_once('../../includes/offcanvas/view-notifications-offcanvas.php');
include_once('../../includes/footer/footer-branch.php');
?>