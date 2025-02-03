<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 me-auto fw-bold mb-0">Unloading</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Vehicle Unloading</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="departed-table">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover text-center small-font" id="unloading-table">
                            <thead>
                                <tr>
                                    <th class="text-center" scope="col">Plate Number</th>
                                    <th class="text-center" scope="col">Time of Entry</th>
                                    <th class="text-center" scope="col">Unloading Time Start</th>
                                    <th class="text-center" scope="col">Unloading Time End</th>
                                    <th class="text-center" scope="col">Time of Departure</th>
                                    <th class="text-center" scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody id="unloading-list">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editUnloadingModal" tabindex="-1" aria-labelledby="editUnloadingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="editUnloadingModalLabel">Edit <span class="fw-bold">unloading</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                        <div class="form-floating">
                            <input type="datetime-local" class="form-control" id="unloading-table-departure">
                            <label for="unloading-table-departure" class="form-label">Time of Departure</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-end p-3 border-top sticky-bottom">
                    <button type="button" class="btn btn-dark me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="edit-unloading-table-form" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/unloading.js"></script>
</body>

</html>