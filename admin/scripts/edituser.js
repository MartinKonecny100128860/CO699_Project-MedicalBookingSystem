$(document).ready(function() {
    function safeValue(value) {
        return value !== null && value !== undefined ? value : '';
    }

    $(".edit-user-btn").click(function() {
        var userId = $(this).data("id");

        $.ajax({
            url: 'php/admin_edit_users.php',
            type: 'POST',
            data: { user_id: userId, action: "fetch" },
            dataType: 'json',
            success: function(response) {
                console.log("Response from server:", response);

                if (response.error) {
                    alert(response.error);
                    return;
                }

                $("#edit_user_id").val(safeValue(response.user_id));
                $("#edit_username").val(safeValue(response.username));
                $("#edit_email").val(safeValue(response.email));
                $("#edit_role").val(safeValue(response.role));
                $("#edit_first_name").val(safeValue(response.first_name));
                $("#edit_last_name").val(safeValue(response.last_name));
                $("#edit_house_no").val(safeValue(response.house_no));
                $("#edit_street_name").val(safeValue(response.street_name));
                $("#edit_post_code").val(safeValue(response.post_code));
                $("#edit_city").val(safeValue(response.city));
                $("#edit_telephone").val(safeValue(response.telephone));
                $("#edit_emergency_contact").val(safeValue(response.emergency_contact));
                $("#edit_gender").val(safeValue(response.gender));

                // Set the Date of Birth properly
                if (response.date_of_birth) {
                    $("#edit_date_of_birth").val(response.date_of_birth);
                } else {
                    $("#edit_date_of_birth").val("");
                }

                showEditUserModal();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + error);
                console.log(xhr.responseText);
                alert("Failed to fetch user details.");
            }
        });
    });

    function showEditUserModal() {
        const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
        editUserModal.show();
    }

    $("#editUserForm").submit(function(event) {
        event.preventDefault();

        var formData = new FormData(this);
        formData.append("action", "update");

        $.ajax({
            url: 'php/admin_edit_users.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log("Update Response:", response);
                alert(response.message);
                if (!response.error) {
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + error);
                console.log(xhr.responseText);
                alert('Error updating user.');
            }
        });
    });
});


$(document).ready(function () {
    $("#editUserForm").submit(function (event) {
        event.preventDefault();

        var formData = new FormData(this);
        formData.append("action", "update");

        $.ajax({
            url: 'php/admin_edit_users.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                console.log("Update Response:", response);

                if (response.error) {
                    alert(response.error);
                } else if (response.message) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert("Unexpected response from server.");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                console.log(xhr.responseText);
                alert('Error updating user.');
            }
        });
    });
});
