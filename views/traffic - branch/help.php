<?php
include_once('../../includes/header/header-branch.php');
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
                <li>Add new transactions</li>
            </ul>
            <h4>How to use:</h4>
            <ol>
                <li><strong>Add New Transaction</strong>: Fill out the form then Click <button class="btn btn-primary">Save</button> to add a new transaction</li>
                <img src="../../assets/img/screenshot/branch.png" class="my-3 mx-auto d-block" alt="" style="width: 500px;">

            </ol>

            <h2>2. User Profile</h2>
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

        </div>
    </div>
</div>
<?php
include_once('../../includes/footer/footer-branch.php');
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