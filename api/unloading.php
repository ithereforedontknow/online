<?php
session_start();
require '../config/connection.php';

// Improved error handling and security
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

class unloadingManager
{
    public $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    public function sendResponse($success, $message, $data = null)
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
        echo json_encode($response);
        exit;
    }
    public function getUnloading()
    {
        try {
            $sql = "SELECT * FROM transaction INNER JOIN unloading ON transaction.transaction_id = unloading.transaction_id WHERE transaction.status = 'ongoing'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Transactions retrieved successfully', ['transactions' => $transactions]);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function setUnloadingTimeStart($transaction_id)
    {
        try {
            $sql = "UPDATE unloading SET unloading_time_start = NOW() WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['transaction_id' => $transaction_id]);
            $this->sendResponse(true, 'Unloading time start set successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function setUnloadingTimeEnd($transaction_id)
    {
        try {
            $sql = "UPDATE unloading SET unloading_time_end = NOW() WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['transaction_id' => $transaction_id]);
            $this->sendResponse(true, 'Unloading time end set successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function setTimeOfDeparture($transaction_id)
    {
        try {
            $sql = "UPDATE unloading SET time_of_departure = NOW() WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['transaction_id' => $transaction_id]);

            $this->sendResponse(true, 'Unloading time end set successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function updateUnloading($data)
    {
        try {
            $transaction_id = $data['transaction_id'];
            $time_of_entry = $data['time_of_entry'];
            $unloading_time_start = $data['unloading_time_start'];
            $unloading_time_end = $data['unloading_time_end'];
            $time_of_departure = $data['time_of_departure'];
            $sql = "UPDATE unloading SET time_of_entry = :time_of_entry, unloading_time_start = :unloading_time_start, unloading_time_end = :unloading_time_end, time_of_departure = :time_of_departure WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':time_of_entry' => $time_of_entry,
                ':unloading_time_start' => $unloading_time_start,
                ':unloading_time_end' => $unloading_time_end,
                ':time_of_departure' => $time_of_departure,
                ':transaction_id' => $transaction_id
            ]);
            $stmt = $this->conn->prepare("SELECT created_by FROM transaction WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
            ]);

            $created_by = $stmt->fetchColumn();
            $stmt = $this->conn->prepare("SELECT to_reference FROM transaction WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
            ]);

            $to_reference = $stmt->fetchColumn();

            $stmt = $this->conn->prepare("INSERT INTO transaction_log (transaction_id, details, created_by) VALUES (:transaction_id, :details, :created_by)");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
                ':details' => $to_reference . ' Unloading details updated by ' . $created_by,
                ':created_by' => $created_by
            ]);
            $this->sendResponse(true, 'Unloading updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function setDone($transaction_id)
    {
        try {
            $sql = "SELECT * FROM transaction INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id INNER JOIN driver ON transaction.driver_id = driver.driver_id INNER JOIN helper ON transaction.helper_id = helper.helper_id WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['transaction_id' => $transaction_id]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$transaction) {
                $this->sendResponse(false, 'Transaction not found');
                return;
            }
            $sql = "UPDATE vehicle SET status = '1' WHERE vehicle_id = :vehicle_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['vehicle_id' => $transaction['vehicle_id']]);

            $sql = "UPDATE driver SET status = '1' WHERE driver_id = :driver_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['driver_id' => $transaction['driver_id']]);

            $sql = "UPDATE helper SET status = '1' WHERE helper_id = :helper_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['helper_id' => $transaction['helper_id']]);

            $sql = "UPDATE transaction SET status = 'done' WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['transaction_id' => $transaction_id]);

            $stmt = $this->conn->prepare("SELECT created_by FROM transaction WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
            ]);

            $created_by = $stmt->fetchColumn();

            $stmt = $this->conn->prepare("SELECT to_reference FROM transaction WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
            ]);

            $to_reference = $stmt->fetchColumn();

            $stmt = $this->conn->prepare("INSERT INTO transaction_log (transaction_id, details, created_by) VALUES (:transaction_id, :details, :created_by)");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
                ':details' => $to_reference . ' Transaction marked as done by ' . $transaction['created_by'],
                ':created_by' => $transaction['created_by']
            ]);
            $this->sendResponse(true, 'Transaction marked as done');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
}


// Main API Handler
try {
    if (!isset($conn) || $conn === null) {
        throw new Exception('Database connection not established');
    }

    $unloadingManager = new unloadingManager($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'list unloading':
                $unloadingManager->getUnloading();
                break;
            case 'set unloading time start':
                $transaction_id = $_POST['transaction_id'] ?? '';
                $unloadingManager->setUnloadingTimeStart($transaction_id);
                break;
            case 'set unloading time end':
                $transaction_id = $_POST['transaction_id'] ?? '';
                $unloadingManager->setUnloadingTimeEnd($transaction_id);
                break;
            case 'set time of departure':
                $transaction_id = $_POST['transaction_id'] ?? '';
                $unloadingManager->setTimeOfDeparture($transaction_id);
                break;
            case 'update unloading';
                $transaction_id = $_POST['transaction_id'] ?? '';
                $unloadingManager->updateUnloading($_POST);
                break;
            case 'set done':
                $transaction_id = $_POST['transaction_id'] ?? '';
                $unloadingManager->setDone($transaction_id);
                break;
            default:
                $unloadingManager->sendResponse(false, 'Invalid action');
        }
    } else {
        $unloadingManager->sendResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    error_log('Unhandled error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
    exit;
}
