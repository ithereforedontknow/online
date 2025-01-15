<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <h1 class="display-5 mb-3 fw-bold">Finished Transactions</h1>
        <div class="table-responsive">
            <table class="table table-hover table-light text-center small-font" id="finished-transactions-table">
                <thead>
                    <tr>
                        <th class="text-center" scope="col">To Reference</th>
                        <th class="text-center" scope="col">Kilos</th>
                        <th class="text-center" scope="col">Transfer Out Net Weight kg</th>
                        <th class="text-center" scope="col">Scrap kg</th>
                        <th class="text-center" scope="col">Remarks</th>
                        <th class="text-center" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody id="finished-transactions-list">

                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/transaction.js"></script>
</body>

</html>