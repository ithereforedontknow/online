<?php
session_start();
require '../config/connection.php';

// Improved error handling and security
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

class settingsManager
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
    public function getHaulers()
    {
        try {
            $sql = "SELECT * FROM hauler INNER JOIN origin ON hauler.branch = origin.origin_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $haulers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Success', $haulers);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function addhauler($name, $branch, $address)
    {
        try {
            $sql = "SELECT * FROM hauler WHERE hauler_name = :name";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'name' => $name
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Hauler already exists!');
                return;
            }

            $sql = "INSERT INTO hauler (hauler_name, branch, hauler_address, status) VALUES (:name, :branch, :address, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'branch' => $branch,
                'address' => $address
            ]);
            $this->sendResponse(true, 'Hauler added successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function updateHauler($id, $name, $branch, $address)
    {
        try {
            $sql = "UPDATE hauler SET hauler_name = :name, branch = :branch, hauler_address = :address WHERE hauler_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'branch' => $branch,
                'address' => $address,
                'id' => $id
            ]);
            $this->sendResponse(true, 'Hauler updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function toggleHaulerStatus($id, $status)
    {
        try {
            if ($status === 'false') {
                $status = 0;
            } else {
                $status = 1;
            }
            $sql = "UPDATE hauler SET hauler.status = :status WHERE hauler_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'status' => $status,
                'id' => $id
            ]);
            $this->sendResponse(true, 'Hauler status updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function getVehicles()
    {
        try {
            $sql = "SELECT * FROM vehicle INNER JOIN hauler ON vehicle.hauler_id = hauler.hauler_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Success', $vehicles);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function addVehicle($plate_number, $truck_type, $hauler_id)
    {
        try {
            $sql = "SELECT * FROM vehicle WHERE plate_number = :plate_number";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'plate_number' => $plate_number,
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Plate number already exists!');
                return;
            }
            $sql = "INSERT INTO vehicle (plate_number, truck_type, hauler_id) VALUES (:plate_number, :truck_type, :hauler_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'plate_number' => $plate_number,
                'truck_type' => $truck_type,
                'hauler_id' => $hauler_id
            ]);
            $this->sendResponse(true, 'Vehicle added successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function updateVehicle($id, $plate_number, $truck_type, $hauler_id)
    {
        try {
            $sql = "UPDATE vehicle SET plate_number = :plate_number, truck_type = :truck_type, hauler_id = :hauler_id WHERE vehicle_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'plate_number' => $plate_number,
                'truck_type' => $truck_type,
                'hauler_id' => $hauler_id,
                'id' => $id
            ]);
            $this->sendResponse(true, 'Vehicle updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function getDrivers()
    {
        try {
            $sql = "SELECT * FROM driver INNER JOIN hauler ON driver.hauler_id = hauler.hauler_id INNER JOIN origin ON hauler.branch = origin.origin_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Success', $drivers);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function getHelpers()
    {
        try {
            $sql = "SELECT * FROM helper INNER JOIN hauler ON helper.hauler_id = hauler.hauler_id INNER JOIN origin ON hauler.branch = origin.origin_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $helpers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Success', $helpers);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function addDriver($hauler_id, $driver_fname, $driver_mname, $driver_lname, $driver_phone)
    {
        try {
            $sql = "INSERT INTO driver (hauler_id, driver_fname, driver_mname, driver_lname, driver_phone) VALUES (:hauler_id, :driver_fname, :driver_mname, :driver_lname, :driver_phone)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'hauler_id' => $hauler_id,
                'driver_fname' => $driver_fname,
                'driver_mname' => $driver_mname,
                'driver_lname' => $driver_lname,
                'driver_phone' => $driver_phone
            ]);
            $this->sendResponse(true, 'Driver added successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function updateDriver($driver_id, $hauler_id, $driver_fname, $driver_mname, $driver_lname, $driver_phone)
    {
        try {
            $sql = "UPDATE driver SET hauler_id = :hauler_id, driver_fname = :driver_fname, driver_mname = :driver_mname, driver_lname = :driver_lname, driver_phone = :driver_phone WHERE driver_id = :driver_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'hauler_id' => $hauler_id,
                'driver_fname' => $driver_fname,
                'driver_mname' => $driver_mname,
                'driver_lname' => $driver_lname,
                'driver_phone' => $driver_phone,
                'driver_id' => $driver_id
            ]);
            $this->sendResponse(true, 'Driver updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }

    public function addHelper($hauler_id, $helper_fname, $helper_mname, $helper_lname, $helper_phone)
    {
        try {
            $sql = "INSERT INTO helper (hauler_id, helper_fname, helper_mname, helper_lname, helper_phone) VALUES (:hauler_id, :helper_fname, :helper_mname, :helper_lname, :helper_phone)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'hauler_id' => $hauler_id,
                'helper_fname' => $helper_fname,
                'helper_mname' => $helper_mname,
                'helper_lname' => $helper_lname,
                'helper_phone' => $helper_phone
            ]);
            $this->sendResponse(true, 'Helper added successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function updateHelper($helper_id, $hauler_id, $helper_fname, $helper_mname, $helper_lname, $helper_phone)
    {
        try {
            $sql =  "UPDATE helper SET hauler_id = :hauler_id, helper_fname = :helper_fname, helper_mname = :helper_mname, helper_lname = :helper_lname, helper_phone = :helper_phone WHERE helper_id = :helper_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'hauler_id' => $hauler_id,
                'helper_fname' => $helper_fname,
                'helper_mname' => $helper_mname,
                'helper_lname' => $helper_lname,
                'helper_phone' => $helper_phone,
                'helper_id' => $helper_id
            ]);
            $this->sendResponse(true, 'Helper updated successfully');
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

    $settingsManager = new settingsManager($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'list haulers':
                $settingsManager->getHaulers();
                break;
            case 'create hauler':
                $name = $_POST['hauler_name'] ?? null;
                $branch = $_POST['branch'] ?? null;
                $address = $_POST['hauler_address'] ?? null;
                $settingsManager->addhauler($name, $branch, $address);
                break;
            case 'update hauler':
                $id = $_POST['hauler_id'] ?? null;
                $name = $_POST['hauler_name'] ?? null;
                $branch = $_POST['branch'] ?? null;
                $address = $_POST['hauler_address'] ?? null;
                $settingsManager->updateHauler($id, $name, $branch, $address);
                break;
            case 'toggle hauler status':
                $id = $_POST['hauler_id'] ?? null;
                $status = $_POST['status'] ?? null;
                $settingsManager->toggleHaulerStatus($id, $status);
                break;
            case 'list vehicles':
                $settingsManager->getVehicles();
                break;
            case 'create vehicle':
                $plate_number = $_POST['plate_number'] ?? null;
                $truck_type = $_POST['truck_type'] ?? null;
                $hauler_id = $_POST['hauler_id'] ?? null;
                $settingsManager->addVehicle($plate_number, $truck_type, $hauler_id);
                break;
            case 'update vehicle':
                $id = $_POST['vehicle_id'] ?? null;
                $plate_number = $_POST['plate_number'] ?? null;
                $truck_type = $_POST['truck_type'] ?? null;
                $hauler_id = $_POST['hauler_id'] ?? null;
                $settingsManager->updateVehicle($id, $plate_number, $truck_type, $hauler_id);
                break;
            case 'list drivers':
                $settingsManager->getDrivers();
                break;
            case 'create driver':
                $hauler_id = $_POST['hauler_id'] ?? null;
                $driver_fname = $_POST['driver_fname'] ?? null;
                $driver_mname = $_POST['driver_mname'] ?? null;
                $driver_lname = $_POST['driver_lname'] ?? null;
                $driver_phone = $_POST['driver_phone'] ?? null;
                $settingsManager->addDriver($hauler_id, $driver_fname, $driver_mname, $driver_lname, $driver_phone);
                break;
            case 'update driver':
                $driver_id = $_POST['driver_id'] ?? null;
                $hauler_id = $_POST['hauler_id'] ?? null;
                $driver_fname = $_POST['driver_fname'] ?? null;
                $driver_mname = $_POST['driver_mname'] ?? null;
                $driver_lname = $_POST['driver_lname'] ?? null;
                $driver_phone = $_POST['driver_phone'] ?? null;
                $settingsManager->updateDriver($driver_id, $hauler_id, $driver_fname, $driver_mname, $driver_lname, $driver_phone);
                break;
            case 'list helpers':
                $settingsManager->getHelpers();
                break;
            case 'create helper':
                $hauler_id = $_POST['hauler_id'] ?? null;
                $helper_fname = $_POST['helper_fname'] ?? null;
                $helper_mname = $_POST['helper_mname'] ?? null;
                $helper_lname = $_POST['helper_lname'] ?? null;
                $helper_phone = $_POST['helper_phone'] ?? null;
                $settingsManager->addHelper($hauler_id, $helper_fname, $helper_mname, $helper_lname, $helper_phone);
                break;
            case 'update helper':
                $helper_id = $_POST['helper_id'] ?? null;
                $hauler_id = $_POST['hauler_id'] ?? null;
                $helper_fname = $_POST['helper_fname'] ?? null;
                $helper_mname = $_POST['helper_mname'] ?? null;
                $helper_lname = $_POST['helper_lname'] ?? null;
                $helper_phone = $_POST['helper_phone'] ?? null;
                $settingsManager->updateHelper($helper_id, $hauler_id, $helper_fname, $helper_mname, $helper_lname, $helper_phone);
                break;
            default:
                $settingsManager->sendResponse(false, 'Invalid action');
        }
    } else {
        $settingsManager->sendResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    error_log('Unhandled error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
    exit;
}
