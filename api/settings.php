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
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('hauler', :details, :created_by)");
            $stmt->execute([
                ':details' => $name . ' Hauler added by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
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
            $sql = "SELECT * FROM hauler WHERE hauler_name = :name AND hauler_id != :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'id' => $id
            ]);

            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Hauler already exists! Please choose a different name.');
                return;
            }

            // Proceed with the update
            $sql = "UPDATE hauler SET hauler_name = :name, branch = :branch, hauler_address = :address WHERE hauler_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'branch' => $branch,
                'address' => $address,
                'id' => $id
            ]);

            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('hauler', :details, :created_by)");
            $stmt->execute([
                ':details' => $name . ' Hauler updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
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
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('hauler', :details, :created_by)");
            $stmt->execute([
                ':details' => 'Hauler status updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
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
            $sql = "SELECT vehicle.*, hauler.hauler_name, hauler.hauler_id FROM vehicle INNER JOIN hauler ON vehicle.hauler_id = hauler.hauler_id";
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
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('vehicle', :details, :created_by)");
            $stmt->execute([
                ':details' => $plate_number . ' Vehicle added by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
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
            $sql = "SELECT * FROM vehicle WHERE plate_number = :plate_number AND vehicle_id != :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'plate_number' => $plate_number,
                'id' => $id
            ]);

            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Plate number already exists! Please choose a different plate number.');
                return;
            }
            $sql = "UPDATE vehicle SET plate_number = :plate_number, truck_type = :truck_type, hauler_id = :hauler_id WHERE vehicle_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'plate_number' => $plate_number,
                'truck_type' => $truck_type,
                'hauler_id' => $hauler_id,
                'id' => $id
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('vehicle', :details, :created_by)");
            $stmt->execute([
                ':details' => $plate_number . ' Vehicle updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
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
            $sql = "SELECT driver.driver_id, driver.driver_fname, driver.driver_lname, driver.driver_mname, driver.driver_phone, hauler.hauler_name, driver.status, origin.origin_name, hauler.hauler_id FROM driver INNER JOIN hauler ON driver.hauler_id = hauler.hauler_id INNER JOIN origin ON hauler.branch = origin.origin_id";
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
            $sql = "SELECT helper.helper_id, helper.helper_fname, helper.helper_lname, helper.helper_mname, helper.helper_phone, hauler.hauler_name, helper.status, origin.origin_name, hauler.hauler_id FROM helper INNER JOIN hauler ON helper.hauler_id = hauler.hauler_id INNER JOIN origin ON hauler.branch = origin.origin_id";
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
            $sql = "SELECT * FROM driver WHERE driver_fname = :driver_fname AND driver_mname = :driver_mname AND driver_lname = :driver_lname";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'driver_fname' => $driver_fname,
                'driver_mname' => $driver_mname,
                'driver_lname' => $driver_lname
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Driver already exist!');
                return;
            }
            $sql = "SELECT * FROM driver WHERE driver_phone = :driver_phone UNION SELECT * FROM helper WHERE helper_phone = :driver_phone";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'driver_phone' => $driver_phone
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Phone number already exist!');
                return;
            }
            $sql = "INSERT INTO driver (hauler_id, driver_fname, driver_mname, driver_lname, driver_phone) VALUES (:hauler_id, :driver_fname, :driver_mname, :driver_lname, :driver_phone)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'hauler_id' => $hauler_id,
                'driver_fname' => $driver_fname,
                'driver_mname' => $driver_mname,
                'driver_lname' => $driver_lname,
                'driver_phone' => $driver_phone
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('driver', :details, :created_by)");
            $stmt->execute([
                ':details' => $driver_fname . ' Driver added by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
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
            $sql = "SELECT * FROM driver WHERE driver_fname = :driver_fname AND driver_mname = :driver_mname AND driver_lname = :driver_lname AND driver_id != :driver_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'driver_fname' => $driver_fname,
                'driver_mname' => $driver_mname,
                'driver_lname' => $driver_lname,
                'driver_id' => $driver_id
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Driver already exist! Please try again.');
                return;
            }
            $sql = "SELECT * FROM driver WHERE driver_phone = :driver_phone AND driver_id != :driver_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'driver_phone' => $driver_phone,
                'driver_id' => $driver_id
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Phone number already exist! Please try again.');
                return;
            }
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
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('driver', :details, :created_by)");
            $stmt->execute([
                ':details' => $driver_fname . ' Driver updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
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
            $sql = "SELECT * FROM helper WHERE helper_fname = :helper_fname AND helper_mname = :helper_mname AND helper_lname = :helper_lname";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'helper_fname' => $helper_fname,
                'helper_mname' => $helper_mname,
                'helper_lname' => $helper_lname
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Helper already exist!');
                return;
            }
            $sql = "SELECT * FROM helper WHERE helper_phone = :helper_phone UNION SELECT * FROM driver WHERE driver_phone = :helper_phone";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'helper_phone' => $helper_phone
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Phone number already exist!');
                return;
            }
            $sql = "INSERT INTO helper (hauler_id, helper_fname, helper_mname, helper_lname, helper_phone) VALUES (:hauler_id, :helper_fname, :helper_mname, :helper_lname, :helper_phone)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'hauler_id' => $hauler_id,
                'helper_fname' => $helper_fname,
                'helper_mname' => $helper_mname,
                'helper_lname' => $helper_lname,
                'helper_phone' => $helper_phone
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('helper', :details, :created_by)");
            $stmt->execute([
                ':details' => $helper_fname . ' Helper added by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
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
            $sql = "SELECT * FROM helper WHERE helper_fname = :helper_fname AND helper_mname = :helper_mname AND helper_lname = :helper_lname AND helper_id != :helper_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'helper_fname' => $helper_fname,
                'helper_mname' => $helper_mname,
                'helper_lname' => $helper_lname,
                'helper_id' => $helper_id
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Helper already exist! Please try again.');
                return;
            }
            $sql = "SELECT * FROM helper WHERE helper_phone = :helper_phone AND helper_id != :helper_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'helper_phone' => $helper_phone,
                'helper_id' => $helper_id
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Helper Phone number already exist! Please try again.');
                return;
            }
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
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('helper', :details, :created_by)");
            $stmt->execute([
                ':details' => $helper_fname . ' Helper updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
            ]);
            $this->sendResponse(true, 'Helper updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function getProjects()
    {
        try {
            $sql = "SELECT * FROM project";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Success', $projects);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function addProject($project_name, $project_description)
    {
        try {
            $sql = "SELECT * FROM project WHERE project_name = :project_name";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'project_name' => $project_name
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Project already exist!');
                return;
            }
            $sql = "INSERT INTO project (project_name, project_description) VALUES (:project_name, :project_description)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'project_name' => $project_name,
                'project_description' => $project_description
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('project', :details, :created_by)");
            $stmt->execute([
                ':details' => $project_name . ' Project added by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
            ]);
            $this->sendResponse(true, 'Project added successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function updateProject($project_id, $project_name, $project_description)
    {
        try {
            $sql = "SELECT * FROM project WHERE project_name = :project_name AND project_id != :project_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'project_name' => $project_name,
                'project_id' => $project_id
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Project already exist! Please try again.');
                return;
            }
            $sql = "UPDATE project SET project_name = :project_name, project_description = :project_description WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'project_name' => $project_name,
                'project_description' => $project_description,
                'project_id' => $project_id
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('project', :details, :created_by)");
            $stmt->execute([
                ':details' => $project_name . ' Project updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
            ]);
            $this->sendResponse(true, 'Project updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function getOrigins()
    {
        try {
            $sql = "SELECT * FROM origin";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $origins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Success', $origins);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function addOrigin($origin_name, $origin_code)
    {
        try {
            $sql = "SELECT * FROM origin WHERE origin_name = :origin_name";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'origin_name' => $origin_name
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Origin already exist!');
                return;
            }
            $sql = "INSERT INTO origin (origin_name, origin_code) VALUES (:origin_name, :origin_code)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'origin_name' => $origin_name,
                'origin_code' => $origin_code
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('origin', :details, :created_by)");
            $stmt->execute([
                ':details' => $origin_name . ' Origin added by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
            ]);
            $this->sendResponse(true, 'Origin added successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function updateOrigin($origin_id, $origin_name, $origin_code)
    {
        try {
            $sql = "SELECT * FROM origin WHERE origin_name = :origin_name AND origin_id != :origin_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'origin_name' => $origin_name,
                'origin_id' => $origin_id
            ]);
            if ($stmt->rowCount() > 0) {
                $this->sendResponse(false, 'Origin already exist! Please try again.');
                return;
            }
            $sql = "UPDATE origin SET origin_name = :origin_name, origin_code = :origin_code WHERE origin_id = :origin_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'origin_name' => $origin_name,
                'origin_code' => $origin_code,
                'origin_id' => $origin_id
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('origin', :details, :created_by)");
            $stmt->execute([
                ':details' => $origin_name . ' Origin updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
            ]);
            $this->sendResponse(true, 'Origin updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function getDemurrage()
    {
        try {
            $sql = "SELECT * FROM demurrage ORDER BY updated_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $demurrage = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendResponse(true, 'Success', $demurrage);
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function updateDemurrage($demurrage)
    {
        try {
            $sql = "INSERT INTO demurrage (demurrage) VALUES (:demurrage)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'demurrage' => $demurrage
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('demurrage', :details, :created_by)");
            $stmt->execute([
                ':details' => $demurrage . ' pesos' . ' Demurrage updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
            ]);
            $this->sendResponse(true, 'Demurrage updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function toggleDriverStatus($driver_id, $status)
    {
        try {
            $sql = "UPDATE driver SET status = :status WHERE driver_id = :driver_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'status' => $status,
                'driver_id' => $driver_id
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('driver', :details, :created_by)");
            $stmt->execute([
                ':details' => 'Driver status updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
            ]);
            $this->sendResponse(true, 'Driver status updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
        }
    }

    public function toggleHelperStatus($helper_id, $status)
    {
        try {
            $sql = "UPDATE helper SET status = :status WHERE helper_id = :helper_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'status' => $status,
                'helper_id' => $helper_id
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('driver', :details, :created_by)");
            $stmt->execute([
                ':details' => 'Helper status updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
            ]);
            $this->sendResponse(true, 'Helper status updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
        }
    }

    public function toggleVehicleStatus($vehicle_id, $status)
    {
        try {
            $sql = "UPDATE vehicle SET status = :status WHERE vehicle_id = :vehicle_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'status' => $status,
                'vehicle_id' => $vehicle_id
            ]);
            $stmt = $this->conn->prepare("INSERT INTO settings_logs (settings_name, details, created_by) VALUES ('driver', :details, :created_by)");
            $stmt->execute([
                ':details' => 'Vehicle status updated by ' . $_SESSION['username'],
                ':created_by' => $_SESSION['username']
            ]);
            $this->sendResponse(true, 'Vehicle status updated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
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
            case 'list projects':
                $settingsManager->getProjects();
                break;
            case 'create project':
                $project_name = $_POST['project_name'] ?? null;
                $project_description = $_POST['project_description'] ?? null;
                $settingsManager->addProject($project_name, $project_description);
                break;
            case 'update project':
                $project_id = $_POST['project_id'] ?? null;
                $project_name = $_POST['project_name'] ?? null;
                $project_description = $_POST['project_description'] ?? null;
                $settingsManager->updateProject($project_id, $project_name, $project_description);
                break;
            case 'list origins':
                $settingsManager->getOrigins();
                break;
            case 'create origin':
                $origin_name = $_POST['origin_name'] ?? null;
                $origin_code = $_POST['origin_code'] ?? null;
                $settingsManager->addOrigin($origin_name, $origin_code);
                break;
            case 'update origin':
                $origin_id = $_POST['origin_id'] ?? null;
                $origin_name = $_POST['origin_name'] ?? null;
                $origin_code = $_POST['origin_code'] ?? null;
                $settingsManager->updateOrigin($origin_id, $origin_name, $origin_code);
                break;
            case 'list demurrage':
                $settingsManager->getDemurrage();
                break;
            case 'update demurrage':
                $demurrage = $_POST['demurrage'] ?? null;
                $settingsManager->updateDemurrage($demurrage);
                break;
            case 'toggle driver status':
                $driver_id = $_POST['driver_id'] ?? null;
                $status = $_POST['status'] ?? null;
                $settingsManager->toggleDriverStatus($driver_id, $status);
                break;
            case 'toggle helper status':
                $helper_id = $_POST['helper_id'] ?? null;
                $status = $_POST['status'] ?? null;
                $settingsManager->toggleHelperStatus($helper_id, $status);
                break;
            case 'toggle vehicle status':
                $vehicle_id = $_POST['vehicle_id'] ?? null;
                $status = $_POST['status'] ?? null;
                $settingsManager->toggleVehicleStatus($vehicle_id, $status);
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
