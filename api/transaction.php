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
    public function getTransactionStatus()
    {
        $sql = "SELECT plate_number, transaction.status, driver.driver_fname, driver.driver_lname, helper.helper_fname, helper.helper_lname, arrival.arrival_time, unloading.time_of_entry, queue.ordinal, queue.shift, queue.schedule, queue.transfer_in_line FROM transaction INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id INNER JOIN driver ON transaction.driver_id = driver.driver_id INNER JOIN helper ON transaction.helper_id = helper.helper_id LEFT JOIN queue on queue.transaction_id = transaction.transaction_id LEFT JOIN arrival on arrival.transaction_id = transaction.transaction_id LEFT JOIN unloading on unloading.transaction_id = transaction.transaction_id WHERE transaction.status != 'cancelled' AND transaction.status != 'done'";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Success', $transactions);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
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

            // // Validate time of departure
            // if (strtotime($data['time-departure']) < strtotime(date('Y-m-d H:i:s'))) {
            //     throw new Exception('Time of Departure cannot be in the past');
            // }
            // Update driver availability
            $stmt = $this->conn->prepare("
                UPDATE driver
                SET status = '0'
                WHERE driver_id = :driver_id 
            ");
            $stmt->execute(['driver_id' => $data['driver-id']]);

            // Update helper availability
            $stmt = $this->conn->prepare("
                UPDATE helper 
                SET status = '0'
                WHERE helper_id = :helper_id 
            ");
            $stmt->execute(['helper_id' => $data['helper-id']]);

            // Update vehicle availability
            $stmt = $this->conn->prepare("
                UPDATE vehicle
                SET status = '0'
                WHERE vehicle_id = :vehicle_id 
            ");
            $stmt->execute(['vehicle_id' => $data['vehicle-id']]);

            // Insert transaction
            $stmt = $this->conn->prepare("
                INSERT INTO transaction (
                    to_reference, guia, hauler_id, vehicle_id, 
                    driver_id, helper_id, project_id, no_of_bales, 
                    kilos, origin_id, time_of_departure, status,
                    created_at, created_by
                ) VALUES (
                    :to_reference, :guia, :hauler_id, :vehicle_id,
                    :driver_id, :helper_id, :project_id, :no_of_bales,
                    :kilos, :origin_id, :time_of_departure, 'departed',
                    NOW(), :created_by
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
                'time_of_departure' => $data['time-departure'],
                'created_by' => $data['created_by']
            ]);
            $transaction_id = $this->conn->lastInsertId();

            $stmt = $this->conn->prepare("SELECT origin_name from origin where origin_id = :origin_id");
            $stmt->execute(['origin_id' => $data['origin-id']]);
            $origin_name = $stmt->fetch(PDO::FETCH_ASSOC)['origin_name'];

            $stmt = $this->conn->prepare("INSERT INTO transaction_log (transaction_id, created_by, details) VALUES (:transaction_id, :created_by, :details)");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
                ':created_by' =>  $data['created_by'],
                ':details' => 'Transaction created by ' . $data['created_by'] . ' from ' . $origin_name,
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
                ORDER BY transaction.transaction_id DESC';
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
                // Update driver availability
                $stmt = $this->conn->prepare("
            UPDATE driver
            SET status = '0'
            WHERE driver_id = :driver_id 
        ");
                $stmt->execute(['driver_id' => $data['driver_id']]);

                // Update helper availability
                $stmt = $this->conn->prepare("
            UPDATE helper 
            SET status = '0'
            WHERE helper_id = :helper_id 
        ");
                $stmt->execute(['helper_id' => $data['helper_id']]);

                // Update vehicle availability
                $stmt = $this->conn->prepare("
            UPDATE vehicle
            SET status = '0'
            WHERE vehicle_id = :vehicle_id 
        ");
                $stmt->execute(['vehicle_id' => $data['vehicle_id']]);
                $status = "arrived";
                // Insert the transaction
                $stmt = $this->conn->prepare("INSERT INTO transaction (to_reference, guia, hauler_id, vehicle_id, driver_id, helper_id, project_id, no_of_bales, kilos, origin_id, time_of_departure, status, created_by) VALUES (:to_reference, :guia, :hauler_id, :vehicle_id, :driver_id, :helper_id, :project_id, :no_of_bales, :kilos, :origin_id, :time_departure, :status, :created_by)");
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
                    'status' => $status,
                    'created_by' => $data['created_by']
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
                    ':details' => $data['to-reference'] . ' Transaction added by ' . $data['created_by'],
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
    public function updateTranasctionForm($data)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM transaction WHERE to_reference = :to_reference AND transaction_id != :transaction_id");
            $stmt->execute(['to_reference' => $data['to_reference'], 'transaction_id' => $data['transaction_id']]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'TO Reference already exists');
                return;
            } // Update driver availability
            $stmt = $this->conn->prepare("
        UPDATE driver
        SET status = '0'
        WHERE driver_id = :driver_id 
    ");
            $stmt->execute(['driver_id' => $data['driver_id']]);

            // Update helper availability
            $stmt = $this->conn->prepare("
        UPDATE helper 
        SET status = '0'
        WHERE helper_id = :helper_id 
    ");
            $stmt->execute(['helper_id' => $data['helper_id']]);

            // Update vehicle availability
            $stmt = $this->conn->prepare("
        UPDATE vehicle
        SET status = '0'
        WHERE vehicle_id = :vehicle_id 
    ");
            $stmt->execute(['vehicle_id' => $data['vehicle_id']]);

            $stmt = $this->conn->prepare("UPDATE transaction SET to_reference = :to_reference, guia = :guia, hauler_id = :hauler_id, vehicle_id = :vehicle_id, driver_id = :driver_id, helper_id = :helper_id, project_id = :project_id, no_of_bales = :no_of_bales, kilos = :kilos, origin_id = :origin_id, time_of_departure = :time_departure WHERE transaction_id = :transaction_id");
            $stmt->execute([
                'to_reference' => $data['to_reference'],
                'guia' => $data['guia'],
                'hauler_id' => $data['hauler_id'],
                'vehicle_id' => $data['vehicle_id'],
                'driver_id' => $data['driver_id'],
                'helper_id' => $data['helper_id'],
                'project_id' => $data['project_id'],
                'no_of_bales' => $data['no_of_bales'],
                'kilos' => $data['kilos'],
                'origin_id' => $data['origin_id'],
                'time_departure' => $data['time_departure'],
                'transaction_id' => $data['transaction_id']
            ]);

            $stmt = $this->conn->prepare("SELECT created_by FROM transaction WHERE transaction_id = :transaction_id");
            $stmt->execute([':transaction_id' => $data['transaction_id']]);
            $created_by = $stmt->fetchColumn();

            $stmt = $this->conn->prepare("SELECT to_reference FROM transaction WHERE transaction_id = :transaction_id");
            $stmt->execute([':transaction_id' => $data['transaction_id']]);
            $to_reference = $stmt->fetchColumn();

            $stmt = $this->conn->prepare("INSERT INTO transaction_log (transaction_id, details, created_by) VALUES (:transaction_id, :details, :created_by)");
            $stmt->execute([
                ':transaction_id' => $data['transaction_id'],
                ':details' => $to_reference . ' Transaction updated by ' . $created_by,
                ':created_by' => $created_by,
            ]);
            $this->sendResponse(true, 'Transaction updated successfully');
        } catch (PDOException $e) {
            error_log('Error retrieving transactions: ' . $e->getMessage());
            $this->sendResponse(false, 'Error retrieving transactions');
        }
    }
    public function cancelTransaction($data)
    {
        try {
            // Fetch the time of departure for the given transaction
            $stmt = $this->conn->prepare("SELECT time_of_departure FROM transaction WHERE transaction_id = :transaction_id");
            $stmt->execute([':transaction_id' => $data['transaction_id']]);

            $timeOfDeparture = $stmt->fetchColumn();
            if ($timeOfDeparture === false) {
                throw new Exception("Time of departure not found");
            }

            $timeOfDepartureTimestamp = strtotime($timeOfDeparture);
            $currentTimestamp = time();

            // Calculate the time difference in seconds
            $timeDifferenceInSeconds = $currentTimestamp - $timeOfDepartureTimestamp;

            // Convert the time difference to hours, minutes, and seconds
            $hoursSpent = floor($timeDifferenceInSeconds / 3600);
            $minutesSpent = floor(($timeDifferenceInSeconds % 3600) / 60);
            $secondsSpent = $timeDifferenceInSeconds % 60;

            // Fetch the latest demurrage rate
            $stmt = $this->conn->prepare("SELECT demurrage FROM demurrage ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute();
            $demurrageRatePerHour = $stmt->fetchColumn();

            if ($demurrageRatePerHour === false) {
                throw new Exception("Demurrage rate not found");
            }

            $demurrageCharge = 0;
            $demurrageRatePerSecond = $demurrageRatePerHour / 3600; // Convert hourly rate to per-second rate

            if ($timeDifferenceInSeconds > (48 * 3600)) { // More than 48 hours
                $chargeableSeconds = $timeDifferenceInSeconds - (48 * 3600);
                $demurrageCharge = $chargeableSeconds * $demurrageRatePerSecond;
            }

            // Update the transaction with the time spent and demurrage charge
            $stmt = $this->conn->prepare("UPDATE transaction SET time_spent_waiting_area = :hours, demurrage = :demurrage, status = 'cancelled' WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':hours' => $hoursSpent,
                ':demurrage' => $demurrageCharge,
                ':transaction_id' => $data['transaction_id']
            ]);

            $stmt = $this->conn->prepare("INSERT INTO transaction_log (transaction_id, details, created_by) VALUES (:transaction_id, :details, :created_by)");
            $stmt->execute([
                ':transaction_id' => $data['transaction_id'],
                ':details' => 'Transaction cancelled by ' . $data['created_by'],
                ':created_by' => $data['created_by']
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
            $stmt = $this->conn->prepare("SELECT time_of_departure FROM transaction WHERE transaction_id = :transaction_id");
            $stmt->execute([
                ':transaction_id' => $transaction_id,
            ]);

            $time_of_departure = $stmt->fetchColumn();
            $timeOfDeparture = new DateTime($time_of_departure);
            $arrivalTime = new DateTime($arrival_time);
            if ($timeOfDeparture >= $arrivalTime) {
                $this->sendResponse(false, 'Arrival time must be after time of departure');
                return;
            }
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
                ':details' => $to_reference . ' Transaction added to arrived by ' . $created_by,
                ':created_by' => $created_by
            ]);
            $this->sendResponse(true, 'Transaction added to arrived successfully');
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error adding transaction to arrived', $transaction_id);
        }
    }
    public function printTransaction($transaction_id)
    {
        require_once('../fpdf/fpdf.php');
        try {
            // Add error logging
            error_log("Starting PDF generation for transaction: " . $transaction_id);
            $stmt = $this->conn->prepare("
            SELECT t.transaction_id, t.to_reference, t.guia, t.no_of_bales, t.kilos,
                   v.plate_number,
                   d.driver_fname, d.driver_lname,
                   h.helper_fname, h.helper_lname,
                   p.project_name,
                   o.origin_name,
                   ha.hauler_name
            FROM transaction t
            INNER JOIN vehicle v ON t.vehicle_id = v.vehicle_id
            INNER JOIN driver d ON t.driver_id = d.driver_id
            INNER JOIN helper h ON t.helper_id = h.helper_id
            INNER JOIN project p ON t.project_id = p.project_id
            INNER JOIN origin o ON t.origin_id = o.origin_id
            INNER JOIN hauler ha ON t.hauler_id = ha.hauler_id
            WHERE t.transaction_id = :transaction_id
        ");
            $stmt->execute([':transaction_id' => $transaction_id]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$transaction) {
                error_log("Transaction not found: " . $transaction_id);
                throw new Exception('Transaction not found');
            }
            // Clear any output buffers
            while (ob_get_level()) ob_end_clean();
            // Generate PDF
            $pdf = new FPDF('L', 'mm', 'A4'); // Set to landscape
            $pdf->AddPage();
            // Add centered header image
            $imagePath = '../assets/img/ulpi agoo.png';
            if (file_exists($imagePath)) {
                $pdf->Image($imagePath, ($pdf->GetPageWidth() - 100) / 2, 2, 100); // Center the image
            } else {
                error_log("Header image not found at: " . $imagePath);
            }
            // Add space below image
            $pdf->Ln(40);

            // Set normal font for all details
            $pdf->SetFont('Arial', 'B', 16);

            $details = [
                'TO Reference' => $transaction['to_reference'],
                'Guia Number' => $transaction['guia'],
                'Plate Number' => $transaction['plate_number'],
                'Driver' => $transaction['driver_fname'] . ' ' . $transaction['driver_lname'],
                'Helper' => $transaction['helper_fname'] . ' ' . $transaction['helper_lname'],
                'Project' => $transaction['project_name'],
                'Origin' => $transaction['origin_name'],
                'Hauler' => $transaction['hauler_name'],
                'Number of Bales' => $transaction['no_of_bales'],
                'Kilos' => $transaction['kilos']
            ];

            foreach ($details as $label => $value) {
                // Center the label and value
                $pdf->Cell(0, 12, $label . ': ' . $value, 0, 1, 'C');

                // Add some vertical spacing between lines
                $pdf->Ln(2);
            }

            // Output PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="transaction_' . $transaction_id . '.pdf"');
            $pdf->Output('D', 'transaction_' . $transaction_id . '.pdf');
            exit;
        } catch (Exception $e) {
            error_log("PDF Generation Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error generating PDF: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    public function getFinishedTransactions()
    {
        $sql = "SELECT *, transaction.time_of_departure as timeOfDeparture FROM transaction INNER JOIN arrival ON transaction.transaction_id = arrival.transaction_id INNER JOIN queue ON transaction.transaction_id = queue.transaction_id INNER JOIN unloading ON transaction.transaction_id = unloading.transaction_id WHERE status = 'done'";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Success', $transactions);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function updateTransaction($id, $data)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE unloading SET transfer_out_kilos = :transfer_out_kilos, scrap = :scrap, remarks = :remarks WHERE transaction_id = :id");
            $stmt->execute([
                ':transfer_out_kilos' => $data['transfer_out_kilos'],
                ':scrap' => $data['scrap'],
                ':remarks' => $data['remarks'],
                ':id' => $id
            ]);
            $this->sendResponse(true, 'Transaction updated successfully');
        } catch (PDOException $e) {
            // Log the error (in a production environment, log to a file)
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error updating transaction');
        }
    }
    public function updateFinishedTransaction($id, $data)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE transaction SET to_reference = :to_reference, guia = :guia, hauler_id = :hauler_id, vehicle_id = :vehicle_id, driver_id = :driver_id, helper_id = :helper_id, project_id = :project_id, origin_id = :origin_id, no_of_bales = :no_of_bales, kilos = :kilos, time_of_departure = :time_of_departure WHERE transaction_id = :id");
            $stmt->execute([
                ':to_reference' => $data['to_reference'],
                ':guia' => $data['guia'],
                ':hauler_id' => $data['hauler_id'],
                ':vehicle_id' => $data['vehicle_id'],
                ':driver_id' => $data['driver_id'],
                ':helper_id' => $data['helper_id'],
                ':project_id' => $data['project_id'],
                ':origin_id' => $data['origin_id'],
                ':no_of_bales' => $data['no_of_bales'],
                ':kilos' => $data['kilos'],
                ':time_of_departure' => $data['time_departure'],
                ':id' => $id
            ]);

            $arrival_date = date('Y-m-d', strtotime($data['arrival_time']));
            $stmt = $this->conn->prepare("UPDATE arrival SET arrival_time = :arrival_time, arrival_date = :arrival_date WHERE transaction_id = :id");
            $stmt->execute([
                ':arrival_time' => $data['arrival_time'],
                ':arrival_date' => $arrival_date,
                ':id' => $id
            ]);

            $stmt = $this->conn->prepare("UPDATE queue SET transfer_in_line = :transfer_in_line, ordinal = :queue_ordinal, shift = :queue_shift, schedule = :queue_schedule, queue_number = :queue_number, priority = :queue_priority WHERE transaction_id = :id");
            $stmt->execute([
                ':transfer_in_line' => $data['transfer_in_line'],
                ':queue_ordinal' => $data['queue_ordinal'],
                ':queue_shift' => $data['queue_shift'],
                ':queue_schedule' => $data['queue_schedule'],
                ':queue_number' => $data['queue_number'],
                ':queue_priority' => $data['queue_priority'],
                ':id' => $id
            ]);

            $stmt = $this->conn->prepare("UPDATE unloading SET time_of_entry = :time_entry, unloading_time_start = :unloading_start, unloading_time_end = :unloading_end, time_of_departure = :departure, transfer_out_kilos = :transfer_out_kilos, scrap = :scrap, remarks = :remarks WHERE transaction_id = :id");
            $stmt->execute([
                ':time_entry' => $data['time_entry'],
                ':unloading_start' => $data['unloading_start'],
                ':unloading_end' => $data['unloading_end'],
                ':departure' => $data['departure'],
                ':transfer_out_kilos' => $data['transfer_out_kilos'],
                ':scrap' => $data['scrap'],
                ':remarks' => $data['remarks'],
                ':id' => $id
            ]);

            $this->sendResponse(true, 'Transaction updated successfully');
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $this->sendResponse(false, 'Error updating transaction');
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
            case 'transaction status':
                $transactionManager->getTransactionStatus();
                break;
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
            case 'update':
                $transaction_id = $_POST['transaction_id'] ?? null;
                if ($transaction_id) {
                    $transactionManager->updateTranasctionForm($_POST);
                } else {
                    $transactionManager->sendResponse(false, 'Missing transaction ID');
                }
                break;
            case 'cancel':
                $transaction_id = $_POST['transaction_id'] ?? null;
                if ($transaction_id) {
                    $transactionManager->cancelTransaction($_POST);
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
            case 'print transaction':
                $transaction_id = $_POST['transaction_id'] ?? null;
                if ($transaction_id) {
                    $transactionManager->printTransaction($transaction_id);
                } else {
                    $transactionManager->sendResponse(false, 'Missing transaction ID');
                }
                break;
            case 'list finished':
                $transactionManager->getFinishedTransactions();
                break;
            case 'update transaction':
                $transaction_id = $_POST['transaction_id'] ?? null;
                if ($transaction_id) {
                    $transactionManager->updateTransaction($transaction_id, $_POST);
                } else {
                    $transactionManager->sendResponse(false, 'Missing transaction ID');
                }
                break;
            case 'update finished':
                $transaction_id = $_POST['transaction_id'] ?? null;
                if ($transaction_id) {
                    $transactionManager->updateFinishedTransaction($transaction_id, $_POST);
                } else {
                    $transactionManager->sendResponse(false, 'Missing transaction ID');
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
