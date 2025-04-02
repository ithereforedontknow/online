<?php
include_once('../../includes/header/header-main.php');
?>
<div class="content bg-light" id="content">
    <div class="container-fluid">
        <!-- Header -->
        <div class=" bg-white p-4 rounded shadow-sm">
            <a class="btn btn-primary" href="queue.php">Go Back</a>
            <div class="d-flex justify-content-center">
                <img
                    src="../../assets/img//ulpi agoo.png"
                    alt="Universal Corporation Logo"
                    class="img-fluid"
                    style="max-width: 300px" />
            </div>


            <div class="row g-4">
                <div class="col">
                    <div class="container bg-white p-4 rounded shadow-sm">
                        <h5 class="mb-0 text-center fs-3">
                            <i class="fas fa-check-circle me-2" style="color: #1b3667"></i>Arrived
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
                            <i class="fas fa-hourglass-half me-2" style="color: #1b3667"></i>Queue
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
                            <i class="fas fa-sign-in-alt me-2" style="color: #1b3667"></i>To
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
    </div>
</div>
<?php
include_once('../../includes/offcanvas/transaction-offcanvas.php');
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>

<script>
    // Display current time
    function updateTime() {
        const now = new Date();
        document.getElementById("current-time").textContent =
            now.toLocaleString();

    }
    updateTime();
    setInterval(updateTime, 60000); // Update every minute
    setInterval(function() {
        location.reload();
    }, 60000);
</script>
</body>

</html>