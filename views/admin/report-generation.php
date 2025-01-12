<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 fw-bold mb-4">Report Generation</h1>
        <hr>
        <div>
            <div class="row mb-4 mt-4">
                <h4 class="fw-bold mb-5">Transaction (Excel)</h4>
                <div class="col-2 text-center" id="haulers">
                    <a class="text-decoration-none" href="report-tally.php" style="color: #1b3667">
                        <i class="fa-solid fa-list-check fa-2xl"></i>
                        <p class="mt-3">Tally In</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="vehicles">
                    <a class="text-decoration-none" href="report-unloading.php" style="color: #1b3667">
                        <i class="fa-solid fa-truck fa-2xl"></i>
                        <p class="mt-3">Daily Unloading</p>
                    </a>
                </div>
                <!-- <div class="col-2 text-center" id="drivers">
                        <a class="text-decoration-none" href="report-entry.php" style="color: #1b3667">
                            <i class="fa-solid fa-road-barrier fa-2xl"></i>
                            <p class="mt-3">Order of Entry</p>
                        </a>
                    </div> -->
                <div class="col-2 text-center" id="project">
                    <a class="text-decoration-none" href="report-summary.php" style="color: #1b3667">
                        <i class="fa-solid fa-table-list fa-2xl"></i>
                        <p class="mt-3">Summary</p>
                    </a>
                </div>
            </div>
            <hr>
            <div class="row mb-4 mt-4">
                <h4 class="fw-bold mb-5">Transaction (PDF)</h4>
                <div class="col-2 text-center" id="haulers">
                    <a class="text-decoration-none" href="report-diverted.php" style="color: #1b3667">
                        <i class="fa-solid fa-route fa-2xl"></i>
                        <p class="mt-3">Diverted Vehicles</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="vehicles">
                    <a class="text-decoration-none" href="report-unloaded.php" style="color: #1b3667">
                        <i class="fa-solid fa-truck fa-2xl"></i>
                        <p class="mt-3">Vehicles Unloaded</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="vehicles">
                    <a class="text-decoration-none" href="report-sms.php" style="color: #1b3667">
                        <i class="fa-solid fa-message fa-2xl"></i>
                        <p class="mt-3">SMS Logs</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="vehicles">
                    <a class="text-decoration-none" href="report-transaction.php" style="color: #1b3667">
                        <i class="fa-solid fa-list-ul fa-2xl"></i>
                        <p class="mt-3">Transaction Logs</p>
                    </a>
                </div>
            </div>
            <hr>
        </div>
    </div>
</div>

<?php
include_once('../../includes/footer/footer-admin.php');
?>
</body>

</html>