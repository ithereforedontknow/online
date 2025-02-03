<?php
require_once '../../config/connection.php';
session_start();
if (!isset($_SESSION['id']) || $_SESSION['userlevel'] !== 'traffic(branch)') {
    header('Location: ../index.php');
} // Assuming you have a database connection in $conn
$userId = $_SESSION['id']; // Example: get the user ID from session
$stmt = $conn->prepare("SELECT * FROM users INNER JOIN origin ON users.branch = origin.origin_id WHERE id = :id");
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
                    <a class="nav-link" href="#viewNotificationsOffcanvas" role="button" data-bs-toggle="offcanvas" aria-expanded="false">
                        <i class="fa-solid fa-bell fa-lg" style="color:#ffffff"></i>
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