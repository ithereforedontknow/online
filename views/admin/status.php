<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <!-- Page Title -->
        <div class="d-flex align-items-center mb-4">
            <h1 class="display-5 me-auto fw-bold mb-0">Transaction Status</h1>
            <select id="statusFilter" class="form-select w-25">
                <option value="">All</option>
                <option value="departed">Departed</option>
                <option value="arrived">Arrived</option>
                <option value="queue">Queue</option>
                <option value="standby">Standby</option>
                <option value="ongoing">Ongoing</option>
                <option value="done">Done</option>
            </select>
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