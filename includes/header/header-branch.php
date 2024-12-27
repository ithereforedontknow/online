<?php
require_once '../../config/connection.php';
session_start();
if (!isset($_SESSION['id']) || $_SESSION['userlevel'] !== 'traffic(branch)') {
    header('Location: ../index.php');
} // Assuming you have a database connection in $conn
$userId = $_SESSION['id']; // Example: get the user ID from session
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vehicle Transactions</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../../assets/img/Untitled-1.png" />
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg fixed-top px-3 shadow">
        <img src="../../assets/img/Untitled-1.png" style="width: 40px;">
        <a class="navbar-brand ms-3 fw-bold">Online Vehicle Management System</a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown ">
                    <a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-bell fa-lg" style="color:#ffffff"></i>
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
                <div class="dropdown">
                    <li class="nav-item dropdown bg">
                        <a href="#" class="nav-link " data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user fa-lg" style="color:#ffffff"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow bg-dark">
                            <li><a class="dropdown-item" href="index.php">Home</a></li>
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