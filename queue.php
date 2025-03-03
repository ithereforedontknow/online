<?php
require_once 'config/connection.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vehicle Management Dashboard</title>
    <link rel="icon" type="image/x-icon" href="assets/img/Untitled-1.png" />
    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap");

        body {
            padding-top: 56px;
            font-family: "Lato", sans-serif;
        }

        html,
        body {
            height: 100%;
        }
    </style>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="bg-light py-4">
    <div class="container bg-white p-4 rounded shadow-sm">
        <div class="d-flex justify-content-center">
            <img
                src="../../assets/img//ulpi agoo.png"
                alt="Universal Corporation Logo"
                class="img-fluid flip-forever"
                style="max-width: 300px; animation: flip 3s ease-in-out infinite;" />
        </div>

        <style>
            .flip-forever {
                animation: flip 3s ease-in-out infinite;
            }

            @keyframes flip {
                0% {
                    transform: rotateY(0deg);
                }

                100% {
                    transform: rotateY(360deg);
                }
            }
        </style>


        <div class="row g-4">
            <div class="col">
                <div class="container bg-white p-4 rounded shadow-sm">
                    <h5 class="mb-0 text-center fs-3">
                        <i class="fas fa-check-circle me-2" style="color: #0d6efd"></i>Arrived
                    </h5>
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th scope="col">Plate Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM transaction INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id WHERE transaction.status = 'arrived'");
                            $stmt->execute();
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (!$result) {
                                echo "<tr><td>None</td></tr>";
                            }
                            foreach ($result as $row) {
                                echo "<tr><td>" . $row["plate_number"] . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col">
                <div class="container bg-white p-4 rounded shadow-sm">
                    <h5 class="mb-0 text-center fs-3">
                        <i class="fas fa-hourglass-half me-2" style="color: #0d6efd"></i>Queue
                    </h5>
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th scope="col">Plate Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM transaction INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id WHERE transaction.status = 'queue'");
                            $stmt->execute();
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (!$result) {
                                echo "<tr><td>None</td></tr>";
                            }
                            foreach ($result as $row) {
                                echo "<tr><td>" . $row["plate_number"] . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col">
                <div class="container bg-white p-4 rounded shadow-sm">
                    <h5 class="mb-0 text-center fs-3">
                        <i class="fas fa-sign-in-alt me-2" style="color: #0d6efd"></i>To
                        Enter
                    </h5>
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th scope="col">Plate Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM transaction INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id WHERE transaction.status = 'standby' OR transaction.status = 'standby - sms sent'");
                            $stmt->execute();
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (!$result) {
                                echo "<tr><td>None</td></tr>";
                            }
                            foreach ($result as $row) {
                                echo "<tr><td>" . $row["plate_number"] . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <p class="text-muted text-center small mt-4">
            Last updated: <span id="current-time"></span>
        </p>
    </div>

    <script src="public/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/jquery.min.js"></script>
    <script>
        // Display current time
        function updateTime() {
            const now = new Date();
            document.getElementById("current-time").textContent =
                now.toLocaleString();
        }
        updateTime();
        setInterval(updateTime, 60000); // Update every minute
    </script>
</body>

</html>