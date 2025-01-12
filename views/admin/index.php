<?php
include_once('../../includes/header/header-admin.php');
$stmt = $conn->prepare("SELECT DISTINCT truck_type FROM Vehicle");
$stmt->execute();
$truckTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmtDemurrage = $conn->prepare("SELECT demurrage FROM demurrage ORDER BY updated_at DESC LIMIT 1");
$stmtDemurrage->execute();
$demurrage = $stmtDemurrage->fetch(PDO::FETCH_ASSOC);

$stmtTransaction = $conn->prepare("SELECT count(*) FROM transaction");
$stmtTransaction->execute();
$Transaction = $stmtTransaction->fetch(PDO::FETCH_ASSOC);

$activeTransactions = $conn->prepare("SELECT count(*) FROM transaction where transaction.status = 'ongoing' || transaction.status = 'queue' || transaction.status = 'departed' || transaction.status = 'standby'");
$activeTransactions->execute();
$Active = $activeTransactions->fetch(PDO::FETCH_ASSOC);

$vehiclesTransit = $conn->prepare("SELECT count(*) FROM transaction where transaction.status = 'departed'");
$vehiclesTransit->execute();
$Transit = $vehiclesTransit->fetch(PDO::FETCH_ASSOC);

$stmtQueue = $conn->prepare("SELECT count(*) FROM queue inner join transaction on queue.transaction_id = transaction.transaction_id where status = 'queue'");
$stmtQueue->execute();
$queue = $stmtQueue->fetch(PDO::FETCH_ASSOC);

$stmtUnloading = $conn->prepare("SELECT count(*) FROM unloading inner join transaction on unloading.transaction_id = transaction.transaction_id where status = 'unloading'");
$stmtUnloading->execute();
$unloading = $stmtUnloading->fetch(PDO::FETCH_ASSOC);
?>

<div class="content" id="content">
  <div class="container">
    <div class="d-flex align-items-center">
      <h1 class="display-5 me-auto mb-3 fw-bold">Dashboard</h1>
      <h5 class="text-muted"><small>You are logged in as <span class="fw-bold"><?php echo $_SESSION['userlevel']; ?></span>.</small></h5>
    </div>
    <!-- <div class="col">
        <div class="greetings-item p-4 shadow-sm bg-body rounded">
          <h4><i class="fas fa-hand greetings-icon"></i>
            Welcome, <?php echo $_SESSION['username']; ?>!
            <small class="text-muted">You are logged in as <span class="fw-bold">Admin</span>.
            </small>
        </div>
      </div> -->
    <div class="row">
      <div class="col">
        <div class="greetings-item p-4 shadow-sm bg-body rounded">
          <h5><i class="fas fa-peso-sign greetings-icon"></i><?php echo number_format($demurrage['demurrage'] / 3600, 4); ?> <small class="text-muted">current demurrage rate per second.</small>
        </div>
      </div>
      <div class="col">
        <div class="greetings-item p-4 shadow-sm bg-body rounded">
          <h5><i class="fas fa-asterisk greetings-icon"></i><?php echo $Transaction['count(*)']; ?> <small class="text-muted">total transactions</small>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="greetings-item p-4 shadow-sm bg-body rounded">
          <h5><i class="fas fa-id-card greetings-icon"></i>
            <?php echo $Active['count(*)'];  ?>
            <small class="text-muted">active transactions</small>
          </h5>
        </div>
      </div>
      <div class="col">
        <div class="greetings-item p-4 shadow-sm bg-body rounded">
          <h5><i class="fas fa-truck greetings-icon"></i>
            <?php echo $Transit['count(*)'];  ?>
            <small class="text-muted">vehicle in transit</small>
          </h5>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="greetings-item p-4 shadow-sm bg-body rounded">
          <h5><i class="fas fa-id-card greetings-icon"></i>
            <?php echo $queue['count(*)'];  ?>
            <small class="text-muted">vehicles on queue</small>
          </h5>
        </div>
      </div>
      <div class="col">
        <div class="greetings-item p-4 shadow-sm bg-body rounded">
          <h5><i class="fas fa-truck greetings-icon"></i>
            <?php echo $unloading['count(*)'];  ?>
            <small class="text-muted">total unloading</small>
          </h5>
        </div>
      </div>
    </div>
    <div class="greetings-item p-4 shadow-sm bg-body rounded">
      <div class="d-flex justify-content-end">
        <select id="transactionPeriodSelect" class="form-select w-25">
          <option value="today">Today</option>
          <option value="month">This Month</option>
          <option value="year" selected>This Year</option>
        </select>
      </div>
      <canvas id="transactionChart"></canvas>
    </div>
  </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
</body>

</html>