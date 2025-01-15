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
    public function addArrived($data)
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
    public function addToQueue($transaction_id, $transfer_in_line, $ordinal, $shift, $schedule, $queue_number, $priority)
    {
        try {
            $currentDate = date('Y-m-d');
            $existingQueueNumberQuery = "SELECT * 
                                FROM queue  
                                WHERE queue_number = :queue_number 
                                AND created_at > :current_date";
            $stmt = $this->conn->prepare($existingQueueNumberQuery);
            $stmt->execute([
                ':queue_number' => $queue_number,
                ':current_date' => $currentDate
            ]);

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) > 0) {
                $this->sendResponse(false, 'Queue number is already in use');
                exit;
            }

            $stmt = $this->conn->prepare("INSERT INTO queue (transaction_id, transfer_in_line, ordinal, shift, schedule, queue_number, priority) VALUES (:transaction_id, :transfer_in_line, :ordinal, :shift, :schedule, :queue_number, :priority)");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
                ':transfer_in_line' => $transfer_in_line,
                ':ordinal' => $ordinal,
                ':shift' => $shift,
                ':schedule' => $schedule,
                ':queue_number' => $queue_number,
                ':priority' => $priority
            ]);
            $stmt = $this->conn->prepare("UPDATE transaction SET status = 'queue' WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id
            ]);
            $this->sendResponse(true, 'Transaction added to queue successfully');
        } catch (PDOException $e) {
            error_log('Error adding transaction to queue: ' . $e->getMessage());
            $this->sendResponse(false, 'Error adding transaction to queue');
        }
    }
    public function updateQueue($transaction_id, $transfer_in_line, $ordinal, $shift, $schedule, $queue_number, $priority)
    {
        try {

            $currentDate = date('Y-m-d');
            $query = "SELECT * 
                      FROM queue 
                      WHERE queue_number = :queue_number
                      AND created_at > :current_date 
                      AND transaction_id != :transaction_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':queue_number' => $queue_number,
                ':current_date' => $currentDate,
                ':transaction_id' => $transaction_id
            ]);

            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Queue number is already in use');
                return;
            }
            $stmt = $this->conn->prepare("UPDATE queue SET transfer_in_line = :transfer_in_line, ordinal = :ordinal, shift = :shift, schedule = :schedule, queue_number = :queue_number, priority = :priority WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transfer_in_line' => $transfer_in_line,
                ':ordinal' => $ordinal,
                ':shift' => $shift,
                ':schedule' => $schedule,
                ':queue_number' => $queue_number,
                ':priority' => $priority,
                ':transaction_id' => $transaction_id,
            ]);
            $this->sendResponse(true, 'Transaction updated to queue successfully');
        } catch (PDOException $e) {
            error_log('Error updating transaction to queue: ' . $e->getMessage());
            $this->sendResponse(false, 'Error updating transaction to queue');
        }
    }
    public function getToEnter($status)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM transaction INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id INNER JOIN arrival ON transaction.transaction_id = arrival.transaction_id WHERE transaction.status = :status");
            $stmt->execute(['status' => $status]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Transactions retrieved successfully', ['transactions' => $transactions]);
        } catch (PDOException $e) {
            error_log('Error retrieving transactions: ' . $e->getMessage());
            $this->sendResponse(false, 'Error retrieving transactions');
        }
    }
    public function enter($transaction_id)
    {
        try {

            $stmt = $this->conn->prepare("UPDATE transaction SET status = 'standby' WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id
            ]);
            $stmt = $this->conn->prepare("INSERT INTO unloading (transaction_id) VALUES (:transaction_id)");
            $stmt->execute([
                ':transaction_id' => $transaction_id
            ]);
            $this->sendResponse(true, 'Transaction moved to unloading successfully');
        } catch (PDOException $e) {
            error_log('Error moving transaction to unloading: ' . $e->getMessage());
            $this->sendResponse(false, 'Error moving transaction to unloading');
        }
    }
    public function timeOfEntry($transaction_id, $time_of_entry)
    {
        try {
            // Get time of departure
            $stmt = $this->conn->prepare("SELECT time_of_departure FROM transaction WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id
            ]);

            $timeOfDeparture = $stmt->fetchColumn();

            if (!$timeOfDeparture) {
                $this->sendResponse(false, "Arrival time not found for this transaction");
            }


            $timeOfDepartureTimestamp = strtotime($timeOfDeparture);
            $timeOfEntryTimestamp = strtotime($time_of_entry);

            if (is_numeric($timeOfDepartureTimestamp) && is_numeric($timeOfEntryTimestamp)) {
                // Calculate the time difference in seconds
                $timeDifference = $timeOfEntryTimestamp - $timeOfDepartureTimestamp;

                // Convert time difference to hours, minutes, and seconds
                $hours = floor($timeDifference / 3600);
                $minutes = floor(($timeDifference % 3600) / 60);
                $seconds = $timeDifference % 60;

                // Get demurrage rate (fetch the latest one based on updated_at)
                $stmt = $this->conn->prepare("SELECT demurrage FROM demurrage ORDER BY updated_at DESC LIMIT 1");
                $stmt->execute();
                $demurrage = $stmt->fetchColumn();

                if ($demurrage === false) {
                    throw new Exception("Demurrage rate not found");
                }

                $totalDemurrage = 0;
                $demurrageRatePerSecond = $demurrage / 3600; // Convert the hourly rate to a per-second rate

                if ($timeDifference > (48 * 3600)) { // 48 hours in seconds
                    $chargeableSeconds = $timeDifference - (48 * 3600); // Remove the first 48 hours from the calculation
                    $totalDemurrage = $chargeableSeconds * $demurrageRatePerSecond;
                }

                // Insert into unloading
                $unloading_date = date('Y-m-d', strtotime($time_of_entry));
                $stmt = $this->conn->prepare("UPDATE unloading SET time_of_entry = ?, unloading_date = ? WHERE transaction_id = ?");
                $stmt->execute([$time_of_entry, $unloading_date, $transaction_id]);

                // Update transaction with time spent in waiting area and demurrage
                $stmt = $this->conn->prepare("UPDATE transaction SET time_spent_waiting_area = ?, status = 'ongoing', demurrage = ? WHERE transaction_id = ?");
                $stmt->execute([$hours, $totalDemurrage, $transaction_id]);

                // Commit the transaction
                $this->sendResponse(true, "<br>Transaction processed successfully");
            } else {
                throw new Exception("Invalid time format");
            }
        } catch (PDOException $e) {
            error_log('Error processing transaction: ' . $e->getMessage());
            $this->sendResponse(false, 'Error processing transaction');
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
                $queueManager->addArrived($_POST);
                break;
            case 'add to queue':
                $transaction_id = $_POST['transaction_id'] ?? null;
                $transfer_in_line = $_POST['transfer_in_line'] ?? null;
                $ordinal = $_POST['ordinal'] ?? null;
                $shift = $_POST['shift'] ?? null;
                $schedule = $_POST['schedule'] ?? null;
                $queue_number = $_POST['queue_number'] ?? null;
                $priority = $_POST['priority'] ?? null;
                if ($transaction_id) {
                    $queueManager->addToQueue($transaction_id, $transfer_in_line, $ordinal, $shift, $schedule, $queue_number, $priority);
                } else {
                    $queueManager->sendResponse(false, 'Missing transaction ID');
                }
                break;
            case 'update queue':
                $transaction_id = $_POST['transaction_id'] ?? null;
                $transfer_in_line = $_POST['transfer_in_line'] ?? null;
                $ordinal = $_POST['ordinal'] ?? null;
                $shift = $_POST['shift'] ?? null;
                $schedule = $_POST['schedule'] ?? null;
                $queue_number = $_POST['queue_number'] ?? null;
                $priority = $_POST['priority'] ?? null;
                if ($transaction_id) {
                    $queueManager->updateQueue($transaction_id, $transfer_in_line, $ordinal, $shift, $schedule, $queue_number, $priority);
                } else {
                    $queueManager->sendResponse(false, 'Missing transaction ID');
                }
                break;
            case 'list to enter':
                $status = $_POST['status'];
                $queueManager->getToEnter($status);
                break;
            case 'enter to unload':
                $transaction_id = $_POST['transaction_id'] ?? null;
                if ($transaction_id) {
                    $queueManager->enter($transaction_id);
                } else {
                    $queueManager->sendResponse(false, 'Missing transaction ID');
                }
                break;
            case 'time of entry':
                $transaction_id = $_POST['transaction_id'] ?? null;
                $time_of_entry = $_POST['time_of_entry'] ?? null;
                if ($transaction_id) {
                    $queueManager->timeOfEntry($transaction_id, $time_of_entry);
                } else {
                    $queueManager->sendResponse(false, 'Missing transaction ID');
                }
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
