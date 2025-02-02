// script that adds users to the database
$(document).ready(function() {
    $("#addUserForm").submit(function(event) {
        event.preventDefault();
        
        var formData = new FormData(this);

        $.ajax({
            url: 'php/add_user.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert(response);
                location.reload();
            },
            error: function() {
                alert('Error adding user.');
            }
        });
    });
});
