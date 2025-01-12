<nav class="navbar navbar-expand-lg fixed-top px-3 shadow">
    <button class="btn mx-2" id="sidebarToggle">
        <i class="fa-solid fa-bars fa-lg" style="color:#ffffff"></i>
    </button>
    <img src="../../assets/img/Untitled-1.png" style="width: 40px;">
    <a class="navbar-brand ms-3 fw-bold">Online Vehicle Management System</a>
    <div class="collapse navbar-collapse justify-content-end">
        <ul class="navbar-nav ml-auto">
            <!-- Notification Dropdown -->
            <li class="nav-item dropdown ">
                <a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bell fa-lg" style="color:#ffffff"></i>
                    <!-- Badge showing the number of notifications -->
                    <span class="badge bg-danger rounded-circle">
                        <?php
                        $currentTime = date('Y-m-d H:i:s');
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM transaction WHERE created_at >= :currentTime AND status = 'departed'");
                        $stmt->bindParam(':currentTime', $currentTime);
                        $stmt->execute();
                        $transactionCount = $stmt->fetchColumn();
                        echo $transactionCount;
                        ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end bg-dark dropdown-menu-dark shadow" aria-labelledby="notificationDropdown">
                    <?php
                    $currentTime = date('Y-m-d H:i:s');
                    $stmt = $conn->prepare("SELECT transaction.to_reference, transaction.created_at, origin.origin_name 
                    FROM transaction 
                    RIGHT JOIN origin ON transaction.origin_id = origin.origin_id 
                    WHERE transaction.created_at >= :currentTime AND status = 'departed' 
                    ORDER BY transaction_id DESC");
                    $stmt->bindParam(':currentTime', $currentTime);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        while ($transaction = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <?= "{$transaction['to_reference']} has departed from {$transaction['origin_name']}" ?>
                                    <div>
                                        <?= date('F j, Y, g:i a', strtotime($transaction['created_at'])) ?>
                                    </div>
                                </a>
                            </li>

                        <?php
                        }
                    } else {
                        ?>
                        <li><a class="dropdown-item" href="#">No Notifications</a></li>
                    <?php
                    }
                    ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" data-bs-toggle="offcanvas" role="button" href="#viewNotificationsOffcanvas">View all notifications</a></li>
                </ul>
            </li>

            <!-- User Information -->
            <!-- <a class="navbar-brand">You are logged in as <?php echo $_SESSION['userlevel']; ?></a> -->
            <div class="dropdown">
                <li class="nav-item dropdown bg">
                    <a href="#" class="nav-link " data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user fa-lg" style="color:#ffffff"></i>
                        <!-- <img src="../../assets/universal_corporation_logo.jpg" class="img-circle" alt="" width="32"> -->
                        <!-- <span><?php echo $_SESSION['username']; ?></span> -->
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
            <li class="nav-item mb-2">
                <a href="users.php" class="nav-link text-white">
                    <i class="fa-solid fa-users fa-lg me-2"></i>
                    Users
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="status.php" class="nav-link text-white">
                    <i class="fa-solid fa-bars-progress fa-lg me-2"></i>
                    Status
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-white" href="vehicle-transactions.php">
                    <i class="fa-solid fa-scroll fa-lg me-2"></i>
                    Vehicle Transactions
                </a>

            </li>
            <li class="nav-item mb-2">
                <a href="queue.php" class="nav-link text-white">
                    <i class="fa-solid fa-clock fa-lg me-2"></i>
                    Queue Management
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="unloading.php" class="nav-link text-white">
                    <i class="fa-solid fa-spinner fa-lg me-2"></i> Vehicle Unloading
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="finished-transactions.php" class="nav-link text-white">
                    <i class="fa-solid fa-receipt fa-lg me-2"></i> Finished Transactions
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="report-generation.php" class="nav-link text-white">
                    <i class="fa-solid fa-print fa-lg me-2"></i>
                    Report Generation
                </a>
            </li>
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
<?php
include_once('../../includes/offcanvas/view-notifications-offcanvas.php');
?>