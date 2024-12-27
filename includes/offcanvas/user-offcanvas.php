<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="addUserOffcanvas" aria-labelledby="addUserOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title " id="addUserOffcanvasLabel">New <span class="fw-bold">user</span> record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="add-user-form">
            <input type="hidden" id="add-user-id">
            <div class="row">
                <div class="col">
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="add-username" name="username" required>
                        <label for="add-username">Username</label>
                    </div>
                </div>
                <div class="col">
                    <div class="form-floating mb-4">
                        <select class="form-control" id="add-userlevel" name="userlevel" required onchange="showBranchOption()">
                            <option value="admin">Admin</option>
                            <option value="encoder">Encoder</option>
                            <option value="traffic(main)">Traffic-in-Charge (Main)</option>
                            <option value="traffic(branch)">Traffic-in-Charge (Branch)</option>
                        </select>
                        <label for="add-userlevel" class="form-label">User Level</label>
                    </div>
                </div>
                <div class="col" id="add-branch-container" style="display: none;">
                    <div class="form-floating mb-4">
                        <select class="form-control" id="add-branch" name="branch" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM `origin`");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['origin_id'] . '">' . $row['origin_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="add-branch" class="form-label">Branch</label>
                    </div>
                </div>
            </div>

            <div class="form-floating mb-4">
                <input type="text" class="form-control" id="add-fname" name="fname" required>
                <label for="add-fname" class="form-label">First Name</label>
            </div>

            <div class="form-floating mb-4">
                <input type="text" class="form-control" id="add-mname" name="mname" required>
                <label for="add-mname" class="form-label">Middle Name</label>
            </div>

            <div class="form-floating mb-4">
                <input type="text" class="form-control" id="add-lname" name="lname" required>
                <label for="add-lname" class="form-label">Last Name</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="add-password" name="password" required>
                <label for="add-password" class="form-label">Password</label>
            </div>
            <div class="form-floating mb-4">
                <input type="email" class="form-control" id="add-email" name="email" required>
                <label for="add-email" class="form-label">Email</label>
            </div>

        </form>
    </div>
    <div class="offcanvas-footer d-flex justify-content-end p-3 border-top">
        <button type="button" class="btn btn-dark me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary" form="add-user-form">Create</button>
    </div>
</div>
<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="editUserOffcanvas" aria-labelledby="editUserOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="editUserOffcanvasLabel">Edit <span class="fw-bold">user</span> record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="edit-user-form">
            <input type="hidden" id="edit-user-id">
            <div class="row">
                <div class="col">
                    <div class="form-floating mb-4">
                        <input type="text" class="form-control" id="edit-username" name="username" required>
                        <label for="edit-username">Username</label>
                    </div>
                </div>
                <div class="col">
                    <div class="form-floating mb-4">
                        <select class="form-control" id="edit-userlevel" name="userlevel" required onchange="showBranchOption()">
                            <option value="admin">Admin</option>
                            <option value="encoder">Encoder</option>
                            <option value="traffic(main)">Traffic-in-Charge (Main)</option>
                            <option value="traffic(branch)">Traffic-in-Charge (Branch)</option>
                        </select>
                        <label for="edit-userlevel" class="form-label">User Level</label>
                    </div>
                </div>
                <div class="col" id="edit-branch-container" style="display: none;">
                    <div class="form-floating mb-4">
                        <select class="form-control" id="edit-branch" name="branch" required>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM `origin`");
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['origin_id'] . '">' . $row['origin_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <label for="edit-branch" class="form-label">Branch</label>
                    </div>
                </div>
            </div>
            <div class="form-floating mb-4">
                <input type="text" class="form-control" id="edit-fname" name="fname" required>
                <label for="edit-fname" class="form-label">First Name</label>
            </div>
            <div class="form-floating mb-4">
                <input type="text" class="form-control" id="edit-mname" name="mname" required>
                <label for="edit-mname" class="form-label">Middle Name</label>
            </div>
            <div class="form-floating mb-4">
                <input type="text" class="form-control" id="edit-lname" name="lname" required>
                <label for="edit-lname" class="form-label">Last Name</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="edit-password" name="password" required>
                <label for="edit-password" class="form-label">Password</label>
            </div>
            <div class="form-floating mb-4">
                <input type="email" class="form-control" id="edit-email" name="email" required>
                <label for="edit-email" class="form-label">Email</label>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer d-flex justify-content-end p-3 border-top">
        <button type="button" class="btn btn-dark me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary" form="edit-user-form">Save Changes</button>
    </div>
</div>