<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">

    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 me-auto fw-bold mb-0">Audit Log</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active"> <a href="settings.php">Utilities</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Audit Log</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4">
            <table class="table" id="audit-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Action</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT username, action, timestamp FROM user_logs ORDER BY user_log_id DESC");
                    $stmt->execute();
                    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>

<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/queue.js"></script>
<script>
    $(document).ready(function() {
        $("#audit-table").DataTable({
            lengthChange: false,
            order: [
                [0, "desc"]
            ],
            pageLength: 10,
            ordering: false
        });
    });
</script>

</body>

</html>