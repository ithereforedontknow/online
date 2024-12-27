<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">

    <div class="container">
        <h1 class="display-5 fw-bold mb-3">Queue Management</h1>
        <div class="row">
            <!-- <div class="col">
                <div class="p-4 shadow-sm bg-body rounded text-center">
                    <h1 class="display-5 fw-bold">Arrived</h1>
                    <table class="table table-hover text-center">
                        <thead>
                            <tr>
                                <th>Plate Number</th>
                                <th>Arrival Time</th>
                                <th>...</th>
                            </tr>
                        </thead>
                        <tbody id="arrived-list">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM transaction inner join vehicle on transaction.vehicle_id = vehicle.vehicle_id inner join arrival on transaction.transaction_id = arrival.transaction_id where transaction.status = 'arrived' order by arrival_time desc");
                            $stmt->execute();
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (count($result) > 0) {
                                foreach ($result as $row) {
                            ?>
                                    <tr onclick="addToQueue(<?= $row['transaction_id'] ?>)" style="cursor: pointer;">
                                        <td class="text-center" scope="row"><?= $row['plate_number'] ?></td>
                                        <td class="text-center" scope="row"><?= date('F j, Y, g:i a', strtotime($row['arrival_time'])) ?></td>
                                        <td class="text-center" scope="row"><i class="fa-solid fa-arrow-right"></i></td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>No Arrived Transactions</td></tr>";
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
            </div> -->
            <div class="col">
                <div class="p-4 shadow-sm bg-body rounded">
                    <h1 class="display-5 fw-bold text-center">Queue</h1>
                    <input type="hidden" id="search-queue" placeholder="Search by Plate Number" class="form-control">
                    <a class="btn btn-primary" href="view-queue.php">Present Screen</a>
                    <div class="row my-3">
                        <div class="queue-legend mb-3">
                            <span class="badge bg-primary">Priority</span>
                            <span class="badge bg-secondary">Regular</span>
                        </div>
                        <div class="col">
                            <select id="ordinalFilter" class="form-select">
                                <option value="">Ordinal</option>
                                <option value="1st">1st</option>
                                <option value="2nd">2nd</option>
                                <option value="3rd">3rd</option>
                                <option value="3rd/1st">3rd/1st</option>
                            </select>
                        </div>
                        <div class="col">
                            <select id="shiftFilter" class="form-select">
                                <option value="">Shift</option>
                                <option value="day">Day</option>
                                <option value="night">Night</option>
                                <option value="day/night">Day/Night</option>
                            </select>
                        </div>
                        <div class="col">
                            <select id="scheduleFilter" class="form-select">
                                <option value="">Schedule</option>
                                <option value="6am-2pm">6am-2pm</option>
                                <option value="2pm-6am">2pm-6am</option>
                                <option value="6am-2pm/2pm-6am">6am-2pm/2pm-6am</option>
                            </select>
                        </div>
                        <div class="col">
                            <select id="lineFilter" class="form-select">
                                <option value="">Line</option>
                                <option value="Line 3">Line 3</option>
                                <option value="Line 4">Line 4</option>
                                <option value="Line 5">Line 5</option>
                                <option value="Line 6">Line 6</option>
                                <option value="GLAD WHSE">GLAD WHSE</option>
                                <option value="WHSE 2-BAY 2">WHSE 2-BAY 2</option>
                                <option value="WHSE 2-BAY 3">WHSE 2-BAY 3</option>
                            </select>
                        </div>
                    </div>

                    <table class="table table-hover text-center" id="queue-table">
                        <thead>
                            <tr>
                                <th class="text-center">Vehicle Pass</th>
                                <th class="text-center">Plate Number</th>
                                <th class="text-center">Order</th>
                                <th class="text-center">Shift</th>
                                <th class="text-center">Schedule</th>
                                <th class="text-center">Line</th>
                                <th class="text-center">...</th>
                            </tr>
                        </thead>
                        <tbody id="queue-list">
                            <?php
                            $sql = "SELECT * FROM transaction 
                                    INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id 
                                    INNER JOIN queue ON transaction.transaction_id = queue.transaction_id 
                                    WHERE transaction.status = 'queue' 
                                    ORDER BY priority DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (count($result) > 0) {
                                foreach ($result as $row) {
                                    $rowColor = ($row['priority'] == 1) ? '#1b3667' : 'table-secondary';
                            ?>
                                    <tr onclick="viewQueue(<?= $row['transaction_id'] ?>)" style="cursor: pointer; background-color: <?= $rowColor ?>;">
                                    <tr onclick="viewQueue(<?= $row['transaction_id'] ?>)" style="cursor: pointer;">
                                        <td class="text-center" scope="row"><?= $row['queue_number'] ?></td>
                                        <td class="text-center" scope="row"><?= $row['plate_number'] ?></td>
                                        <td class="text-center" scope="row"><?= $row['ordinal'] ?></td>
                                        <td class="text-center" scope="row"><?= $row['shift'] ?></td>
                                        <td class="text-center" scope="row"><?= $row['schedule'] ?></td>
                                        <td class="text-center" scope="row"><?= $row['transfer_in_line'] ?></td>
                                        <td class="text-center" scope="row"><i class="fa-solid fa-arrow-right"></i></td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>No Queued Transactions</td></tr>";
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col">
                <div class="p-4 shadow-sm bg-body rounded text-center">
                    <h1 class="display-5 fw-bold">To Enter</h1>
                    <table class="table table-hover text-center">
                        <thead>
                            <tr>
                                <th>Plate Number</th>
                                <th>Demurrage</th>
                                <th>...</th>
                            </tr>
                        </thead>
                        <tbody id="to-enter-list">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM transaction inner join vehicle on transaction.vehicle_id = vehicle.vehicle_id inner join arrival on transaction.transaction_id = arrival.transaction_id where transaction.status = 'standby' order by arrival_time desc");
                            $stmt->execute();
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (count($result) > 0) {
                                foreach ($result as $row) {
                            ?>
                                    <tr>
                                        <td class="text-center" scope="row"><?= $row['plate_number'] ?></td>
                                        <td class="text-center" scope="row">&#8369; <?= number_format($row['demurrage'], 2) ?></td>
                                        <td class="text-center" scope="row"><i class="fa-solid fa-arrow-right"></i></td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>No To Enter Transactions</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>