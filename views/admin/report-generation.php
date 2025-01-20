<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 fw-bold mb-4">Report Generation</h1>
        <hr>
        <div>
            <div class="row mb-4 mt-4">
                <h4 class="fw-bold mb-5">Transaction</h4>
                <div class="col-2 text-center" id="haulers">
                    <a class="text-decoration-none" onclick="reportModal('tally-in')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-list-check fa-2xl"></i>
                        <p class="mt-3">Tally In</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="vehicles">
                    <a class="text-decoration-none" onclick="reportModal('daily-unloading')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-truck fa-2xl"></i>
                        <p class="mt-3">Daily Unloading</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="project">
                    <a class="text-decoration-none" onclick="reportModal('summary')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-table-list fa-2xl"></i>
                        <p class="mt-3">Summary</p>
                    </a>
                </div>
            </div>
            <hr>
            <div class="row mb-4 mt-4">
                <h4 class="fw-bold mb-5">Logs</h4>

                <div class="col-2 text-center" id="haulers">
                    <a class="text-decoration-none" onclick="reportModal('settings')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-gear fa-2xl"></i>
                        <p class="mt-3">Settings Logs</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="vehicles">
                    <a class="text-decoration-none" onclick="reportModal('vehicles-unloaded')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-truck fa-2xl"></i>
                        <p class="mt-3">Vehicles Unloaded</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="vehicles">
                    <a class="text-decoration-none" onclick="reportModal('sms-logs')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-message fa-2xl"></i>
                        <p class="mt-3">SMS Logs</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="vehicles">
                    <a class="text-decoration-none" onclick="reportModal('event-logs')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-list-ul fa-2xl"></i>
                        <p class="mt-3">Event Logs</p>
                    </a>
                </div>
            </div>
            <hr>
        </div>
    </div>
</div>
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <input type="hidden" name="reportType" id="reportType" value="">

                    <div class="form-floating mb-3">
                        <select name="branch" id="branch" class="form-select" required>
                            <option value="all">All</option>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM origin");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['origin_id'] . '" data-code="' . $row['origin_code'] . '">' . $row['origin_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="branch">Branch</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="status" id="status" class="form-select" required>
                            <option value="all">All</option>
                            <option value="departed">Departed</option>
                            <option value="arrived">Arrived</option>
                            <option value="queue">Queue</option>
                            <option value="standby">Standby</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="done">Done</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="diverted">Diverted</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="date" name="dateFrom" id="dateFrom" class="form-control" required>
                        <label for="dateFrom">Date From</label>
                        <div class="invalid-feedback">Please enter a valid date</div>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="date" name="dateTo" id="dateTo" class="form-control" required>
                        <label for="dateTo">Date To</label>
                        <div class="invalid-feedback">Please enter a valid date</div>
                    </div>
                    <div class="form-floating mb-3">
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
                        $stmt->bindParam(':id', $_SESSION['id'], PDO::PARAM_INT);
                        $stmt->execute();
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <input type="hidden" name="currentUser" id="currentUser" value="<?= $user['fname'] . ' ' . $user['lname']; ?>">
                        <select name="signature" id="signature" class="form-select" onchange="toggleUserSelect()">
                            <option value="no" selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                        <label for="signature">Change Signature</label>
                    </div>
                    <div class="form-floating mb-3" id="userSelect" style="display: none;">
                        <select name="user" id="user" class="form-select">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM users WHERE userlevel = 'admin' OR userlevel = 'traffic(main)'");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['user_id'] . '">' . $row['fname'] . ' ' . $row['lname'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="user">User</label>
                    </div>
                    <div class="form-floating">
                        <select name="reportFormat" id="reportFormat" class="form-select" required>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                        <label for="reportFormat">Format</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="reportForm">Generate Report</button>
            </div>
        </div>
    </div>
</div>

<?php
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/report.js"></script>
</body>

</html>