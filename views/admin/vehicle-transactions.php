<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <div class="d-flex align-items-center">
            <h1 class="display-5 me-auto mb-3 fw-bold">Vehicle Transactions</h1>
            <button class="btn btn-primary ms-2" data-bs-toggle="offcanvas" data-bs-target="#addTransactionOffcanvas">
                <i class="fa-solid fa-plus fa-lg me-2" style="color: #ffffff;"></i> New Transaction
            </button>
        </div>
        <!-- Pagination Navigation -->
        <nav class="">
            <ul class="pagination">
                <li class="page-item active" data-table="departed">
                    <a class="page-link pagination-nav" href="#" onclick="showTable('departed')">Departed</a>
                </li>
                <li class="page-item" data-table="arrived">
                    <a class="page-link pagination-nav" href="#" onclick="showTable('arrived')">Arrived</a>
                </li>
                <li class="page-item" data-table="cancelled">
                    <a class="page-link pagination-nav" href="#" onclick="showTable('cancelled')">Cancelled</a>
                </li>
            </ul>
        </nav>

        <!-- Departed Table -->
        <table class="table table-hover text-center table-light" id="departed-table">
            <thead>
                <tr>
                    <th class="text-center" scope="col">TO Reference</th>
                    <th class="text-center" scope="col">GUIA</th>
                    <th class="text-center" scope="col">Hauler</th>
                    <th class="text-center" scope="col">Plate Number</th>
                    <th class="text-center" scope="col">Project</th>
                    <th class="text-center" scope="col">Origin</th>
                    <th class="text-center" scope="col">Arrival Time</th>
                    <th class="text-center" scope="col">Action</th>
                </tr>
            </thead>
            <tbody id="departed-list">

            </tbody>
        </table>

        <!-- Arrived Table -->
        <table class="table table-hover text-center table-light d-none" id="arrived-table">
            <thead>
                <tr>
                    <th class="text-center" scope="col">TO Reference</th>
                    <th class="text-center" scope="col">GUIA</th>
                    <th class="text-center" scope="col">Hauler</th>
                    <th class="text-center" scope="col">Plate Number</th>
                    <th class="text-center" scope="col">Project</th>
                    <th class="text-center" scope="col">Origin</th>
                    <th class="text-center" scope="col">Arrival Time</th>
                    <th class="text-center" scope="col">Action</th>
                </tr>
            </thead>
            <tbody id="arrived-list">

            </tbody>
        </table>

        <!-- Cancelled Table -->
        <table class="table table-hover text-center table-light d-none" id="cancelled-table">
            <thead>
                <tr>
                    <th class="text-center" scope="col">TO Reference</th>
                    <th class="text-center" scope="col">GUIA</th>
                    <th class="text-center" scope="col">Hauler</th>
                    <th class="text-center" scope="col">Plate Number</th>
                    <th class="text-center" scope="col">Project</th>
                    <th class="text-center" scope="col">Origin</th>
                    <th class="text-center" scope="col">Arrival Time</th>
                    <th class="text-center" scope="col">Action</th>
                </tr>
            </thead>
            <tbody id="cancelled-list">

            </tbody>
        </table>
    </div>
</div>
<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="addQueueOffcanvas" aria-labelledby="addQueueOffcanvas-label">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="addQueueOffcanvas-label">Add to <span class="fw-bold">queue</span> record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
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
                    <option value="1">High</option>
                    <option value="0">Low</option>
                </select>
                <label for="add-queue-priority">Set Priority</label>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer d-flex justify-content-end p-3 border-top sticky-bottom">
        <button type="button" class="btn btn-dark me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary" form="add-queue-transaction">Queue</button>
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