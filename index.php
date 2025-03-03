<?php
require_once 'config/connection.php';
session_start();

if (isset($_SESSION['id']) && $_SESSION['userlevel'] === 'admin') {
    header('Location: views/admin/index.php');
    exit;
} elseif (isset($_SESSION['id']) && $_SESSION['userlevel'] === 'traffic(branch)') {
    header('Location: views/traffic - branch/index.php');
    exit;
} elseif (isset($_SESSION['id']) && $_SESSION['userlevel'] === 'traffic(main)') {
    header('Location: views/traffic - main/index.php');
    exit;
} elseif (isset($_SESSION['id']) && $_SESSION['userlevel'] === 'encoder') {
    header('Location: views/encoder/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="icon" type="image/x-icon" href="assets/img/Untitled-1.png" />
    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-light">
    <div class="text-center">
        <img class="" src="assets/img/ULPI_BLUE (1).png" alt="" width="400" />
    </div>
    <main class="form-signin w-100 m-auto">
        <div class="container ">
            <div class="text-center">
                <form id="login_form">
                    <div class="form-floating mb-2 mt-2">
                        <input type="text" class="form-control shadow-sm" id="username" name="username" autocomplete="off" placeholder="JohnDoe" required />
                        <label for="username">Username</label>
                    </div>
                    <div class="form-floating mb-2">
                        <input type="password" class="form-control shadow-sm" id="password" name="password" placeholder="Password" required />
                        <label for="password">Password</label>
                    </div>
                </form>
                <button class="btn btn-primary w-100 py-3" type="submit" form="login_form">
                    Login
                </button>
                <a href="queue.php" class="btn btn-primary w-100 py-3 mt-2">View Queue</a>
                <div class="mt-3">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" class="text-decoration-none">Forgot Password?</a>
                </div>
            </div>
        </div>
        <p class="mt-5 mb-3 text-muted text-center">&copy; <?= date('Y'); ?></p>
    </main>
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="forgot-password">
                        <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="forgot-email" name="forgot-email" placeholder="JohnDoe" required />
                            <label for="forgot-email">Email</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" form="forgot-password">Send</button>
                </div>
            </div>
        </div>
    </div>
    <script src="public/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/jquery.min.js"></script>
    <script src="public/js/sweetalert2.all.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <script src="assets/js/index.js"></script>
</body>

</html>