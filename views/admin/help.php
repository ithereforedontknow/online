<?php
include_once('../../includes/header/header-admin.php');
?>
<div class="content" id="content">
    <div class="container">
        <input type="text" class="form-control w-25 mb-3" name="search" id="search" placeholder="Search">
        <a href="api/export_manual.php" target="_blank" class="btn btn-primary" id="exportBtn">Export to PDF</a>
        <!-- <img src="../assets/ULPI_BLUE (1).png" class="mx-auto d-block" alt="" style="width: 500px;"> -->
        <!-- <h1 class="mb-4 text-center">Inhouse Vehicle Management System User Manual</h1> -->
        <div id="manualContent" class="mt-5">
            <h2>1. User Management</h2>
            <p>The User Management section allows administrators to manage user accounts within the system.</p>
            <h4>Features:</h4>
            <ul>
                <li>View all users in a table format</li>
                <li>Add new users</li>
                <li>Edit existing user information</li>
                <li>Activate or deactivate user accounts</li>
                <li>Search for users by name</li>
            </ul>
            <h4>How to use:</h4>
            <ol>
                <li><strong>View Users</strong>: All users are displayed in a table showing ID, Name, Username, Userlevel, Status, and Action options.</li>
                <li><strong>Add New User</strong>: Click the <button class="btn btn-primary">New User</button> button in the top right corner, fill in the required information, and click <button class="btn btn-primary">Save</button></li>
                <li><strong>Edit User</strong>: Click <button class="btn btn-primary">Edit</button> next to the user, update the info, and click <button class="btn btn-primary">Save</button></li>
                <li><strong>Activate/Deactivate User</strong>: Click <button class="btn btn-primary">Activate</button> or <button class="btn btn-secondary">Deactivate</button> to change the user's status.</li>
                <li><strong>Search Users</strong>: Use the search bar at the top of the table to search for users by name.</li>
            </ol>

            <!-- Add other sections here in the same format -->

            <h2>2. Transactions</h2>
            <p>This section manages transactions for vehicles that have departed and newly arrived.</p>
            <h4>Features:</h4>
            <ul>
                <li>View all departed transactions</li>
                <li>Add new transactions</li>
                <li>Edit transactions</li>
                <li>Cancel transactions</li>
                <li>Search transactions</li>
            </ul>
            <h4>How to use:</h4>
            <ol>
                <li><strong>View Departed Transactions</strong>: All transactions are displayed in a table.</li>
                <li><strong>Add New Transaction</strong>: Click <button class="btn btn-primary">New Transaction</button> fill in the form, and click <button class="btn btn-primary">Save</button></li>
                <li><strong>Edit Transaction</strong>: Click <button class="btn btn-primary">Edit</button> next to the transaction to update information.</li>
                <li><strong>Cancel Transaction</strong>: Click <button class="btn btn-secondary">Cancel</button> and confirm.</li>
                <li><strong>Search Transactions</strong>: Use the search bar to find specific transactions.</li>
                <li><strong>Edit Arrival Time</strong>: Update arrival time and click <button class="btn btn-primary">Save</button></li>
            </ol>

            <h2>3. Queue Management</h2>
            <p>The Queue Management section assists users in organizing and managing the vehicle queue.</p>

            <h4>Features:</h4>
            <ul>
                <li>View arrived vehicles</li>
                <li>Add vehicles to the queue</li>
                <li>Manage the current queue</li>
                <li>Filter the queue</li>
            </ul>

            <h4>How to Use:</h4>
            <ol>
                <li><strong>View Arrived Vehicles:</strong> Vehicles not yet in the queue are shown in the left column.</li>
                <li><strong>Manage Queue:</strong>
                    <ul>
                        <li>The current queue is displayed in the right column.</li>
                        <li>Use filter options such as Ordinal, Shift, Schedule, and Line to refine the queue.</li>
                    </ul>
                </li>
                <li><strong>View Queue Details:</strong> Click on a vehicle in the queue to view more information.</li>
                <li><strong>Search Queue:</strong> Use the search bar to find specific vehicles in the queue.</li>
                <li><strong>View Queue on TV Display:</strong> Click the <button class="btn btn-primary">View (TV)</button> button for a large screen display format of the queue.</li>
            </ol>


            <h2>4. Unloading Vehicles</h2>
            <p>The Unloading Vehicles section allows users to manage and track vehicles currently in the unloading process.</p>
            <h4>Features:</h4>
            <ul>
                <li>View all vehicles in the unloading process</li>
                <li>Update unloading start and end times</li>
                <li>Record time of departure</li>
                <li>Track time spent in waiting area and demurrage</li>
            </ul>
            <h4>How to use:</h4>
            <ol>
                <li><strong>View Unloading Vehicles</strong>: All vehicles in the unloading process are displayed in a table.</li>
                <li><strong>Update Unloading Start Time</strong>: Set the unloading start time and click <button class="btn btn-primary">Save</button></li>
                <li><strong>Update Unloading End Time</strong>: Set the unloading end time and click <button class="btn btn-primary">Save</button></li>
                <li><strong>Record Time of Departure</strong>: Set the departure time and click <button class="btn btn-primary">Done</button></li>
                <li><strong>Search Transactions</strong>: Use the search bar to find specific transactions.</li>
            </ol>

            <h2>5. Finished Transactions</h2>
            <p>The Finished Transactions section displays all completed transactions.</p>
            <h4>Features:</h4>
            <ul>
                <li>View all finished transactions</li>
                <li>Update transfer out net weight</li>
                <li>Record scrap weight</li>
                <li>Add remarks</li>
                <li>Search transactions</li>
            </ul>
            <h4>How to use:</h4>
            <ol>
                <li><strong>View Finished Transactions</strong>: Completed transactions are listed in a table.</li>
                <li><strong>Update Transfer Out Net Weight</strong>: Enter the transfer out weight and click <button class="btn btn-primary">Save</button></li>
                <li><strong>Record Scrap Weight</strong>: Enter the scrap weight and click <button class="btn btn-primary">Save</button></li>
                <li><strong>Add Remarks</strong>: Enter remarks and click <button class="btn btn-primary">Save</button></li>
                <li><strong>Search Transactions</strong>: Use the search bar to find finished transactions.</li>
            </ol>

            <h2>6. User Profile</h2>
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
                <li><strong>Edit Personal Details</strong>: Update your first, middle, and last names.</li>
                <li><strong>Change Password</strong>: Enter and confirm a new password, then click <button class="btn btn-primary">Change Password</button></li>
            </ol>

            <h2>7. Report Generation</h2>
            <p>The Report Generation section allows users to generate various reports related to transactions and system usage.</p>
            <h4>Available Reports:</h4>
            <ul>
                <li><strong>Transaction Reports:</strong>
                    <ul>
                        <li>Tally In</li>
                        <li>Daily Unloading</li>
                        <li>Order of Entry</li>
                        <li>Summary</li>
                    </ul>
                </li>
                <li><strong>Extra Reports:</strong>
                    <ul>
                        <li>Users</li>
                    </ul>
                </li>
            </ul>
            <h4>How to generate a report:</h4>
            <ol>
                <li><strong>Click on the desired report</strong>.</li>
                <li><strong>Set parameters or date ranges</strong> if needed.</li>
                <li><strong>Click <button class="btn btn-primary">Export to Excel</button></strong></li>
                <li><strong>Or Click <button class="btn btn-primary">Export to PDF</button></strong></li>
            </ol>

            <h2>8. Settings</h2>
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
                <li><strong>Click on the desired setting category</strong>.</li>
                <li><strong>Add new entries</strong> or edit existing ones.</li>
                <li><strong>Save changes</strong> after making modifications.</li>
                <li><strong>Follow on-screen instructions</strong> for Backup & Restore.</li>
            </ol>

            <p>For further assistance, contact your system administrator or IT support team.</p>

        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-admin.php');
?>