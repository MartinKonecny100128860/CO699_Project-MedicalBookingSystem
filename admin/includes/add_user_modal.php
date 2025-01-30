<!-- Add New User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-container">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Success Message -->
                <div id="successMessage" class="success-message" style="display: none;">
                    New user added! Please close the window or add another user.
                </div>

                <!-- Add User Form -->
                <form id="addUserForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telephone" class="form-label">Telephone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="emergencyContact" class="form-label">Emergency Contact</label>
                        <input type="text" class="form-control" id="emergencyContact" name="emergency_contact" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="houseNo" class="form-label">House No / Name</label>
                            <input type="text" class="form-control" id="houseNo" name="house_no" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="streetName" class="form-label">Street Name</label>
                            <input type="text" class="form-control" id="streetName" name="street_name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="postCode" class="form-label">Post Code</label>
                            <input type="text" class="form-control" id="postCode" name="post_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                    </div>

                    <!-- Role Type Selection Field -->
                    <div class="mb-3">
                        <label for="roleType" class="form-label">Role Type</label>
                        <select class="form-control" id="roleType" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="doctor">Doctor</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="profilePicture" class="form-label">Upload Profile Picture</label>
                        <input type="file" class="form-control" id="profilePicture" name="profile_picture" accept="image/*">
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="addNewUser()">Add User</button>
            </div>
        </div>
    </div>
</div>
