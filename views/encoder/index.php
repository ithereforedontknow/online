<?php
include_once('../../includes/header/header-encoder.php');
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

<div class="content bg-light" id="content">
  <div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h1 class="display-5 me-auto fw-bold mb-0">Dashboard</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
              </ol>
            </nav>
          </div>
          <div class="text-end">
            <span class="badge bg-secondary">Logged in as <?php echo $_SESSION['userlevel']; ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
      <!-- Demurrage Rate Card -->
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <div class="bg-primary bg-opacity-10 p-3 rounded">
                  <i class="fas fa-peso-sign text-primary fa-fw fa-lg"></i>
                </div>
              </div>
              <div>
                <h6 class="card-title mb-1">Demurrage Rate</h6>
                <h3 class="mb-0"><?php echo number_format($demurrage['demurrage'] / 3600, 4); ?></h3>
                <small class="text-muted">per second</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Transactions Card -->
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <div class="bg-success bg-opacity-10 p-3 rounded">
                  <i class="fas fa-asterisk text-success fa-fw fa-lg"></i>
                </div>
              </div>
              <div>
                <h6 class="card-title mb-1">Total Transactions</h6>
                <h3 class="mb-0"><?php echo number_format($Transaction['count(*)']); ?></h3>
                <small class="text-muted">all time</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Transactions Card -->
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <div class="bg-info bg-opacity-10 p-3 rounded">
                  <i class="fas fa-id-card text-info fa-fw fa-lg"></i>
                </div>
              </div>
              <div>
                <h6 class="card-title mb-1">Active Transactions</h6>
                <h3 class="mb-0"><?php echo number_format($Active['count(*)']); ?></h3>
                <small class="text-muted">current</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Vehicles in Transit Card -->
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <div class="bg-warning bg-opacity-10 p-3 rounded">
                  <i class="fas fa-truck text-warning fa-fw fa-lg"></i>
                </div>
              </div>
              <div>
                <h6 class="card-title mb-1">Vehicles in Transit</h6>
                <h3 class="mb-0"><?php echo number_format($Transit['count(*)']); ?></h3>
                <small class="text-muted">on the road</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Queue and Unloading Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <div class="bg-secondary bg-opacity-10 p-3 rounded">
                  <i class="fas fa-clock text-secondary fa-fw fa-lg"></i>
                </div>
              </div>
              <div>
                <h6 class="card-title mb-1">Vehicles in Queue</h6>
                <h3 class="mb-0"><?php echo number_format($queue['count(*)']); ?></h3>
                <small class="text-muted">waiting</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <div class="bg-danger bg-opacity-10 p-3 rounded">
                  <i class="fas fa-box text-danger fa-fw fa-lg"></i>
                </div>
              </div>
              <div>
                <h6 class="card-title mb-1">Total Unloading</h6>
                <h3 class="mb-0"><?php echo number_format($unloading['count(*)']); ?></h3>
                <small class="text-muted">in progress</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts and Logs -->
    <div class="row g-3">
      <!-- Transaction Chart -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title mb-0">Transaction Analysis</h5>
              <select id="transactionPeriodSelect" class="form-select form-select-sm w-auto">
                <option value="today">Today</option>
                <option value="month">This Month</option>
                <option value="year" selected>This Year</option>
              </select>
            </div>
            <canvas id="transactionChart" height="350" width="500"></canvas>
          </div>
        </div>
      </div>

      <!-- Settings Log -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-3">Settings Update Log</h5>
            <table class="table" id="settingsLogTable">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Details</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $stmtLogs = $conn->prepare("SELECT created_at, details FROM settings_logs ORDER BY settings_log_id DESC");
                $stmtLogs->execute();
                $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);
                foreach ($logs as $log) {
                  echo '<tr>
                          <td>' . date('F j, Y, g:i a', strtotime($log['created_at'])) . '</td>
                          <td>' . $log['details'] . '</td>
                        </tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script>
  $(document).ready(() => {
    $("#settingsLogTable").DataTable({
      lengthChange: false,
      order: [
        [0, "desc"]
      ],
      pageLength: 5
    });
    var chart;

    function updateChart(period) {
      $.ajax({
        url: "../../api/main.php",
        type: "POST",
        data: {
          action: "get-transaction-count",
          period: period,
        },
        dataType: "json",
        success: function(data) {
          if (!Array.isArray(data)) {
            console.error("Error fetching data:", data);
            return;
          }

          var ctx = document.getElementById("transactionChart").getContext("2d");

          var labels = data.map(function(item) {
            return item.label;
          });

          var counts = data.map(function(item) {
            return item.transaction_count;
          });

          if (chart) {
            chart.destroy();
          }

          chart = new Chart(ctx, {
            type: "line",
            data: {
              labels: labels,
              datasets: [{
                label: "Number of Transactions",
                backgroundColor: "#2f364a",
                borderColor: "#2f364a",
                pointBackgroundColor: "#2f364a",
                pointBorderColor: "#fff",
                pointHoverBackgroundColor: "#fff",
                pointHoverBorderColor: "#2f364a",
                data: counts,
                tension: 0.4,
              }, ],
            },
            options: {

              responsive: true,
              plugins: {
                title: {
                  display: true,
                  text: "Transaction Count - " +
                    period.charAt(0).toUpperCase() +
                    period.slice(1),
                  font: {
                    size: 24,
                  },
                },
                legend: {
                  display: false,
                },
              },
              scales: {
                x: {
                  display: true,
                  title: {
                    display: true,
                    text: period === "today" ?
                      "Hour" : period === "month" ?
                      "Day" : "Month",
                    font: {
                      size: 18,
                    },
                  },
                  grid: {
                    color: "rgba(0, 0, 0, 0.1)",
                  },
                  ticks: {
                    font: {
                      size: 14,
                    },
                  },
                },
                y: {
                  display: true,
                  title: {
                    display: true,
                    text: "Number of Transactions",
                    font: {
                      size: 16,
                    },
                  },
                  beginAtZero: true,

                  ticks: {
                    font: {
                      size: 14,
                    },
                  },
                },
              },
            },
          });
        },
        error: function(xhr, status, error) {
          console.error("Error fetching data:", error);
        },
      });
    }

    // Initial chart load
    updateChart("year");

    // Add event listener for the select dropdown
    $("#transactionPeriodSelect").change(function() {
      var period = $(this).val();
      updateChart(period);
    });
  });
</script>
</body>

</html>