<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 mb-3 fw-bold">Finished Transactions</h1>
        <div class="table-responsive">
            <table class="table table-hover table-light text-center small-font" id="finished-transactions">
                <thead>
                    <tr>
                        <th class="text-center" scope="col">To Reference</th>
                        <th class="text-center" scope="col" style="width:15%">Transfer Out Net Weight kg</th>
                        <th class="text-center" scope="col">Scrap kg</th>
                        <th class="text-center" scope="col">Remarks</th>
                        <th class="text-center" scope="col">Action</th>
                        <th class="text-center" scope="col">Diverted</th>
                    </tr>
                </thead>
                <tbody id="transaction-data">
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM Transaction
                                INNER JOIN unloading ON transaction.transaction_id = unloading.transaction_id
                                INNER JOIN arrival ON transaction.transaction_id = arrival.transaction_id
                                WHERE transaction.status = 'done' OR transaction.status = 'diverted' ORDER BY unloading.time_of_departure DESC");
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($result) > 0) {
                        foreach ($result as $row) {
                    ?>
                            <tr>
                                <td class='text-center'><?= htmlspecialchars($row['to_reference']) ?></td>
                                <td class='text-center'><?= date('F j, Y g:i a', strtotime($row['unloading_time_end'])) ?></td>
                                <td class='text-center'><?= date('F j, Y g:i a', strtotime($row['time_of_departure'])) ?></td>
                                <td class='text-center'>&#8369; <?= number_format($row['demurrage'], 2) ?></td>
                                <td class='text-center'><?= $row['kilos'] ?></td>
                                <td class='text-center'>
                                    <?php
                                    if ($row['transfer_out_kilos'] == null) {
                                    ?>
                                        <form id="insert-transfer-out-scrap-remarks-<?= htmlspecialchars($row['transaction_id']) ?>">
                                            <input type="hidden" name="transaction_id" value="<?= htmlspecialchars($row['transaction_id']) ?>">
                                            <input type="text" class="form-control" name="transfer_out_kilos" required>
                                        </form>
                                    <?php
                                    } else {
                                        echo $row['transfer_out_kilos'];
                                    }
                                    ?>
                                </td>
                                <td class='text-center'>
                                    <?php
                                    if ($row['scrap'] == null) {
                                    ?>
                                        <input type="text" form="insert-transfer-out-scrap-remarks-<?= htmlspecialchars($row['transaction_id']) ?>"
                                            class="form-control" name="scrap" required>
                                    <?php
                                    } else {
                                        echo $row['scrap'];
                                    }
                                    ?>
                                </td>
                                <td class='text-center'>
                                    <?php
                                    if ($row['remarks'] == null) {
                                    ?>
                                        <input type="text" form="insert-transfer-out-scrap-remarks-<?= htmlspecialchars($row['transaction_id']) ?>"
                                            class="form-control" name="remarks" required>
                                    <?php
                                    } else {
                                        echo $row['remarks'];
                                    }
                                    ?>
                                </td>
                                <td class='text-center'>
                                    <button type="submit" form="insert-transfer-out-scrap-remarks-<?= htmlspecialchars($row['transaction_id']) ?>"
                                        class="btn btn-primary">Save</button>
                                </td>
                                <td class='text-center'>
                                    <?php
                                    if ($row['status'] !== 'diverted') {
                                    ?>
                                        <button class="btn btn-primary" onclick="updateStatus('diverted', '<?= htmlspecialchars($row['transaction_id']) ?>')">Yes</button>
                                    <?php
                                    } else {
                                    ?>
                                        <button class="btn btn-secondary" onclick="updateStatus('done', '<?= htmlspecialchars($row['transaction_id']) ?>')">No</button>
                                    <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="10" class="text-center">No Finished Transactions</td>
                        </tr>
                    <?php
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
</body>

</html>