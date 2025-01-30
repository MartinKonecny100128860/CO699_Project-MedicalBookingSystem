function showEditUserModal(userId, username, email) {
    $('#editUserId').val(userId);
    $('#editUsername').val(username);
    $('#editEmail').val(email);
    $('#editPassword').val('');
    $('#editUserModal').modal('show');
}

function saveUserChanges() {
    const userId = $('#editUserId').val();
    const username = $('#editUsername').val();
    const email = $('#editEmail').val();
    const password = $('#editPassword').val();

    $.post("php/admin_edit_users.php", { user_id: userId, username, email, password }, function (data) {
        if (data.success) {
            alert(data.message); // Display success message
            location.reload(); // Reload the page to reflect changes
        } else {
            alert(data.message); // Display error message
        }
    }, "json").fail(function () {
        alert("Error updating user.");
    });
}

$(document).ready(function () {
    $(".save-role-btn").click(function () {
        const userId = $(this).data("user-id");
        const newRole = $(this).closest("tr").find(".role-dropdown").val();

        $.ajax({
            url: "php/update_user_role.php",
            type: "POST",
            data: { user_id: userId, role: newRole },
            success: function (response) {
                let data = JSON.parse(response);
                if (data.success) {
                    alert("User role updated successfully.");
                    location.reload();
                } else {
                    alert("Error updating role.");
                }
            },
            error: function () {
                alert("An error occurred while updating the role.");
            }
        });
    });
});