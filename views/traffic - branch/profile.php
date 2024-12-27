<?php
include_once '../../includes/header/header-branch.php';

?>
<div class="content1" id="content">
    <div class="container p-5">
        <h1 class="display-5 mb-3 fw-bold">Profile</h1>
        <div class="row">
            <div class="col">
                <div class="container shadow-sm p-3 mb-5 bg-white rounded">
                    <form id="edit-profile-form" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="userId" class="form-label">User ID:</label>
                                    <input type="text" id="userId" class="form-control" value="<?php echo htmlspecialchars($row['id']); ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username:</label>
                                    <input type="text" id="edit-username" class="form-control" value="<?php echo htmlspecialchars($row['username']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="userlevel" class="form-label">User Level:</label>
                                    <input type="text" id="userlevel" class="form-control" value="<?php echo htmlspecialchars($row['userlevel']); ?>" disabled>
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="edit-firstname" class="form-label">First Name:</label>
                                    <input type="text" id="edit-firstname" class="form-control" value="<?php echo htmlspecialchars($row['fname']); ?>" required>
                                    <div class="invalid-feedback">Please provide your first name.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-middlename" class="form-label">Middle Name:</label>
                                    <input type="text" id="edit-middlename" class="form-control" value="<?php echo htmlspecialchars($row['mname']); ?>" required>
                                    <div class="invalid-feedback">Please provide your middle name.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-lastname" class="form-label">Last Name:</label>
                                    <input type="text" id="edit-lastname" class="form-control" value="<?php echo htmlspecialchars($row['lname']); ?>" required>
                                    <div class="invalid-feedback">Please provide your last name.</div>
                                </div>
                            </div>
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
include_once '../../includes/footer/footer-branch.php';
?>