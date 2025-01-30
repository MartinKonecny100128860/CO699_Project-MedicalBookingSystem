function addNewUser() {
    const formData = new FormData(document.getElementById('addUserForm'));

    $.ajax({
        url: 'php/add_user.php', // Ensure this matches your backend URL
        type: 'POST',
        data: formData,
        contentType: false, // Important for file upload
        processData: false, // Prevent jQuery from parsing form data
        dataType: 'json', // Expect JSON response
        success: function (response) {
            if (response.success) {
                $("#successMessage").text(response.message).fadeIn();

                // Close the modal
                setTimeout(() => {
                    const addUserModalElement = document.getElementById('addUserModal');
                    const addUserModalInstance = bootstrap.Modal.getInstance(addUserModalElement);
                    if (addUserModalInstance) {
                        addUserModalInstance.hide();
                    }
                    document.getElementById("addUserForm").reset();
                    setTimeout(() => location.reload(), 1000);
                }, 2000);
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", xhr.responseText);
            alert("Error adding user. Please try again later.");
        }
    });
}
