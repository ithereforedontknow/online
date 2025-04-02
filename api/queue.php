<?php
session_start();
require '../config/connection.php';

require '../vendor/autoload.php';



// Improved error handling and security
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
date_default_timezone_set('Asia/Manila');

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
                ORDER BY transaction.updated_at DESC';
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
        INNER JOIN project ON transaction.project_id = project.project_id
        WHERE transaction.status = :status
        ORDER BY queue.queue_number ASC';
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
                $stmt = $this->conn->prepare('INSERT INTO user_logs (user_id, username, action) VALUES (:user_id, :username, :action)');
                $stmt->execute([
                    'user_id' => $_SESSION['id'],
                    'username' => $_SESSION['username'],
                    'action' => 'Added transaction',
                ]);
                $this->sendResponse(true, 'Transaction added successfully');
            }
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error adding transaction');
        }
    }
    public function addToQueue($transaction_id, $transfer_in_line, $ordinal, $shift, $schedule, $priority)
    {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS queue_number
FROM queue
WHERE DATE(created_at) = CURDATE()
");
            $stmt->execute();
            $queue_number = $stmt->fetchColumn();
            $queue_number = $queue_number + 1;

            $stmt = $this->conn->prepare("INSERT INTO queue (transaction_id, transfer_in_line, ordinal, shift, schedule, priority, queue_number) VALUES (:transaction_id, :transfer_in_line, :ordinal, :shift, :schedule, :priority, :queue_number)");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
                ':transfer_in_line' => $transfer_in_line,
                ':ordinal' => $ordinal,
                ':shift' => $shift,
                ':schedule' => $schedule,
                ':priority' => $priority,
                ':queue_number' => $queue_number
            ]);
            $stmt = $this->conn->prepare("UPDATE transaction SET status = 'queue' WHERE transaction_id = :transaction_id");
            $stmt->execute([
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
                ':details' => $to_reference . ' Transaction added to queue by ' . $_SESSION['username'] . ' with transfer in line ' . $transfer_in_line . ', ordinal ' . $ordinal . ', shift ' . $shift . ', schedule ' . $schedule .  ', and priority ' . $priority . ', and queue number ' . $queue_number,
                ':created_by' => $created_by
            ]);
            $stmt = $this->conn->prepare('INSERT INTO user_logs (user_id, username, action) VALUES (:user_id, :username, :action)');
            $stmt->execute([
                'user_id' => $_SESSION['id'],
                'username' => $_SESSION['username'],
                'action' => 'Added transaction to queue',
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
                ':details' => $to_reference .
                    ' Queue details updated by ' . $created_by,
                ':created_by' => $created_by
            ]);
            $stmt = $this->conn->prepare('INSERT INTO user_logs (user_id, username, action) VALUES (:user_id, :username, :action)');
            $stmt->execute([
                'user_id' => $_SESSION['id'],
                'username' => $_SESSION['username'],
                'action' => 'Updated queue details',
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
            $stmt = $this->conn->prepare("SELECT project.project_name, transaction.to_reference, transaction.transaction_id, vehicle.plate_number, transaction.status, arrival.arrival_time 
            FROM transaction 
            INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id 
            INNER JOIN project ON transaction.project_id = project.project_id 
            INNER JOIN arrival ON transaction.transaction_id = arrival.transaction_id 
            WHERE transaction.status = :status || transaction.status = 'standby - sms sent'
            ORDER BY arrival.arrival_time ASC");
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
                ':details' => $to_reference . ' Transaction moved to unloading by ' . $created_by,
                ':created_by' => $created_by
            ]);
            $stmt = $this->conn->prepare('INSERT INTO user_logs (user_id, username, action) VALUES (:user_id, :username, :action)');
            $stmt->execute([
                'user_id' => $_SESSION['id'],
                'username' => $_SESSION['username'],
                'action' => 'Moved transaction to standby',
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
            $query = "SELECT transfer_in_line FROM queue WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':transaction_id' => $transaction_id]);
            $transfer_in_line = $stmt->fetchColumn();

            $query = "SELECT COUNT(*) as count 
                      FROM queue 
                      INNER JOIN transaction ON transaction.transaction_id = queue.transaction_id 
                      WHERE transfer_in_line = :transfer_in_line 
                      AND transaction.status = 'ongoing'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':transfer_in_line' => $transfer_in_line]);
            $countResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $transferInLineCount = $countResult['count'];

            if ($transferInLineCount >= 3) {
                $this->sendResponse(false, $transfer_in_line . '  is full. Maximum of 3 allowed');
                exit;
            }
            $stmt = $this->conn->prepare("SELECT arrival_time FROM arrival WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id
            ]);

            $arrival_time = $stmt->fetchColumn();
            $arrivalTimeVerify = new DateTime($arrival_time);
            $timeOfEntryVerify = new DateTime($time_of_entry);

            if ($arrivalTimeVerify >= $timeOfEntryVerify) {
                $this->sendResponse(false, "Time of entry must be after arrival time");
                return;
            }

            $current_time = date('Y-m-d H:i', time());

            if ($timeOfEntryVerify > new DateTime($current_time)) {
                $this->sendResponse(false, "Time of entry must be before current time");
                return;
            }

            $stmt = $this->conn->prepare("SELECT arrival_time FROM transaction INNER JOIN arrival ON transaction.transaction_id = arrival.transaction_id WHERE transaction.transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id
            ]);

            $arrivalTime = $stmt->fetchColumn();

            if (!$arrivalTime) {
                $this->sendResponse(false, "Arrival time not found for this transaction");
            }


            $arrivalTimeTimestamp = strtotime($arrival_time);
            $timeOfEntryTimestamp = strtotime($time_of_entry);

            if (is_numeric($arrivalTimeTimestamp) && is_numeric($timeOfEntryTimestamp)) {
                // Calculate the time difference in seconds
                $timeDifference = $timeOfEntryTimestamp - $arrivalTimeTimestamp;

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

                $stmt = $this->conn->prepare("SELECT created_by, to_reference FROM transaction WHERE transaction_id = :transaction_id");
                $stmt->execute([
                    ':transaction_id' => $transaction_id,
                ]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $created_by = $row['created_by'];
                $to_reference = $row['to_reference'];

                $stmt = $this->conn->prepare("INSERT INTO transaction_log (transaction_id, details, created_by) VALUES (:transaction_id, :details, :created_by)");
                $stmt->execute([
                    ':transaction_id' => $transaction_id,
                    ':details' => $to_reference . ' Transaction has been processed to enter by ' . $_SESSION['username'],
                    ':created_by' => $created_by
                ]);

                // Commit the transaction
                $this->sendResponse(true, "<br>Transaction processed successfully");
            } else {
                throw new Exception("Invalid time format");
            }
            $stmt = $this->conn->prepare('INSERT INTO user_logs (user_id, username, action) VALUES (:user_id, :username, :action)');
            $stmt->execute([
                'user_id' => $_SESSION['id'],
                'username' => $_SESSION['username'],
                'action' => 'Moved transaction to unloading',
            ]);
        } catch (PDOException $e) {
            error_log('Error processing transaction: ' . $e->getMessage());
            $this->sendResponse(false, 'Error processing transaction', $e->getMessage());
        }
    }
    public function sendSMS($transaction_id, $force = false)
    {
        try {
            // Fetch the transaction details
            $stmt = "SELECT * FROM transaction WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($stmt);
            $stmt->execute(['transaction_id' => $transaction_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $this->sendResponse(false, "Transaction not found");
                return;
            }

            // Check if SMS was already sent and if the action is not forced
            if ($row['status'] === 'standby - sms sent' && !$force) {
                $this->sendResponse(false, "SMS already sent");
                return;
            }

            // Update transaction status
            if (!$force) {
                $stmt = "UPDATE transaction SET status = 'standby - sms sent' WHERE transaction_id = :transaction_id";
                $stmt = $this->conn->prepare($stmt);
                $stmt->execute(['transaction_id' => $transaction_id]);
            }

            // Fetch driver and vehicle details
            $stmt = "SELECT driver.driver_fname, driver.driver_lname, driver.driver_phone, vehicle.plate_number, queue.transfer_in_line 
                 FROM transaction 
                 INNER JOIN driver ON transaction.driver_id = driver.driver_id 
                 INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id 
                 INNER JOIN queue ON transaction.transaction_id = queue.transaction_id
                 WHERE transaction.transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($stmt);
            $stmt->execute(['transaction_id' => $transaction_id]);
            $driver = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$driver) {
                $this->sendResponse(false, "Driver or vehicle details not found");
                return;
            }

            // Construct SMS message
            // $message = "Hi " . $driver['driver_fname'] . " " . $driver['driver_lname'] . ", your vehicle with plate number " . $driver['plate_number'] . " has been processed." . " You may enter " . $driver['transfer_in_line'] . ". Please be on time.";
            // $message = "Dear " . $driver['driver_fname'] . " " . $driver['driver_lname'] . ", your vehicle (Plate #" . $driver['plate_number'] . ") is now processed. You may proceed to " . $driver['transfer_in_line'] . ". Please ensure timely arrival. Thank you!";
            $message = "Good day Mr. " . $driver['driver_fname'] . " " . $driver['driver_lname'] . ",\n\n" .
                "Your vehicle (Plate No. " . $driver['plate_number'] . ") has been successfully processed. " .
                "Kindly proceed to " . $driver['transfer_in_line'] . " at your earliest convenience. " .
                "Please ensure timely arrival.\n\n" .
                "Thank you for your cooperation.";
            // Send SMS via Semaphore API

            $apiKey = "6c557df5b5cfb79a20287af09f6f85af"; // Replace with your API key
            $url = "https://semaphore.co/api/v4/messages";

            $url = 'https://api.semaphore.co/api/v4/messages';
            $number = $driver['driver_phone'];

            try {
                $data = [
                    'apikey' => $apiKey,
                    'number' => $number,
                    'message' => $message,
                    'sendername' => 'PUVFMS'
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

                $response = curl_exec($ch);
                if ($response === false) {
                    throw new Exception('Curl error: ' . curl_error($ch));
                }
                curl_close($ch);
            } catch (Exception $e) {
                error_log('Error sending SMS: ' . $e->getMessage());
                $this->sendResponse(false, 'Failed to send SMS', $e->getMessage());
                return;
            }

            // Log the SMS sending action
            $stmt = "INSERT INTO transaction_log (transaction_id, details, created_by) 
                 VALUES (:transaction_id, :details, :created_by)";
            $stmt = $this->conn->prepare($stmt);
            $stmt->execute([
                ':transaction_id' => $transaction_id,
                ':details' => 'SMS successfully sent to ' . $driver['driver_fname'] . ' ' . $driver['driver_lname'] . ' with truck plate number ' . $driver['plate_number'] . ' by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
            ]);

            // Log SMS details
            $sendAttempt = 1;
            $stmt = "SELECT send_attempt FROM sms_log WHERE transaction_id = :transaction_id AND recipient_number = :recipient_number";
            $stmt = $this->conn->prepare($stmt);
            $stmt->execute([
                ':transaction_id' => $transaction_id,
                ':recipient_number' => $driver['driver_phone']
            ]);
            $existingAttempt = $stmt->fetchColumn();

            if ($existingAttempt !== false) {
                $sendAttempt = $existingAttempt + 1;
                $stmt = "UPDATE sms_log SET message_content = :message_content, send_attempt = :send_attempt WHERE transaction_id = :transaction_id AND recipient_number = :recipient_number";
            } else {
                $stmt = "INSERT INTO sms_log (transaction_id, recipient_number, message_content, send_attempt) VALUES (:transaction_id, :recipient_number, :message_content, :send_attempt)";
            }

            $stmt = $this->conn->prepare($stmt);
            $stmt->execute([
                ':transaction_id' => $transaction_id,
                ':recipient_number' => $driver['driver_phone'],
                ':message_content' => $message,
                ':send_attempt' => $sendAttempt
            ]);

            $this->sendResponse(true, "SMS sent successfully", ['transaction' => $row]);
        } catch (PDOException $e) {
            error_log('Error sending SMS: ' . $e->getMessage());
            $this->sendResponse(false, 'Error sending SMS');
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
                $priority = $_POST['priority'] ?? null;
                if ($transaction_id) {
                    $queueManager->addToQueue($transaction_id, $transfer_in_line, $ordinal, $shift, $schedule, $priority);
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
            case 'send sms':
                $transaction_id = $_POST['transaction_id'];
                $force = isset($_POST['force']) && $_POST['force'] === 'true';
                $queueManager->sendSMS($transaction_id, $force);
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
