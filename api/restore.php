<?php
require '../config/connection.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

class DatabaseManager
{
    private $conn;
    private $dsn;
    private $username;
    private $password;

    public function __construct($dsn, $username, $password)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
    }

    private function connect()
    {
        try {
            $this->conn = new PDO($this->dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    public function restore($sqlFile)
    {
        $this->connect();

        try {
            $sqlContent = file_get_contents($sqlFile);

            // Disable foreign key checks
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Get database name
            $database = substr($this->dsn, strpos($this->dsn, 'dbname=') + 7);

            // Drop all existing tables
            $dropTablesQuery = "SELECT concat('DROP TABLE IF EXISTS `', table_name, '`;') AS stmt 
                FROM information_schema.tables 
                WHERE table_schema = '$database'";
            $stmt = $this->conn->query($dropTablesQuery);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->exec($row['stmt']);
            }

            // Split SQL file into statements
            $statements = preg_split('/;\s*$/m', $sqlContent);

            // Execute each statement
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $this->conn->exec($statement);
                }
            }

            // Re-enable foreign key checks
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 1");

            return true;
        } catch (PDOException $e) {
            throw new Exception("Restore failed: " . $e->getMessage());
        } finally {
            $this->conn = null;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['restore-file'])) {
    $allowedExtensions = ['sql'];
    $maxFileSize = 50 * 1024 * 1024;

    $fileExtension = strtolower(pathinfo($_FILES['restore-file']['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid file type. Only SQL files are allowed.'
        ]);
        exit;
    }

    if ($_FILES['restore-file']['size'] > $maxFileSize) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'File too large. Maximum file size is 50MB.'
        ]);
        exit;
    }

    $uploadDir = sys_get_temp_dir() . '/';
    $uploadFile = $uploadDir . basename($_FILES['restore-file']['name']);

    if (move_uploaded_file($_FILES['restore-file']['tmp_name'], $uploadFile)) {
        try {
            $dbManager = new DatabaseManager($dsn, $username, $password);
            $dbManager->restore($uploadFile);
            unlink($uploadFile);

            echo json_encode(['success' => true, 'message' => 'Database restored successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'File upload failed'
        ]);
    }
    exit;
}
