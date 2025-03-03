<?php
include_once '../../includes/header/header-admin.php';

?>
<div class="content" id="content">
    <div class="container">
        <?php
        $userId = $_SESSION['id']; // Example: get the user ID from session
        $stmt = $conn->prepare("SELECT * FROM users INNER JOIN origin ON users.branch = origin.origin_id WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <h1 class="display-5 mb-3 fw-bold">Profile</h1>
        <div class="row">
            <div class="col">
                <div class="container shadow-sm p-3 mb-5 bg-white rounded">
                    <form id="edit-profile-form" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="userId" class="form-label">User ID:</label>
                                    <input type="text" id="userId" class="form-control" value="<?php echo htmlspecialchars($profile['id']); ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username:</label>
                                    <input type="text" id="edit-username" class="form-control" value="<?php echo htmlspecialchars($profile['username']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="userlevel" class="form-label">User Level:</label>
                                    <input type="text" id="userlevel" class="form-control" value="<?php echo htmlspecialchars($profile['userlevel']); ?>" disabled>
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="edit-firstname" class="form-label">First Name:</label>
                                    <input type="text" id="edit-firstname" class="form-control" value="<?php echo htmlspecialchars($profile['fname']); ?>" required>
                                    <div class="invalid-feedback">Please provide your first name.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-middlename" class="form-label">Middle Name:</label>
                                    <input type="text" id="edit-middlename" class="form-control" value="<?php echo htmlspecialchars($profile['mname']); ?>" required>
                                    <div class="invalid-feedback">Please provide your middle name.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-lastname" class="form-label">Last Name:</label>
                                    <input type="text" id="edit-lastname" class="form-control" value="<?php echo htmlspecialchars($profile['lname']); ?>" required>
                                    <div class="invalid-feedback">Please provide your last name.</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit-email" class="form-label">New Email:</label>
                            <input type="text" id="edit-email" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                            <div class="invalid-feedback">Please provide your email.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit-newPassword" class="form-label">New Password:</label>
                            <input type="password" id="edit-newPassword" name="new_password" class="form-control" required>
                            <div class="invalid-feedback">Please provide a new password.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit-confirmPassword" class="form-label">Confirm New Password:</label>
                            <input type="password" id="edit-confirmPassword" name="confirm_password" class="form-control" required>
                            <div class="invalid-feedback">Please confirm your new password.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once '../../includes/footer/footer-admin.php';
?>
<script src="../../assets/js/main.js"></script>

<script>
    $("#edit-profile-form").submit((event) => {
        event.preventDefault(); // Prevent the default form submission

        // Reset validation feedback
        const form = event.target;
        Array.from(form.elements).forEach((input) => {
            input.classList.remove("is-invalid");
            input.classList.remove("is-valid");
        });

        const newPassword = $("#edit-newPassword").val();
        const confirmPassword = $("#edit-confirmPassword").val();

        // Check if new password and confirm password match
        if (newPassword !== confirmPassword) {
            $("#edit-confirmPassword").addClass("is-invalid");
            $("#edit-newPassword")
                .addClass("is-invalid")
                .next(".invalid-feedback")
                .text("Passwords do not match.")
                .show();
            $("#edit-confirmPassword")
                .next(".invalid-feedback")
                .text("Passwords do not match.")
                .show();
            return; // Exit the function if passwords do not match
        } else if (newPassword === "" || confirmPassword === "") {
            $("#edit-confirmPassword").addClass("is-invalid");
            $("#edit-newPassword")
                .addClass("is-invalid")
                .next(".invalid-feedback")
                .text("Please provide a password.")
                .show();
            $("#edit-confirmPassword")
                .next(".invalid-feedback")
                .text("Please provide a password for confirmation.")
                .show();
            return;
        } else {
            $("#edit-confirmPassword").removeClass("is-invalid").addClass("is-valid");
            $("#edit-newPassword").removeClass("is-invalid").addClass("is-valid");
        }
        Swal.fire({
            title: "Are you sure?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#1c3464",
            cancelButtonColor: "#6c757d",
            cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                const data = {
                    action: "update profile",
                    id: $("#userId").val(), // Assuming user ID is needed for the request
                    username: $("#edit-username").val(),
                    fname: $("#edit-firstname").val(),
                    mname: $("#edit-middlename").val(),
                    lname: $("#edit-lastname").val(),
                    new_password: newPassword,
                };

                $.post("../../api/user.php", data)
                    .done((response) => {
                        Swal.fire({
                            title: "Updated!",
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1500,
                            didClose: () => {
                                window.location.reload(); // Reload the page to reflect changes
                            },
                        });
                    })
                    .fail((error) => {
                        // Handle error response
                        alert("Error updating profile.");
                    });
            }
        });
        // If validation passes, submit the form via AJAX
    });
</script>