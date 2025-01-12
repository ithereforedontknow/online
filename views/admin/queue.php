<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">

    <div class="container">
        <h1 class="display-5 fw-bold mb-3">Queue Management</h1>
        <div class="row">

            <div class="col">
                <div class="p-4 shadow-sm bg-body rounded">
                    <h1 class="display-5 fw-bold text-center">Queue</h1>
                    <input type="hidden" id="search-queue" placeholder="Search by Plate Number" class="form-control">
                    <a class="btn btn-primary" href="view-queue.php">Present Screen</a>
                    <div class="row my-3">
                        <div class="queue-legend mb-3">
                            <span class="badge bg-primary" style="background-color: rgb(27, 54, 103) !important;">Priority</span>
                            <span class="badge bg-secondary" style="background-color: #6c757d !important;">Regular</span>
                        </div>
                        <div class="col">
                            <select id="ordinalFilter" class="form-select">
                                <option value="">Ordinal</option>
                                <option value="1st">1st</option>
                                <option value="2nd">2nd</option>
                                <option value="3rd">3rd</option>
                                <option value="3rd/1st">3rd/1st</option>
                            </select>
                        </div>
                        <div class="col">
                            <select id="shiftFilter" class="form-select">
                                <option value="">Shift</option>
                                <option value="day">Day</option>
                                <option value="night">Night</option>
                                <option value="day/night">Day/Night</option>
                            </select>
                        </div>
                        <div class="col">
                            <select id="scheduleFilter" class="form-select">
                                <option value="">Schedule</option>
                                <option value="6am-2pm">6am-2pm</option>
                                <option value="2pm-6am">2pm-6am</option>
                                <option value="6am-2pm/2pm-6am">6am-2pm/2pm-6am</option>
                            </select>
                        </div>
                        <div class="col">
                            <select id="lineFilter" class="form-select">
                                <option value="">Line</option>
                                <option value="Line 3">Line 3</option>
                                <option value="Line 4">Line 4</option>
                                <option value="Line 5">Line 5</option>
                                <option value="Line 6">Line 6</option>
                                <option value="GLAD WHSE">GLAD WHSE</option>
                                <option value="WHSE 2-BAY 2">WHSE 2-BAY 2</option>
                                <option value="WHSE 2-BAY 3">WHSE 2-BAY 3</option>
                            </select>
                        </div>
                    </div>

                    <table class="table table-hover text-center" id="queue-table">
                        <thead>
                            <tr>
                                <th class="text-center">Vehicle Pass</th>
                                <th class="text-center">Plate Number</th>
                                <th class="text-center">Order</th>
                                <th class="text-center">Shift</th>
                                <th class="text-center">Schedule</th>
                                <th class="text-center">Line</th>
                                <th class="text-center">...</th>
                            </tr>
                        </thead>
                        <tbody id="queue-list">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col">
                <div class="p-4 shadow-sm bg-body rounded text-center">
                    <h1 class="display-5 fw-bold">To Enter</h1>
                    <table class="table table-hover text-center" id="to-enter-table">
                        <thead>
                            <tr>
                                <th class="text-center">Plate Number</th>
                                <th class="text-center">Waiting Time</th>
                                <th class="text-center">...</th>
                            </tr>
                        </thead>
                        <tbody id="to-enter-list">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="viewQueueOffCanvas" aria-labelledby="viewQueueOffCanvas-label">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="viewQueueOffCanvas-label">Update <span class="fw-bold">queue</span> record / Add to <span class="fw-bold">unloading</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="edit-queue-form">
            <input type="hidden" id="edit-view-transaction-id" name="edit-view-transaction-id">
            <div class="form-floating mb-4">
                <select class="form-select" id="edit-view-transfer-in-line" name="edit-view-transfer-in-line">
                    <option value="1">Line 3</option>
                    <option value="2">Line 4</option>
                    <option value="3">Line 5</option>
                    <option value="4">Line 6</option>
                    <option value="5">Line 7</option>
                    <option value="6">GLAD WHSE</option>
                    <option value="7">WHSE 2-BAY 2</option>
                    <option value="8">WHSE 2-BAY 3</option>
                </select>
                <label for="edit-view-transfer-in-line" class="form-label">Transfer in Line #</label>
            </div>
            <div class="form-floating mb-4">
                <select class="form-select" id="edit-view-queue-ordinal" name="edit-view-queue-ordinal">
                    <option value="1st">1st</option>
                    <option value="2nd">2nd</option>
                    <option value="3rd">3rd</option>
                    <option value="3rd/1st">3rd/1st</option>
                </select>
                <label for="edit-view-queue-ordinal" class="form-label">Ordinal</label>
            </div>
            <div class="form-floating mb-4">
                <select class="form-select" id="edit-view-queue-shift" name="edit-view-queue-shift">
                    <option value="day">Day</option>
                    <option value="night">Night</option>
                    <option value="day/night">Day/Night</option>
                </select>
                <label for="edit-view-queue-shift" class="form-label">Shift</label>
            </div>
            <div class="form-floating mb-4">
                <select class="form-select" id="edit-view-queue-schedule" name="edit-view-queue-schedule">
                    <option value="6am-2pm">6 am to 2 pm</option>
                    <option value="2pm-6am">2 pm to 6 am</option>
                    <option value="6am-2pm/2pm-6am">6 am to 2 pm/2 pm to 6 am</option>
                </select>
                <label for="edit-view-queue-schedule" class="form-label">Schedule</label>
            </div>

            <div class="form-floating mb-4">
                <input type="text" class="form-control" id="edit-view-queue-number" name="edit-view-queue-number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                <label for="edit-view-queue-number" class="form-label">Vehicle Pass</label>
                <div class="invalid-feedback">Vehicle Pass already exist.</div>
            </div>
            <div class="form-floating">
                <select name="edit-view-queue-priority" class="form-select" id="edit-view-queue-priority">
                    <option value="1">High</option>
                    <option value="0">Low</option>
                </select>
                <label for="edit-view-queue-priority">Set Priority</label>
            </div>
        </form>
        <form id="add-unloading-form">
            <input type="hidden" id="add-unloading-transaction-id" name="add-unloading-transaction-id">
        </form>
    </div>
    <div class="offcanvas-footer d-flex justify-content-end p-3 border-top sticky-bottom">
        <button type="button" class="btn btn-dark me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary me-2" form="edit-queue-form">Save</button>
        <button type="submit" class="btn btn-primary" form="add-unloading-form">Enter to Unload</button>
    </div>
</div>
<div class="modal fade" id="enterQueueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Confirm Enter</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="confirm-enter-queue-form">
                    <input type="hidden" name="confirm-enter-queue-transaction-id" id="confirm-enter-queue-transaction-id">
                    <div class="form-floating">
                        <input type="datetime-local" class="form-control" name="confirm-enter-queue-time" id="confirm-enter-queue-time" required>
                        <label for="confirm-enter-queue-time">Time of Entry</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" form="confirm-enter-queue-form">Enter</button>
            </div>
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
        $("#edit-view-queue-shift").change(function() {
            const scheduleSelect = $("#edit-view-queue-schedule");
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
    });
</script>
</body>

</html>