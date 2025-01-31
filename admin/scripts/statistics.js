document.addEventListener("DOMContentLoaded", function () {
    // Fetch main statistics
    fetch("php/stats/get_statistics.php")
        .then(response => response.json())
        .then(data => {
            console.log("Statistics Data:", data); // Debugging
            if (data.error) {
                console.error("Error fetching statistics:", data.error);
                return;
            }
            document.getElementById("totalUsers").innerText = data.totalUsers;
            document.getElementById("totalAppointments").innerText = data.totalAppointments;
            document.getElementById("totalLogs").innerText = data.totalLogs;
            document.getElementById("mostActiveAdmin").innerText = data.mostActiveAdmin;
        })
        .catch(error => console.error("Error fetching statistics:", error));
});
