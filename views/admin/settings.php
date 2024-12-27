<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 fw-bold mb-4">Settings</h1>
        <hr>
        <div>
            <div class="row mb-4 mt-4">
                <h4 class="fw-bold mb-5">Transaction Settings</h4>
                <div class="col-2 text-center" id="haulers">
                    <a class="text-decoration-none" href="settings-hauler.php" style="color: #1b3667">
                        <i class="fa-solid fa-warehouse fa-2xl"></i>
                        <p class="mt-3">Haulers</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="vehicles">
                    <a class="text-decoration-none" href="settings-vehicle.php" style="color: #1b3667">
                        <i class="fa-solid fa-truck fa-2xl"></i>
                        <p class="mt-3">Vehicles</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="drivers">
                    <a class="text-decoration-none" href="settings-driver.php" style="color: #1b3667">
                        <i class="fa-regular fa-id-card fa-2xl"></i>
                        <p class="mt-3">Drivers & Helpers</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="project">
                    <a class="text-decoration-none" href="settings-project.php" style="color: #1b3667">
                        <i class="fa-solid fa-sheet-plastic fa-2xl"></i>
                        <p class="mt-3">Project Description</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="origin">
                    <a class="text-decoration-none" href="settings-origin.php" style="color: #1b3667">
                        <i class="fa-solid fa-location-dot fa-2xl"></i>
                        <p class="mt-3">Origin</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="demurrage">
                    <a class="text-decoration-none" href="settings-demurrage.php" style="color: #1b3667">
                        <i class="fa-solid fa-dollar-sign fa-2xl"></i>
                        <p class="mt-3">Demurrage</p>
                    </a>
                </div>
            </div>
            <hr>
            <div class="row mb-4 mt-4">
                <h4 class="fw-bold mb-5">System</h4>
                <div class="col-2 text-center">
                    <a class="text-decoration-none" href="settings-backup.php" style="color: #1b3667">
                        <i class="fa-solid fa-database fa-2xl"></i>
                        <p class="mt-3">Backup & Restore</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>