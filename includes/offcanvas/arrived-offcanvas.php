<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="viewArrivedVehicles" aria-labelledby="viewArrivedVehicles-label">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="viewArrivedVehicles-label">Arrived <span class="fw-bold">vehicles</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
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
    <div class="offcanvas-footer d-flex justify-content-end p-3 border-top sticky-bottom bg-white">
        <button type="button" class="btn btn-dark me-2" data-bs-dismiss="offcanvas">Cancel</button>
    </div>
</div>