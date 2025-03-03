<?php
require_once '../../config/connection.php';
session_start();
if (!isset($_SESSION['id']) || $_SESSION['userlevel'] !== 'encoder') {
    header('Location: ../../index.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vehicle Management</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/datatables.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../../assets/img/Untitled-1.png" />

</head>

<body class="bg-light">

    <?php
    include_once('../../includes/nav/navbar.php');
    ?>