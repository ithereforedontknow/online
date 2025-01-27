<?php
require '../config/connection.php';

// Improved error handling and security
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

    public function backup()
    {
        $this->connect();

        try {
            // Disable foreign key checks
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Get all table names
            $tables = [];
            $stmt = $this->conn->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            // Create SQL dump
            $sqlDump = "-- Database Backup\n";
            $sqlDump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            foreach ($tables as $table) {
                // Table structure
                $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
                $createStmt = $this->conn->query("SHOW CREATE TABLE `$table`");
                $createTable = $createStmt->fetch(PDO::FETCH_ASSOC);
                $sqlDump .= $createTable['Create Table'] . ";\n\n";

                // Table data
                $dataStmt = $this->conn->query("SELECT * FROM `$table`");
                $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    $sqlDump .= "INSERT INTO `$table` VALUES\n";
                    $rowCount = 0;
                    foreach ($rows as $row) {
                        $rowCount++;
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $this->conn->quote($value);
                            }
                        }
                        $sqlDump .= "(" . implode(",", $values) . ")";
                        $sqlDump .= ($rowCount < count($rows)) ? ",\n" : ";\n\n";
                    }
                }
            }

            $sqlDump .= "SET FOREIGN_KEY_CHECKS = 1;\n";

            return $sqlDump;
        } catch (PDOException $e) {
            throw new Exception("Backup failed: " . $e->getMessage());
        } finally {
            $this->conn = null;
        }
    }
}

// Backup endpoint
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    try {
        $dbManager = new DatabaseManager($dsn, $username, $password);

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="database_backup_' . date('Y-m-d') . '.sql"');

        echo $dbManager->backup();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
