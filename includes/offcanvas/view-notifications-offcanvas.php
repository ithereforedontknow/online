<div class="offcanvas offcanvas-end" tabindex="-1" id="viewNotificationsOffcanvas" aria-labelledby="viewNotificationsOffcanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="viewNotificationsOffcanvas">All <span class="fw-bold">Notifications</span></h5>

        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php
        $stmt = $conn->prepare("SELECT transaction.transaction_id, transaction.to_reference, transaction.created_at, origin.origin_name 
FROM transaction 
RIGHT JOIN origin ON transaction.origin_id = origin.origin_id 
WHERE transaction.created_at >= :currentTime AND status = 'departed' 
ORDER BY transaction_id DESC");
        $stmt->bindParam(':currentTime', $currentTime);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            while ($transaction = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
                <div class="notification-item">
                    <h5><i class="fas fa-truck notification-icon"></i><?= $transaction['to_reference'] ?> has departed from <?= $transaction['origin_name'] ?></h5>
                    <small class="text-muted">Transaction ID: <?= $transaction['transaction_id'] ?></small>
                    <small class="text-muted float-end"><?= date('F j, Y, g:i a', strtotime($transaction['created_at'])) ?></small>
                </div>
            <?php
            }
        } else {
            ?>
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle mr-2"></i> No notifications at this time.
            </div>
        <?php
        }
        ?>
    </div>
</div>