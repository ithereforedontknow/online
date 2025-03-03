<nav class="navbar navbar-expand-lg fixed-top px-3 shadow">
    <button class="btn mx-2" id="sidebarToggle">
        <i class="fa-solid fa-bars fa-lg" style="color:#ffffff"></i>
    </button>
    <img src="../../assets/img/Untitled-1.png" style="width: 40px;">
    <a class="navbar-brand ms-3 fw-bold">Online Vehicle Management System</a>
    <div class="collapse navbar-collapse justify-content-end">
        <ul class="navbar-nav ml-auto">
            <!-- Notification Dropdown -->
            <li class="nav-item dropdow">
                <a class="nav-link" data-bs-toggle="offcanvas" role="button" href="#viewNotificationsOffcanvas" id="notificationDropdown">
                    <i class="fa-solid fa-bell fa-lg" style="color:#ffffff"></i>
                    <!-- Badge showing the number of notifications -->
                    <span class="badge bg-danger rounded-circle" id="notificationBadge">

                    </span>
                </a>
            </li>
            <div class="dropdown">
                <li class="nav-item dropdown bg">
                    <a href="#" class="nav-link " data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user fa-lg" style="color:#ffffff"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow bg-dark">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><button class="dropdown-item" onclick="logout_user()">Sign out</button></li>
                    </ul>
                </li>
            </div>
        </ul>
    </div>
</nav>
<div class="sidebar shadow" id="sidebar">
    <div class="d-flex flex-column flex-shrink-0 p-3" style="width: 280px; height: 100svh; ">
        <ul class="nav nav-pills flex-column mb-3">
            <li class="nav-item mb-2">
                <a href="index.php" class="nav-link text-white">
                    <i class="fa-solid fa-chart-line fa-lg me-2 "></i>
                    Dashboard
                </a>
            </li>
            <?php if ($_SESSION['userlevel'] == 'admin') { ?>
                <li class="nav-item mb-2">
                    <a href="users.php" class="nav-link text-white">
                        <i class="fa-solid fa-users fa-lg me-2"></i>
                        Users
                    </a>
                </li>
            <?php } ?>
            <?php if ($_SESSION['userlevel'] == 'admin' || $_SESSION['userlevel'] == 'traffic(main)') { ?>
                <li class="nav-item mb-2">
                    <a href="status.php" class="nav-link text-white">
                        <i class="fa-solid fa-bars-progress fa-lg me-2"></i>
                        In Progress
                    </a>
                </li>
            <?php } ?>
            <li class="nav-item mb-2">
                <a class="nav-link text-white" href="vehicle-transactions.php">
                    <i class="fa-solid fa-scroll fa-lg me-2"></i>
                    Vehicle Transactions
                </a>
            </li>
            <?php if ($_SESSION['userlevel'] != 'encoder') { ?>
                <li class="nav-item mb-2">
                    <a href="queue.php" class="nav-link text-white">
                        <i class="fa-solid fa-clock fa-lg me-2"></i>
                        Queue Management
                    </a>
                </li>
            <?php } ?>
            <?php if ($_SESSION['userlevel'] != 'encoder') { ?>
                <li class="nav-item mb-2">
                    <a href="unloading.php" class="nav-link text-white">
                        <i class="fa-solid fa-spinner fa-lg me-2"></i> Vehicle Unloading
                    </a>
                </li>
            <?php } ?>
            <?php if ($_SESSION['userlevel'] != 'encoder') { ?>
                <li class="nav-item mb-2">
                    <a href="finished-transactions.php" class="nav-link text-white">
                        <i class="fa-solid fa-receipt fa-lg me-2"></i> Finished Transactions
                    </a>
                </li>
            <?php } ?>
            <?php if ($_SESSION['userlevel'] != 'encoder') { ?>
                <li class="nav-item mb-2">
                    <a href="report-generation.php" class="nav-link text-white">
                        <i class="fa-solid fa-print fa-lg me-2"></i>
                        Report Generation
                    </a>
                </li>
            <?php } ?>
            <li class="nav-item mb-2">
                <a href="settings.php" class="nav-link text-white">
                    <i class="fa-solid fa-gear fa-lg me-2"></i> Settings
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="help.php" class="nav-link text-white">
                    <i class="fa-solid fa-question fa-lg me-2"></i> Help
                </a>
            </li>
        </ul>
    </div>
</div>
<div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<?php
include_once('../../includes/offcanvas/view-notifications-offcanvas.php');
?>