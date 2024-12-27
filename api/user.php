<?php
session_start();
require_once '../config/connection.php';

// Improved error handling and security
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

class UserManager
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    public function sendResponse($success, $message, $users = null)
    {
        $response = [
            'success' => $success,
            'message' => $message
        ];

        if ($users !== null) {
            $response['users'] = $users;
        }

        echo json_encode($response);
        exit;
    }
    public function createUser($fname, $lname, $mname, $email, $username, $password, $userlevel, $branch)
    {
        try {
            // Hash the password
            $status = '1';
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Prepare statement to prevent SQL injection
            $stmt = $this->conn->prepare("INSERT INTO users (fname, lname, mname, email, username, password, userlevel, status, branch) VALUES (:fname, :lname, :mname, :email, :username, :hashed_password, :userlevel, :status, :branch)");
            $stmt->execute([
                'fname' => $fname,
                'lname' => $lname,
                'mname' => $mname,
                'email' => $email,
                'username' => $username,
                'hashed_password' => $hashedPassword,
                'userlevel' => $userlevel,
                'status' => $status,
                'branch' => $branch
            ]);

            $this->sendResponse(true, 'User created successfully');
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error creating user');
        }
    }
    public function updateUser($id, $fname, $lname, $mname, $email, $password, $username, $userlevel, $branch)
    {
        try {
            // Prepare statement to prevent SQL injection
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users SET fname = :fname, lname = :lname, mname = :mname, email = :email, username = :username, password = :hashed_password, userlevel = :userlevel, branch = :branch WHERE id = :id");
            $stmt->execute([
                'fname' => $fname,
                'lname' => $lname,
                'mname' => $mname,
                'email' => $email,
                'username' => $username,
                'hashed_password' => $hashedPassword,
                'userlevel' => $userlevel,
                'branch' => $branch,
                'id' => $id
            ]);


            $this->sendResponse(true, 'User updated successfully');
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error updating user');
        }
    }
    public function activateUser($id)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET status = '1' WHERE id = :id");
            $stmt->execute([
                'id' => $id
            ]);
            $this->sendResponse(true, 'User activated successfully');
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error activating user');
        }
    }
    public function deactivateUser($id)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET status = '0' WHERE id = :id");
            $stmt->execute([
                'id' => $id
            ]);
            $this->sendResponse(true, 'User deactivated successfully');
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error deactivating user');
        }
    }
    public function getUsers()
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, fname, lname, mname, email, username, userlevel, status, branch, created_at FROM users");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendResponse(true, 'Users retrieved successfully', $users);
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error retrieving users');
        }
    }
}

// Handle request
try {
    // Ensure database connection is established
    if (!isset($conn) || $conn === null) {
        throw new Exception('Database connection not established');
    }

    $userManager = new UserManager($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Use strict comparison and correct isset check
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    if (isset($_POST['fname'], $_POST['lname'], $_POST['mname'], $_POST['email'], $_POST['username'], $_POST['password'], $_POST['userlevel'], $_POST['branch'])) {
                        $userManager->createUser($_POST['fname'], $_POST['lname'], $_POST['mname'], $_POST['email'], $_POST['username'], $_POST['password'], $_POST['userlevel'], $_POST['branch']);
                    } else {
                        $userManager->sendResponse(false, 'Missing required fields for creating user');
                    }
                    break;
                case 'list':
                    $userManager->getUsers();
                    break;
                case 'update':
                    if (isset($_POST['id'], $_POST['fname'], $_POST['lname'], $_POST['mname'], $_POST['email'], $_POST['password'], $_POST['username'], $_POST['userlevel'], $_POST['branch'])) {
                        $userManager->updateUser($_POST['id'], $_POST['fname'], $_POST['lname'], $_POST['mname'], $_POST['email'], $_POST['password'], $_POST['username'], $_POST['userlevel'], $_POST['branch']);
                    } else {
                        $userManager->sendResponse(false, 'Missing required fields for updating user');
                    }
                    break;
                case 'deactivate':
                    if (isset($_POST['id'])) {
                        $userManager->deactivateUser($_POST['id']);
                    } else {
                        $userManager->sendResponse(false, 'Missing required fields for deactivating user');
                    }
                    break;
                case 'activate':
                    if (isset($_POST['id'])) {
                        $userManager->activateUser($_POST['id']);
                    } else {
                        $userManager->sendResponse(false, 'Missing required fields for activating user');
                    }
                    break;
                default:
                    $userManager->sendResponse(false, 'Invalid action');
                    break;
            }
        } else {
            $userManager->sendResponse(false, 'Missing action field');
        }
    } else {
        $userManager->sendResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    // Catch any unexpected errors
    error_log('Unhandled error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
    exit;
}
