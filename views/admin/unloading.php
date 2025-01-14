<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 mb-3 fw-bold">Ongoing</h1>
        <div class="table-responsive">
            <table class="table table-hover table-light text-center small-font" id="unloading-table">
                <thead>
                    <tr>
                        <th class="text-center" scope="col">To Reference</th>
                        <th class="text-center" scope="col">Time of Entry</th>
                        <th class="text-center" scope="col">Unloading Time Start</th>
                        <th class="text-center" scope="col">Unloading Time End</th>
                        <th class="text-center" scope="col">Time of Departure</th>
                        <th class="text-center" scope="col">Demurrage</th>
                        <th class="text-center" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody id="unloading-list">

                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="editUnloadingOffCanvas" aria-labelledby="editUnloadingOffCanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="editUnloadingOffCanvasLabel">Edit <span class="fw-bold">unloading</span></h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="edit-unloading-table-form">
            <input type="hidden" name="unloading-table-id" id="unloading-table-id">
            <div class="form-floating mb-4">
                <input type="datetime-local" class="form-control" id="unloading-table-time-entry">
                <label for="unloading-table-time-entry" class="form-label">Time of Entry</label>
            </div>
            <div class="form-floating mb-4">
                <input type="datetime-local" class="form-control" id="unloading-table-unloading-start">
                <label for="unloading-table-unloading-start" class="form-label">Unloading Time Start</label>
            </div>
            <div class="form-floating mb-4">
                <input type="datetime-local" class="form-control" id="unloading-table-unloading-end">
                <label for="unloading-table-unloading-end" class="form-label">Unloading Time End</label>
            </div>
            <div class="form-floating mb-4">
                <input type="datetime-local" class="form-control" id="unloading-table-departure">
                <label for="unloading-table-departure" class="form-label">Time of Departure</label>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer d-flex justify-content-end p-3 border-top sticky-bottom">
        <button type="button" class="btn btn-dark me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" form="edit-unloading-table-form" class="btn btn-primary">Save</button>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/unloading.js"></script>
</body>

</html>