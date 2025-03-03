<?php
include_once('../../includes/header/header-admin.php');
$departed = $conn->prepare("SELECT count(*) FROM transaction where transaction.status = 'departed'");
$departed->execute();
$Departed = $departed->fetch(PDO::FETCH_ASSOC);

$arrived = $conn->prepare("SELECT count(*) FROM transaction where transaction.status = 'arrived'");
$arrived->execute();
$Arrived = $arrived->fetch(PDO::FETCH_ASSOC);

$queue = $conn->prepare("SELECT count(*) FROM queue inner join transaction on queue.transaction_id = transaction.transaction_id where status = 'queue' OR status = 'standby' OR status = 'standby - sms sent'");
$queue->execute();
$Queue = $queue->fetch(PDO::FETCH_ASSOC);

$ongoing = $conn->prepare("SELECT count(*) FROM transaction where transaction.status = 'ongoing'");
$ongoing->execute();
$Ongoing = $ongoing->fetch(PDO::FETCH_ASSOC);
?>
<div class="content" id="content">
    <div class="container-fluid">

        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 me-auto fw-bold mb-0">Transaction In Progress</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Status</li>
                            </ol>
                        </nav>
                    </div>
                    <select id="statusFilter" class="form-select w-25">
                        <option value="">All</option>
                        <option value="departed">Departed</option>
                        <option value="arrived">Arrived</option>
                        <option value="queue">Queue</option>
                        <option value="standby">Standby</option>
                        <option value="ongoing">Ongoing</option>
                    </select>
                </div>
            </div>
        </div>
        <!-- Summary Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded bg-info bg-opacity-10 p-3 me-3">
                            <i class="fa-solid fa-arrow-right fa-flip text-info fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-title text-muted mb-1">Departed</h6>
                            <h3 class="mb-0 fw-bold" id="departedCount"><?= $Departed['count(*)'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded bg-success bg-opacity-10 p-3 me-3">
                            <i class="fa-solid fa-check-circle fa-fade text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-title text-muted mb-1">Arrived</h6>
                            <h3 class="mb-0 fw-bold" id="ongoingCount"><?= $Arrived['count(*)'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded bg-warning bg-opacity-10 p-3 me-3">
                            <i class="fa-solid fa-hourglass-half fa-bounce text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-title text-muted mb-1">Waiting</h6>
                            <h3 class="mb-0 fw-bold" id="waitingCount"><?= $Queue['count(*)'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded bg-primary bg-opacity-10 p-3 me-3">
                            <i class="fa-solid fa-spinner fa-spin text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-title text-muted mb-1">Ongoing</h6>
                            <h3 class="mb-0 fw-bold" id="totalVehicles"><?= $Ongoing['count(*)'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>


        </div>
        <!-- Transaction Progress Section -->
        <div class="row" id="transaction-column">
            <!-- Dynamic content will be injected here -->
        </div>

    </div>
</div>

<?php
include_once('../../includes/offcanvas/user-offcanvas.php');
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/transaction.js"></script>
<script>
    // Example of animating progress bars dynamically
    document.addEventListener('DOMContentLoaded', () => {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const value = bar.getAttribute('aria-valuenow');
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = `${value}%`;
            }, 500);
        });
    });
</script>
</body>

</html>