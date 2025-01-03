<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 mb-3 fw-bold">Departed</h1>
        <div class="row">
            <div class="col">
                <div class="greetings-item p-4 shadow-sm bg-body rounded">
                    <h5>Total En Route</h5>
                    <h2 id="total-enroute">0</h2>
                </div>
            </div>
            <div class="col">
                <div class="greetings-item p-4 shadow-sm bg-body rounded">
                    <h5>Expected Today</h5>
                    <h2 id="expected-today">0</h2>
                </div>
            </div>
        </div>
        <!-- Add more stats cards -->
        <div class="d-flex justify-content-end">
            <div style="width: 200px; flex-shrink: 0;">
                <select class="form-select" id="status-filter">
                    <option value="departed" selected>Departed</option>
                    <option value="arrived">Arrived</option>
                    <option value="cancelled">Cancelled</option>
                </select>

            </div>

            <button class="btn btn-primary ms-2" data-bs-toggle="offcanvas" data-bs-target="#addTransactionOffcanvas">
                <i class="fa-solid fa-plus fa-lg me-2" style="color: #ffffff;"></i> New Transaction
            </button>
        </div>
        <div class="table-responsive">
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
                    <!-- Data will be dynamically populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include_once('../../includes/offcanvas/transaction-offcanvas.php');
include_once('../../includes/footer/footer-admin.php');
?>