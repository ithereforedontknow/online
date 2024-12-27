<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 mb-3 fw-bold">Ongoing</h1>
        <div class="table-responsive">
            <table class="table table-hover table-light text-center small-font" id="unloading-table">
                <thead>
                    <tr>
                        <th class="text-center" scope="col">To Reference</th>
                        <th class="text-center" scope="col">Time of Entry</th>
                        <th class="text-center" scope="col">Unloading Time Start</th>
                        <th class="text-center" scope="col">Unloading Time End</th>
                        <th class="text-center" scope="col">Time of Departure</th>
                        <th class="text-center" scope="col">Time in waiting area</th>
                    </tr>
                </thead>
                <tbody id="transaction-data">
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM Transaction
                        inner join unloading on transaction.transaction_id = unloading.transaction_id
                        WHERE transaction.status = 'ongoing' ORDER BY time_of_entry DESC");
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($result) > 0) {
                        foreach ($result as $row) {
                    ?>
                            <tr>
                                <td class='text-center'><?= $row['to_reference'] ?></td>
                                <td class='text-center'><?= date('m/d/Y h:i A', strtotime($row['time_of_entry'])) ?></td>
                                <td class="text-center">
                                    <?php
                                    if ($row['status'] === 'standby') {
                                    ?>
                                        <form class="unloading-start-form d-flex justify-content-center align-items-center">
                                            <input type="hidden" name="unloading-start-id" value="<?= $row['transaction_id'] ?>">
                                            <input type="datetime-local" class="form-control d-none" name="time-of-entry" style="width: auto;" value="<?= date('Y-m-d\TH:i', strtotime($row['time_of_entry'])) ?>" required>
                                            <input type="datetime-local" class="form-control" name="unloading-start-time" style="width: auto;" required>
                                            <button type="submit" class="btn btn-primary ms-2">Save</button>
                                        </form>
                                    <?php
                                    } else {
                                        echo date('m/d/Y h:i A', strtotime($row['unloading_time_start']));
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    if ($row['unloading_time_end'] === NULL) {
                                    ?>
                                        <form class="unloading-end-form d-flex justify-content-center align-items-center">
                                            <input type="hidden" name="unloading-end-id" value="<?= $row['transaction_id'] ?>">
                                            <input type="datetime-local" class="form-control d-none" name="unloading-time-start" style="width: auto;" value="<?= date('Y-m-d\TH:i', strtotime($row['unloading_time_start'])) ?>" required>
                                            <input type="datetime-local" class="form-control" name="unloading-end-time" style="width: auto;" required>
                                            <button type="submit" class="btn btn-primary ms-2">Save</button>
                                        </form>
                                    <?php
                                    } else {
                                        echo date('m/d/Y h:i A', strtotime($row['unloading_time_end']));
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    if ($row['time_of_departure'] === NULL) {
                                    ?>
                                        <form class="time-departure-form d-flex justify-content-center align-items-center">
                                            <input type="hidden" name="time-departure-id" value="<?= $row['transaction_id'] ?>">
                                            <input type="datetime-local" class="form-control d-none" name="unloading-time-end" style="width: auto;" value="<?= date('Y-m-d\TH:i', strtotime($row['unloading_time_end'])) ?>" required>
                                            <input type="datetime-local" class="form-control" name="time-departure-time" style="width: auto;" required>
                                            <button type="submit" class="btn btn-primary ms-2">Done</button>
                                        </form>
                                    <?php
                                    } else {
                                        echo date('m/d/Y h:i A', strtotime($row['time_of_departure']));
                                    }
                                    ?>
                                </td>
                                <td class='text-center'><?= $row['time_spent_waiting_area'] . " hours" ?></td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No Ongoing Transactions</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>