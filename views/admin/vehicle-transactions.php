<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content bg-light" id="content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 me-auto fw-bold mb-0">Vehicle Transactions</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Vehicle Transactions</li>
                            </ol>
                        </nav>
                    </div>
                    <button class="btn btn-primary d-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                        <i class="fa-solid fa-plus me-2"></i>
                        New Transaction
                    </button>
                </div>
            </div>
        </div>

        <!-- Status Navigation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class=" ">
                    <div class="card-body p-0">
                        <nav class="">
                            <ul class="pagination">
                                <li class="page-item active">
                                    <a class="page-link pagination-nav" href="#">Departed</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link pagination-nav" href="#">Arrived</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link pagination-nav" href="#">Cancelled</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="row">
            <div class="col-12">
                <!-- Departed Table -->
                <div class="departed-table">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="departed-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">TO Reference</th>
                                            <th class="text-center">Hauler</th>
                                            <th class="text-center">Plate Number</th>
                                            <th class="text-center">Project</th>
                                            <th class="text-center">Origin</th>
                                            <th class="text-center">Arrival Time</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="departed-list">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Arrived Table -->
                <div class="arrived-table d-none">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="arrived-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">TO Reference</th>
                                            <th class="text-center">Hauler</th>
                                            <th class="text-center">Plate Number</th>
                                            <th class="text-center">Project</th>
                                            <th class="text-center">Origin</th>
                                            <th class="text-center">Arrival Time</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="arrived-list">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Table -->
                <div class="cancelled-table d-none">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="cancelled-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">TO Reference</th>
                                            <th class="text-center">GUIA</th>
                                            <th class="text-center">Hauler</th>
                                            <th class="text-center">Plate Number</th>
                                            <th class="text-center">Project</th>
                                            <th class="text-center">Origin</th>
                                            <th class="text-center">Arrival Time</th>
                                            <th class="text-center">Demurrage</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cancelled-list">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="divertTransactionModal" tabindex="-1" aria-labelledby="divertTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="divertTransactionModalLabel">Divert <span class="fw-bold">transaction</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="divert-transaction-form">
                    <input type="hidden" id="divert-transaction-transaction-id" name="divert-transaction-transaction-id">
                    <input type="hidden" id="divert-transaction-to-reference" name="divert-transaction-to-reference">
                    <div class="form-floating mb-4">
                        <select name="divert-transaction-branch" id="divert-transaction-branch" class="form-select" aria-label="Branch" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM origin");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['origin_id'] . '" data-code="' . $row['origin_code'] . '">' . $row['origin_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="divert-transaction-branch">Choose Branch</label>
                    </div>
                    <div class="form-floating mb-4">
                        <textarea name="divert-transaction-remarks" id="divert-transaction-remarks" class="form-control" placeholder="Remarks" required></textarea>
                        <label for="divert-transaction-remarks">Remarks</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" form="divert-transaction-form">Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addQueueModal" tabindex="-1" aria-labelledby="addQueueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="addQueueModalLabel">Add to <span class="fw-bold">queue</span> record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-queue-transaction">
                    <input type="hidden" id="add-queue-transaction-id" name="add-queue-transaction-id">
                    <div class="form-floating mb-4">
                        <select class="form-select" id="add-queue-transfer-in-line" name="add-queue-transfer-in-line">
                            <option value="1">Line 3</option>
                            <option value="2">Line 4</option>
                            <option value="3">Line 5</option>
                            <option value="4">Line 6</option>
                            <option value="5">Line 7</option>
                            <option value="6">GLAD WHSE</option>
                            <option value="7">WHSE 2-BAY 2</option>
                            <option value="8">WHSE 2-BAY 3</option>
                        </select>
                        <label for="add-queue-transfer-in-line" class="form-label">Transfer in Line #</label>
                    </div>
                    <div class="form-floating mb-4">
                        <select class="form-select" id="add-queue-ordinal" name="add-queue-ordinal">
                            <option value="1st">1st</option>
                            <option value="2nd">2nd</option>
                            <option value="3rd">3rd</option>
                            <option value="3rd/1st">3rd/1st</option>
                        </select>
                        <label for="add-queue-ordinal" class="form-label">Ordinal</label>
                    </div>
                    <div class="form-floating mb-4">
                        <select class="form-select" id="add-queue-shift" name="add-queue-shift">
                            <option value="day">Day</option>
                            <option value="night">Night</option>
                            <option value="day/night">Day/Night</option>
                        </select>
                        <label for="add-queue-shift" class="form-label">Shift</label>
                    </div>
                    <div class="form-floating mb-4">
                        <select class="form-select" id="add-queue-schedule" name="add-queue-schedule">
                            <option value="6am-2pm">6 am to 2 pm</option>
                            <option value="2pm-6am">2 pm to 6 am</option>
                            <option value="6am-2pm/2pm-6am">6 am to 2 pm/2 pm to 6 am</option>
                        </select>
                        <label for="add-queue-schedule" class="form-label">Schedule</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-queue-number" name="add-queue-number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        <label for="add-queue-number" class="form-label">Vehicle Pass</label>
                        <div class="invalid-feedback">Vehicle Pass already exist.</div>
                    </div>
                    <div class="form-floating">
                        <select name="add-queue-priority" class="form-select" id="add-queue-priority">
                            <option value="1">Priority</option>
                            <option value="0">Regular</option>
                        </select>
                        <label for="add-queue-priority">Set Priority</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-end p-3 border-top sticky-bottom">
                <button type="button" class="btn btn-dark me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" form="add-queue-transaction">Queue</button>
            </div>
        </div>
    </div>
</div>

<?php
include_once('../../includes/offcanvas/transaction-offcanvas.php');
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/transaction.js"></script>
<script>
    $("#add-queue-shift").change(function() {
        const scheduleSelect = $("#add-queue-schedule");
        switch ($(this).val()) {
            case "night":
                scheduleSelect.val("2pm-6am");
                break;
            case "day":
                scheduleSelect.val("6am-2pm");
                break;
            case "day/night":
                scheduleSelect.val("6am-2pm/2pm-6am");
                break;
        }
    });
</script>
</body>

</html>