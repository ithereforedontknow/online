<?php
session_start();
require '../config/connection.php';

// Improved error handling and security
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

class queueManager
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
    public function getArrived(string $status): void
    {
        $sql = 'SELECT * FROM transaction
                INNER JOIN arrival ON transaction.transaction_id = arrival.transaction_id
                INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id
                INNER JOIN hauler ON transaction.hauler_id = hauler.hauler_id
                INNER JOIN origin ON transaction.origin_id = origin.origin_id
                INNER JOIN project ON transaction.project_id = project.project_id
                WHERE transaction.status = :status
                ORDER BY arrival.arrival_time DESC';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['status' => $status]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Transactions retrieved successfully', ['transactions' => $transactions]);
        } catch (PDOException $e) {
            error_log('Error retrieving transactions: ' . $e->getMessage());
            $this->sendResponse(false, 'Error retrieving transactions');
        }
    }
    public function getQueue(string $status): void
    {
        $sql = 'SELECT * FROM transaction
        INNER JOIN queue ON transaction.transaction_id = queue.transaction_id
        INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id
        WHERE transaction.status = :status';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['status' => $status]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Transactions retrieved successfully', ['transactions' => $transactions]);
        } catch (PDOException $e) {
            error_log('Error retrieving transactions: ' . $e->getMessage());
            $this->sendResponse(false, 'Error retrieving transactions');
        }
    }
    public function createQueue($data)
    {

        try {
            $stmt = $this->conn->prepare("SELECT * FROM transaction WHERE to_reference = :to_reference");
            $stmt->execute(['to_reference' => $data['to-reference']]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'TO Reference already exists');
            } else {
                $status = "arrived";
                // Insert the transaction
                $stmt = $this->conn->prepare("INSERT INTO transaction (to_reference, guia, hauler_id, vehicle_id, driver_id, helper_id, project_id, no_of_bales, kilos, origin_id, time_of_departure, status) VALUES (:to_reference, :guia, :hauler_id, :vehicle_id, :driver_id, :helper_id, :project_id, :no_of_bales, :kilos, :origin_id, :time_departure, :status)");
                $stmt->execute([
                    'to_reference' => $data['to-reference'],
                    'guia' => $data['guia'],
                    'hauler_id' => $data['hauler_id'],
                    'vehicle_id' => $data['vehicle_id'],
                    'driver_id' => $data['driver_id'],
                    'helper_id' => $data['helper_id'],
                    'project_id' => $data['project_id'],
                    'no_of_bales' => $data['no-of-bales'],
                    'kilos' => $data['kilos'],
                    'origin_id' => $data['origin'],
                    'time_departure' => $data['time_departure'],
                    'status' => $status
                ]);

                $transaction_id = $this->conn->lastInsertId();

                $arrival_time = $data['arrival-time'];
                $arrival_date = date('Y-m-d', strtotime($arrival_time));

                // Insert the arrival
                $stmt = $this->conn->prepare("INSERT INTO arrival (transaction_id, arrival_time, arrival_date) VALUES (:transaction_id, :arrival_time, :arrival_date)");
                $stmt->execute([
                    ':transaction_id' => $transaction_id,
                    ':arrival_time' => $arrival_time,
                    ':arrival_date' => $arrival_date
                ]);
                // Insert the transaction log
                $stmt = $this->conn->prepare("INSERT INTO transaction_log (transaction_id, details, created_by) VALUES (:transaction_id, :details, :created_by)");
                $stmt->execute([
                    ':transaction_id' => $transaction_id,
                    ':details' => 'Transaction added by ' . $data['created_by'],
                    ':created_by' => $data['created_by']
                ]);
                $this->sendResponse(true, 'Transaction added successfully');
            }
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error adding transaction');
        }
    }
}

// Main API Handler
try {
    if (!isset($conn) || $conn === null) {
        throw new Exception('Database connection not established');
    }

    $queueManager = new queueManager($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'list arrived':
                $status = $_POST['status'];
                $queueManager->getArrived($status);
                break;
            case 'list queue':
                $status = $_POST['status'];
                $queueManager->getQueue($status);
                break;
            case 'create':
                $queueManager->createQueue($_POST);
                break;
            default:
                $queueManager->sendResponse(false, 'Invalid action');
        }
    } else {
        $queueManager->sendResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    error_log('Unhandled error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
    exit;
}
