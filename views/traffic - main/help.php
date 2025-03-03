<?php
include_once('../../includes/header/header-main.php');
?>
<div class="content" id="content">
    <div class="container-fluid">
        <input type="text" class="form-control w-25 mb-3" name="search" id="search" placeholder="Search">
        <a href="#" target="_blank" class="btn btn-primary" id="exportBtn">Export to PDF</a>
        <!-- <h1 class="mb-4 text-center">Inhouse Vehicle Management System User Manual</h1> -->
        <div id="manualContent">
            <img src="../../assets/img/ULPI_BLUE (1).png" class="mx-auto d-block" alt="" style="width: 500px;">


            <!-- Add other sections here in the same format -->

            <h2>1. Transactions</h2>
            <p>This section manages transactions for vehicles that have departed and newly arrived.</p>
            <h4>Features:</h4>
            <ul>
                <li>View all departed/arrived/cancelled transactions</li>
                <li>Add new transactions</li>
                <li>Edit transactions</li>
                <li>Cancel transactions</li>
                <li>Search transactions</li>
            </ul>
            <h4>How to use:</h4>
            <ol>
                <li><strong>View Departed Transactions</strong>: All transactions are displayed in a table.</li>
                <img src="../../assets/img/screenshot/vehicletransaction.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>View Arrived Transactions</strong>: All transactions are displayed in a table.</li>
                <img src="../../assets/img/screenshot/arrivedtransaction.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>View Cancelled Transactions</strong>: All transactions are displayed in a table.</li>
                <img src="../../assets/img/screenshot/cancelled.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Add New Transaction</strong>: Click <button class="btn btn-primary">New Transaction</button> fill in the form, and click <button class="btn btn-primary">Add to arrived</button></li>
                <img src="../../assets/img/screenshot/addtransaction.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Edit Transaction</strong>: Click <button class="btn btn-primary">Edit</button> next to the transaction to update information.</li>
                <li><strong>Cancel Transaction</strong>: Click <button class="btn btn-secondary"><i class="fa-solid fa-cancel"></i></button> and confirm.</li>
                <li><strong>Divert Transaction</strong>: Click <button class="btn btn-secondary"><i class="fa-solid fa-cancel"></i></button> and confirm.</li>
                <li><strong>Search Transactions</strong>: Use the search bar to find specific transactions.</li>
                <li><strong>Queue Transactions</strong>: Click <button class="btn btn-primary">Queue</button> to add the transaction to the queue.</li>
                <img src="../../assets/img/screenshot/addqueue.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">

            </ol>

            <h2>2. Queue Management</h2>
            <p>The Queue Management section assists users in organizing and managing the vehicle queue.</p>

            <h4>Features:</h4>
            <ul>
                <li>Manage the current queue</li>
                <li>Filter the queue</li>
                <li>Send SMS</li>
            </ul>

            <h4>How to Use:</h4>
            <ol>
                <li><strong>View Queued Vehicles:</strong></li>
                <li><strong>Manage Queue:</strong>
                    <ul>
                        <li>Use filter options such as Ordinal, Shift, Schedule, and Line to refine the queue.</li>
                    </ul>
                </li>
                <img src="../../assets/img/screenshot/queue.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>View Queue Details:</strong> Click on a vehicle in the queue to view more information.</li>
                <img src="../../assets/img/screenshot/editqueue.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>View Queue on TV Display:</strong> Click the <button class="btn btn-primary">Present Screen</button> button for a large screen display format of the queue.</li>
                <li><strong>Send SMS and Enter vehicle:</strong> Click the <button class="btn btn-primary"><i class="fa-solid fa-sms"></i></button> button to send SMS and <button class="btn btn-primary">Set</button> to Set time of entry.</li>
                <img src="../../assets/img/screenshot/to enter.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">

            </ol>
            <h2>3. Unloading Vehicles</h2>
            <p>The Unloading Vehicles section allows users to manage and track vehicles currently in the unloading process.</p>
            <h4>Features:</h4>
            <ul>
                <li>View all vehicles in the unloading process</li>
                <li>Update unloading start and end times, and time of departure</li>
            </ul>
            <h4>How to use:</h4>
            <ol>
                <li><strong>View Unloading Vehicles</strong>: All vehicles in the unloading process are displayed in a table.</li>
                <img src="../../assets/img/screenshot/viewunloading.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Add Unloading Start Time, End Time, and Time of departure</strong>: Click the <button class="btn btn-primary">Set Time</button> button</li>
                <img src="../../assets/img/screenshot/unloading.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Update Unloading Start Time, End Time, and Time of departure</strong>: Click the <button class="btn btn-primary">Edit</button> button</li>
                <img src="../../assets/img/screenshot/editunloading.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Search Transactions</strong>: Use the search bar to find specific transactions.</li>
            </ol>

            <h2>4. Finished Transactions</h2>
            <p>The Finished Transactions section displays all completed transactions.</p>
            <h4>Features:</h4>
            <ul>
                <li>View all finished transactions</li>
                <li>Update transfer out net weight</li>
                <li>Record scrap weight</li>
                <li>Add remarks</li>
                <li>Update finished transaction</li>
                <li>Search transactions</li>
            </ul>
            <h4>How to use:</h4>
            <ol>
                <li><strong>View Finished Transactions</strong>: Completed transactions are listed in a table.</li>
                <img src="../../assets/img/screenshot/finished.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Record Transfer Out Net Weight and Scrap Weight then Add Remarks</strong>: Enter the transfer out net weight, scrap, and remarks and click <button class="btn btn-primary">Save</button></li>
                <li><strong>Search Transactions</strong>: Use the search bar to find finished transactions.</li>
                <li><strong>Update finished transaction</strong>: Click <button class="btn btn-primary">Edit</button> then enter the required fields</li>
                <img src="../../assets/img/screenshot/editfinished.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">

            </ol>

            <h2>5. User Profile</h2>
            <p>The User Profile section allows users to view and update personal information.</p>
            <h4>Features:</h4>
            <ul>
                <li>View personal information</li>
                <li>Edit details</li>
                <li>Change password</li>
            </ul>
            <h4>How to use:</h4>
            <ol>
                <li><strong>View Profile Information</strong>: User ID, username, and user level are displayed.</li>
                <img src="../../assets/img/screenshot/profile.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">

                <li><strong>Edit Personal Details</strong>: Update your username, first, middle and last names, and email.</li>
                <li><strong>Change Password</strong>: Enter and confirm a new password, then click <button class="btn btn-primary">Change Password</button></li>
            </ol>

            <h2>6. Report Generation</h2>
            <p>The Report Generation section allows users to generate various reports related to transactions and system usage.</p>
            <h4>Available Reports:</h4>
            <ul>
                <li><strong>Transaction Reports:</strong>
                    <ul>
                        <li>Reports</li>
                        <li>Tally In</li>
                        <li>Daily Unloading</li>
                        <li>Summary</li>
                        <li>Demurrage</li>
                        <li>Diverted</li>
                    </ul>
                </li>
                <li><strong>Extra Reports (Logs):</strong>
                    <ul>
                        <li>Settings</li>
                        <li>SMS</li>
                        <li>Event</li>
                    </ul>
                </li>
            </ul>
            <h4>How to generate a report:</h4>
            <ol>
                <li><strong>Set parameters or date ranges</strong>.</li>
                <img src="../../assets/img/screenshot/reportgen.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Click on the desired report</strong>.</li>
                <img src="../../assets/img/screenshot/report.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Click <button class="btn btn-primary">Export to Excel</button></strong> <strong>Or Click <button class="btn btn-primary">Export to PDF</button></strong></li>
                <img src="../../assets/img/screenshot/reportpre.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
            </ol>

            <h2>7. Settings</h2>
            <p>The Settings section allows administrators to configure various aspects of the system.</p>

            <h4>Transaction Settings:</h4>
            <ul>
                <li><strong>Haulers:</strong>
                    <ul>
                        <li>Manage hauler information.</li>
                        <li>Add or edit deactivate haulers.</li>
                    </ul>
                </li>
                <li><strong>Vehicles:</strong>
                    <ul>
                        <li>Add or edit vehicle information.</li>
                        <li>Manage plate numbers, vehicle types, and associated haulers.</li>
                    </ul>
                </li>
                <li><strong>Drivers & Helpers:</strong>
                    <ul>
                        <li>Manage driver and helper details.</li>
                    </ul>
                </li>
                <li><strong>Project Description:</strong>
                    <ul>
                        <li>Define and manage project descriptions.</li>
                    </ul>
                </li>
                <li><strong>Origin:</strong>
                    <ul>
                        <li>Set and manage transaction origins.</li>
                    </ul>
                </li>
                <li><strong>Demurrage:</strong>
                    <ul>
                        <li>Configure demurrage rates.</li>
                    </ul>
                </li>
            </ul>

            <h4>System Settings:</h4>
            <ul>
                <li><strong>Backup & Restore:</strong>
                    <ul>
                        <li>Perform database backups or restore from a previous backup.</li>
                    </ul>
                </li>
            </ul>

            <h4>How to use Settings:</h4>
            <ol>
                <img src="../../assets/img/screenshot/settings.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Click on the desired setting category</strong>.</li>
                <img src="../../assets/img/screenshot/settingspre.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Add new entries</strong> or edit existing ones.</li>
                <img src="../../assets/img/screenshot/settingsadd.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Save changes</strong> after making modifications.</li>
                <img src="../../assets/img/screenshot/settingsedit.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
                <li><strong>Follow on-screen instructions</strong> for Backup & Restore.</li>
                <img src="../../assets/img/screenshot/backup.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">
            </ol>

            <p>For further assistance, contact your system administrator or IT support team.</p>

        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>
<script src="../../assets/js/main.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Handle the export button click
        $("#exportBtn").click(function(e) {
            e.preventDefault(); // Prevent default anchor behavior

            // Get the content element
            const element = document.getElementById('manualContent');

            // Set the PDF options
            const options = {
                margin: 10,
                filename: 'vehicle-management-system-manual.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };

            // Show a loading message
            const loadingMessage = $('<div class="alert alert-info text-center" role="alert">Generating PDF, please wait...</div>');
            $('#content').prepend(loadingMessage);

            // Use html2pdf to generate and download the PDF
            html2pdf().from(element).set(options).save().then(function() {
                // Remove the loading message after PDF is generated
                loadingMessage.remove();
            });
        });

        // Your existing search functionality
        $("#search").keyup(function() {
            var searchText = $(this).val().toLowerCase();
            $("#manualContent *").each(function() {
                var content = $(this).text().toLowerCase();
                if (content.indexOf(searchText) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>
</body>

</html>