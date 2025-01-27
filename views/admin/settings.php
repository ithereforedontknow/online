<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 me-auto fw-bold mb-0">Settings</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Settings</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <div>
            <div class="row mb-4 mt-4">
                <h4 class="fw-bold mb-5">Transaction Settings</h4>
                <div class="col-2 text-center" id="haulers">
                    <a class="text-decoration-none" href="settings-hauler.php" style="color: #1b3667">
                        <i class="fa-solid fa-warehouse fa-2xl"></i>
                        <p class="mt-3">Haulers</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="vehicles">
                    <a class="text-decoration-none" href="settings-vehicle.php" style="color: #1b3667">
                        <i class="fa-solid fa-truck fa-2xl"></i>
                        <p class="mt-3">Vehicles</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="drivers">
                    <a class="text-decoration-none" href="settings-driver.php" style="color: #1b3667">
                        <i class="fa-regular fa-id-card fa-2xl"></i>
                        <p class="mt-3">Drivers & Helpers</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="project">
                    <a class="text-decoration-none" href="settings-project.php" style="color: #1b3667">
                        <i class="fa-solid fa-sheet-plastic fa-2xl"></i>
                        <p class="mt-3">Projects</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="origin">
                    <a class="text-decoration-none" href="settings-origin.php" style="color: #1b3667">
                        <i class="fa-solid fa-location-dot fa-2xl"></i>
                        <p class="mt-3">Origin</p>
                    </a>
                </div>
                <div class="col-2 text-center" id="demurrage">
                    <a class="text-decoration-none" href="settings-demurrage.php" style="color: #1b3667">
                        <i class="fa-solid fa-dollar-sign fa-2xl"></i>
                        <p class="mt-3">Demurrage</p>
                    </a>
                </div>
            </div>
            <hr>
            <div class="row mb-4 mt-4">
                <h4 class="fw-bold mb-5">System</h4>
                <div class="col-2 text-center">
                    <a class="text-decoration-none" href="#backupModal" data-bs-toggle="modal" style="color: #1b3667">
                        <i class="fa-solid fa-database fa-2xl"></i>
                        <p class="mt-3">Backup & Restore</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="backupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Backup & Restore</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <button type="button" class="btn btn-primary w-25" id="backup-btn">New Backup</button>
                <hr>
                <form id="restore-form">
                    <input type="file" class="form-control" id="restore-file" name="restore-file" accept=".sql">
                    <button type="button" class="btn btn-secondary w-25 mt-2" id="restore-button" disabled>Restore</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script src="../../assets/js/settings.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const backupBtn = document.getElementById('backup-btn');
        const restoreFileInput = document.getElementById('restore-file');
        const restoreForm = document.getElementById('restore-form');
        const restoreButton = document.getElementById('restore-button');

        // Backup Function
        backupBtn.addEventListener('click', () => {
            window.location.href = '../../api/backup.php?action=backup';
        });

        // Enable restore button when file is selected
        restoreFileInput.addEventListener('change', () => {
            restoreButton.disabled = !restoreFileInput.files.length;
        });

        // Restore Confirmation Button
        restoreButton.addEventListener('click', async () => {
            const file = restoreFileInput.files[0];

            if (!file) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No File Selected',
                    text: 'Please select a SQL file to restore.'
                });
                return;
            }

            if (!file.name.toLowerCase().endsWith('.sql')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: 'Please select a valid SQL file.'
                });
                return;
            }

            // Confirmation dialog
            Swal.fire({
                icon: 'warning',
                title: 'Confirm Database Restoration',
                text: 'This will replace all existing data. Are you sure?',
                showCancelButton: true,
                confirmButtonColor: '#1f3a69',
                cancelButtonColor: '#5c636a',
                confirmButtonText: 'Yes, restore it!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('restore-file', file);

                    try {
                        const response = await fetch('../../api/restore.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Restoration Successful',
                                text: 'Database has been restored successfully!',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            restoreFileInput.value = '';
                            restoreButton.disabled = true;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Restoration Failed',
                                text: result.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }

                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to restore database. Please try again.'
                        });
                        console.error('Restore error:', error);
                    }
                }
            });
        });
    });
</script>
</body>

</html>