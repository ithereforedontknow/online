<?php
session_start();
require '../config/connection.php';

// Improved error handling and security
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

class TransactionManager
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
    public function addBranchTransaction($data)
    {
        try {
            $this->conn->beginTransaction();

            // Validate TO Reference
            $stmt = $this->conn->prepare("
                SELECT 1 FROM transaction 
                WHERE to_reference = :to_reference
            ");
            $stmt->execute(['to_reference' => $data['to-reference']]);
            if ($stmt->rowCount() > 0) {
                throw new Exception('TO Reference already exists');
            }

            // Validate time of departure
            if (strtotime($data['time-departure']) < strtotime(date('Y-m-d H:i:s'))) {
                throw new Exception('Time of Departure cannot be in the past');
            }

            // Validate driver availability
            $stmt = $this->conn->prepare("
                SELECT 1 FROM transaction 
                WHERE driver_id = :driver_id 
                AND status = 'departed'
            ");
            $stmt->execute(['driver_id' => $data['driver-id']]);
            if ($stmt->rowCount() > 0) {
                throw new Exception('Driver is currently assigned to another transaction');
            }

            // Validate helper availability
            $stmt = $this->conn->prepare("
                SELECT 1 FROM transaction 
                WHERE helper_id = :helper_id 
                AND status = 'departed'
            ");
            $stmt->execute(['helper_id' => $data['helper-id']]);
            if ($stmt->rowCount() > 0) {
                throw new Exception('Helper is currently assigned to another transaction');
            }

            // Insert transaction
            $stmt = $this->conn->prepare("
                INSERT INTO transaction (
                    to_reference, guia, hauler_id, vehicle_id, 
                    driver_id, helper_id, project_id, no_of_bales, 
                    kilos, origin_id, time_of_departure, status,
                    created_at
                ) VALUES (
                    :to_reference, :guia, :hauler_id, :vehicle_id,
                    :driver_id, :helper_id, :project_id, :no_of_bales,
                    :kilos, :origin_id, :time_of_departure, 'departed',
                    NOW()
                )
            ");

            $stmt->execute([
                'to_reference' => $data['to-reference'],
                'guia' => $data['guia'],
                'hauler_id' => $data['hauler-id'],
                'vehicle_id' => $data['vehicle-id'],
                'driver_id' => $data['driver-id'],
                'helper_id' => $data['helper-id'],
                'project_id' => $data['project-id'],
                'no_of_bales' => $data['no-of-bales'],
                'kilos' => $data['kilos'],
                'origin_id' => $data['origin-id'],
                'time_of_departure' => $data['time-departure']
            ]);

            $this->conn->commit();
            $this->sendResponse(true, 'Transaction added successfully');
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('Transaction error: ' . $e->getMessage());
            $this->sendResponse(false, $e->getMessage());
        }
    }

    public function getTransactions(string $status): void
    {
        $sql = 'SELECT * FROM transaction
                INNER JOIN hauler ON transaction.hauler_id = hauler.hauler_id
                INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id
                INNER JOIN project ON transaction.project_id = project.project_id
                INNER JOIN origin ON transaction.origin_id = origin.origin_id
                WHERE transaction.status = :status
                ORDER BY transaction_id DESC';
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
    public function createTransaction($data)
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
    public function cancelTransaction($id)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE transaction SET status = 'cancelled' WHERE transaction_id = :id");
            $stmt->execute([
                'id' => $id
            ]);
            $this->sendResponse(true, 'Transaction cancelled successfully');
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error cancelling transaction');
        }
    }
    public function restoreTransaction($id)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE transaction SET status = 'departed' WHERE transaction_id = :id");
            $stmt->execute([
                'id' => $id
            ]);
            $this->sendResponse(true, 'Transaction restored successfully');
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error restoring transaction');
        }
    }
    public function addToArrived($transaction_id, $arrival_time, $arrival_date)
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO arrival (transaction_id, arrival_time, arrival_date) VALUES (:transaction_id, :arrival_time, :arrival_date)");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
                ':arrival_time' => $arrival_time,
                ':arrival_date' => $arrival_date
            ]);
            $stmt = $this->conn->prepare("UPDATE transaction SET status = 'arrived' WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id
            ]);
            $this->sendResponse(true, 'Transaction added to arrived successfully');
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error adding transaction to arrived');
        }
    }
}

// Main API Handler
try {
    if (!isset($conn) || $conn === null) {
        throw new Exception('Database connection not established');
    }

    $transactionManager = new TransactionManager($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'branch add transaction':
                $transactionManager->addBranchTransaction($_POST);
                break;
            case 'list':
                $status = $_POST['status'] ?? 'departed';
                $transactionManager->getTransactions($status);
                break;
            case 'create':
                $transactionManager->createTransaction($_POST);
                break;
            case 'cancel':
                $id = $_POST['id'] ?? null;
                if ($id) {
                    $transactionManager->cancelTransaction($id);
                } else {
                    $transactionManager->sendResponse(false, 'Missing transaction ID');
                }
                break;
            case 'restore':
                $id = $_POST['id'] ?? null;
                if ($id) {
                    $transactionManager->restoreTransaction($id);
                } else {
                    $transactionManager->sendResponse(false, 'Missing transaction ID');
                }
                break;
            case 'add to arrived':
                $transaction_id = $_POST['transaction_id'] ?? null;
                $arrival_time = $_POST['arrival_time'] ?? null;
                $arrival_date = date('Y-m-d', strtotime($_POST['arrival_time'] ?? null));
                if ($transaction_id && $arrival_time) {
                    $transactionManager->addToArrived($transaction_id, $arrival_time, $arrival_date);
                } else {
                    $transactionManager->sendResponse(false, 'Missing transaction ID or arrival time');
                }
                break;

            default:
                $transactionManager->sendResponse(false, 'Invalid action');
        }
    } else {
        $transactionManager->sendResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    error_log('Unhandled error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
    exit;
}
