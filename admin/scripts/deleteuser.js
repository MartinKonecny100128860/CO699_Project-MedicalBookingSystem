// script that deletes users from database
function deleteUser(userId) {
    if (confirm("Are you sure you want to delete this user?")) {
        $.post("php/delete_user.php", { id: userId }, function() {
            alert("User deleted successfully.");
            location.reload();
        }).fail(function() {
            alert("Error deleting user.");
        });
    }
}