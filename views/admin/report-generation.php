<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 me-auto fw-bold mb-0">Report Generation</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Report Generation</li>
                            </ol>
                        </nav>
                    </div>

                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="date" name="dateFrom" id="dateFrom" class="form-control" required>
                    <label for="dateFrom">Date From</label>
                    <div class="invalid-feedback">Please enter a valid date</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="date" name="dateTo" id="dateTo" class="form-control" required>
                    <label for="dateTo">Date To</label>
                    <div class="invalid-feedback">Please enter a valid date</div>
                </div>
            </div>
        </div>

        <hr>

        <div>
            <div class="row mb-4 mt-4">
                <h4 class="fw-bold mb-5">Transaction</h4>
                <div class="col-2 text-center">
                    <a class="text-decoration-none" data-bs-target="#allReportsModal" data-bs-toggle="modal" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-clipboard-list fa-2xl"></i>
                        <p class="mt-3">Reports</p>
                    </a>
                </div>
                <div class="col-2 text-center">
                    <a class="text-decoration-none" onclick="reportModal('tally-in')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-list-check fa-2xl"></i>
                        <p class="mt-3">Tally In</p>
                    </a>
                </div>
                <div class="col-2 text-center">
                    <a class="text-decoration-none" onclick="reportModal('daily-unloading')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-truck fa-2xl"></i>
                        <p class="mt-3">Daily Unloading</p>
                    </a>
                </div>
                <div class="col-2 text-center">
                    <a class="text-decoration-none" onclick="reportModal('summary')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-table-list fa-2xl"></i>
                        <p class="mt-3">Summary</p>
                    </a>
                </div>

                <div class="col-2 text-center">
                    <a class="text-decoration-none" onclick="reportModal('demurrage')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-circle-exclamation fa-2xl"></i>
                        <p class="mt-3">Demurrage</p>
                    </a>
                </div>
                <div class="col-2 text-center">
                    <a class="text-decoration-none" onclick="reportModal('diverted')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-circle-exclamation fa-2xl"></i>
                        <p class="mt-3">Diverted</p>
                    </a>
                </div>
            </div>
            <hr>
            <div class="row mb-4 mt-4">
                <h4 class="fw-bold mb-5">Logs</h4>
                <div class="col-2 text-center">
                    <a class="text-decoration-none" onclick="logReports('settings')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-gear fa-2xl"></i>
                        <p class="mt-3">Settings Logs</p>
                    </a>
                </div>
                <div class="col-2 text-center">
                    <a class="text-decoration-none" onclick="logReports('sms')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-message fa-2xl"></i>
                        <p class="mt-3">SMS Logs</p>
                    </a>
                </div>
                <div class="col-2 text-center">
                    <a class="text-decoration-none" onclick="logReports('event')" style="color: #1b3667; cursor: pointer;">
                        <i class="fa-solid fa-list-ul fa-2xl"></i>
                        <p class="mt-3">Event Logs</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
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
                        <select name="signature" id="signature" class="form-select">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM users WHERE userlevel = 'admin' OR userlevel = 'traffic(main)'");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option>' . $row['fname'] . ' ' . $row['lname'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="signature">Signature</label>
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
<div class="modal fade" id="allReportsModal" tabindex="-1" aria-labelledby="allReportsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allReportsModalLabel">Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="allReportsForm">
                    <div class="form-floating mb-3">
                        <select name="all-reports-branch" id="all-reports-branch" class="form-select" required>
                            <option value="all">All</option>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM origin");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['origin_id'] . '" data-code="' . $row['origin_code'] . '">' . $row['origin_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="all-reports-branch">Branch</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="all-reports-status" id="all-reports-status" class="form-select" required>
                            <option value="all">All</option>
                            <option value="departed">Departed</option>
                            <option value="arrived">Arrived</option>
                            <option value="queue">Queue</option>
                            <option value="standby - sms sent">Standby</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="done">Finished</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="diverted">Diverted</option>
                        </select>
                        <label for="all-reports-status">Status</label>
                    </div>
                    <div class="form-floating mb-3">
                        <select name="all-reports-signature" id="all-reports-signature" class="form-select">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM users WHERE userlevel = 'admin' OR userlevel = 'traffic(main)'");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option>' . $row['fname'] . ' ' . $row['lname'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="all-reports-signature">Signature</label>
                    </div>
                    <div class="form-floating">
                        <select name="all-reports-reportFormat" id="all-reports-reportFormat" class="form-select" required>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                        <label for="all-reports-reportFormat">Format</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="allReportsForm">Generate Report</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="logsReportModal" tabindex="-1" aria-labelledby="logsReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logsReportModalLabel">Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="logsReportForm">
                    <input type="hidden" name="logReportType" id="logReportType" value="logs">
                    <div class="form-floating mb-3">
                        <select name="logReportSignature" id="logReportSignature" class="form-select">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM users WHERE userlevel = 'admin' OR userlevel = 'traffic(main)'");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option>' . $row['fname'] . ' ' . $row['lname'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="logReportSignature">Signature</label>
                    </div>
                    <div class="form-floating">
                        <select name="logReportFormat" id="logReportFormat" class="form-select" required>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                        <label for="logReportFormat">Format</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="logsReportForm">Generate Report</button>
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