<?php
session_start();
require '../config/connection.php';

// Improved error handling and security
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

class mainManager
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
    public function getTransactionCount($data)
    {
        try {

            $selectedPeriod = $data['period'] ?? 'year';

            $timeCondition = match ($selectedPeriod) {
                'today' => "DATE(created_at) = CURDATE()",
                'month' => "MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())",
                default => "YEAR(created_at) = YEAR(CURRENT_DATE())",
            };

            // Adjust the query to return the correct labels and transaction counts
            $query = "
    SELECT 
        " . ($selectedPeriod === 'today' ? "HOUR(created_at)" : ($selectedPeriod === 'month' ? "DAY(created_at)" : "MONTH(created_at)")) . " AS label,
        COUNT(*) AS transaction_count
    FROM Transaction
    WHERE $timeCondition
    GROUP BY label
    ORDER BY label
";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            echo json_encode($data);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function getNotifications()
    {
        try {
            $limit = $_POST['limit'] ?? 15;
            $offset = $_POST['offset'] ?? 0;
            $searchTerm = $_POST['search'] ?? '';

            // Prepare base query with optional search condition
            $baseQuery = "FROM 
            transaction_log 
        INNER JOIN 
            transaction 
        ON 
            transaction_log.transaction_id = transaction.transaction_id ";

            // Add search condition if search term is provided
            $whereClause = "";
            $params = [];
            if (!empty($searchTerm)) {
                $whereClause = "WHERE (
                transaction_log.details LIKE :searchTerm OR 
                transaction_log.transaction_id LIKE :searchTerm OR 
                transaction.to_reference LIKE :searchTerm
            )";
                $params[':searchTerm'] = "%{$searchTerm}%";
            }

            // Count total notifications with search filter
            $countQuery = "SELECT COUNT(*) " . $baseQuery . $whereClause;
            $countStmt = $this->conn->prepare($countQuery);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $totalNotifications = $countStmt->fetchColumn();

            // Main query with pagination and search
            $query = "SELECT 
            transaction_log.transaction_id AS transaction_id, 
            transaction_log.created_at AS created_at,
            transaction_log.details AS details, 
            transaction.to_reference AS to_reference
        " . $baseQuery . $whereClause . "
        ORDER BY 
            transaction_log.log_id DESC
        LIMIT :limit OFFSET :offset;";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            // Bind search parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Determine if there are more notifications
            $hasMore = ($offset + $limit) < $totalNotifications;

            $this->sendResponse(true, 'Notifications retrieved successfully', [
                'notifications' => $notifications,
                'hasMore' => $hasMore,
                'total' => $totalNotifications
            ]);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function getNotificationCount()
    {
        try {
            $currentTime = date('Y-m-d H:i:s');
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM transaction_log WHERE created_at >= :currentTime");
            $stmt->bindParam(':currentTime', $currentTime);
            $stmt->execute();
            $transactionCount = $stmt->fetchColumn();
            $this->sendResponse(true, 'Notification count retrieved successfully', ['notification_count' => $transactionCount]);
        } catch (Exception $e) {
            $this->sendResponse(false, 'Internal server error');
        }
    }
}

// Main API Handler
try {

    if (!isset($conn) || $conn === null) {
        throw new Exception('Database connection not established');
    }

    $mainManager = new mainManager($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'get-transaction-count':
                $mainManager->getTransactionCount($_POST);
                break;
            case 'get-notifications':
                $mainManager->getNotifications();
                break;
            case 'get-notification-count':
                $mainManager->getNotificationCount();
                break;
            default:
                $mainManager->sendResponse(false, 'Invalid action');
        }
    } else {
        $mainManager->sendResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    error_log('Unhandled error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
    exit;
}
