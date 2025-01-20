<?php
session_start();
require_once '../config/connection.php';

// Improved error handling and security
header('Content-Type: application/json');

function sendResponse($success, $message, $redirect = null)
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'redirect' => $redirect
    ]);
    exit;
}

function login()
{

    // Validate input
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        sendResponse(false, 'Missing username or password');
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate username
    if (empty($username)) {
        sendResponse(false, 'Username cannot be empty');
    }

    try {

        // Prepare statement to prevent SQL injection
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Check if user exists
        if (!$user) {
            sendResponse(false, 'Invalid username or password');
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            sendResponse(false, 'Invalid username or password');
        }

        // Check account status
        if ($user['status'] != 1) {
            sendResponse(false, 'Account is deactivated');
        }

        // Set session variables
        $_SESSION['username'] = $username;
        $_SESSION['id'] = $user['id'];
        $_SESSION['userlevel'] = $user['userlevel'];


        // Determine redirect based on user level
        $redirects = [
            'admin' => './views/admin/index.php',
            'traffic(main)' => './views/traffic - main/index.php',
            'traffic(branch)' => './views/traffic - branch/index.php',
            'encoder' => './views/encoder/index.php'
        ];

        // Send response with redirect
        $redirect = $redirects[$user['userlevel']] ?? null;
        sendResponse(true, $user['userlevel'], $redirect);
    } catch (PDOException $e) {
        // Log error securely
        error_log('Login error: ' . $e->getMessage());
        sendResponse(false, 'An error occurred. Please try again.');
    }
}

function logout()
{
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    sendResponse(true, 'Logged out successfully');
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        login();
    } elseif (isset($_POST['logout'])) {
        logout();
    } else {
        sendResponse(false, 'Invalid request');
    }
} else {
    sendResponse(false, 'Method not allowed');
}
