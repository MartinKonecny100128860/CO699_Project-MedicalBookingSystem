<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" enctype="multipart/form-data">
                    <input type="hidden" id="edit_user_id" name="user_id">

                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">New Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-control" id="edit_role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="doctor">Doctor</option>
                            <option value="receptionist">Receptionist</option>
                            <option value="patient">Patient</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name">
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name">
                    </div>
                    <div class="mb-3">
                        <label for="edit_date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth">
                    </div>
                    <div class="mb-3">
                        <label for="edit_house_no" class="form-label">House No</label>
                        <input type="text" class="form-control" id="edit_house_no" name="house_no">
                    </div>
                    <div class="mb-3">
                        <label for="edit_street_name" class="form-label">Street Name</label>
                        <input type="text" class="form-control" id="edit_street_name" name="street_name">
                    </div>
                    <div class="mb-3">
                        <label for="edit_post_code" class="form-label">Post Code</label>
                        <input type="text" class="form-control" id="edit_post_code" name="post_code">
                    </div>
                    <div class="mb-3">
                        <label for="edit_city" class="form-label">City</label>
                        <input type="text" class="form-control" id="edit_city" name="city">
                    </div>
                    <div class="mb-3">
                        <label for="edit_telephone" class="form-label">Telephone</label>
                        <input type="text" class="form-control" id="edit_telephone" name="telephone">
                    </div>
                    <div class="mb-3">
                        <label for="edit_emergency_contact" class="form-label">Emergency Contact</label>
                        <input type="text" class="form-control" id="edit_emergency_contact" name="emergency_contact">
                    </div>
                    <div class="mb-3">
                        <label for="edit_gender" class="form-label">Gender</label>
                        <select class="form-control" id="edit_gender" name="gender">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="prefer not to say">Prefer not to say</option>
                        </select>
                    </div>
                 <!--    <div class="mb-3">
                        <label for="edit_profile_picture" class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" id="edit_profile_picture" name="profile_picture">
                        <small>Leave blank to keep the current profile picture.</small>
                    </div> 
                    --> 
                    <button type="submit" class="btn btn-primary">Update User</button>
                </form>
            </div>
        </div>
    </div>
</div>
