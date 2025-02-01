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
document.addEventListener("DOMContentLoaded", function () {
    // Fetch recent logs
    fetch("php/stats/get_recent_logs.php")
        .then(response => response.json())
        .then(data => {
            console.log("Recent Logs Response:", data); // Debugging
            if (data.length === 0) {
                document.getElementById("recentLogs").innerHTML = "<li>No recent activity found.</li>";
            } else {
                const logsList = document.getElementById("recentLogs");
                logsList.innerHTML = "";
                data.forEach(log => {
                    logsList.innerHTML += `<li>Admin ${log.admin_id} ${log.action} on ${log.timestamp}</li>`;
                });
            }
        })
        .catch(error => console.error("Error fetching recent logs:", error));

    // Fetch active admins
    fetch("php/stats/get_active_admins.php")
        .then(response => response.json())
        .then(data => {
            console.log("Active Admins Response:", data); // Debugging
            if (data.length === 0) {
                document.getElementById("activeAdmins").innerHTML = "<li>No active admins.</li>";
            } else {
                const adminList = document.getElementById("activeAdmins");
                adminList.innerHTML = "";
                data.forEach(admin => {
                    adminList.innerHTML += `<li>${admin.username} (ID: ${admin.user_id})</li>`;
                });
            }
        })
        .catch(error => console.error("Error fetching active admins:", error));
});

document.addEventListener("DOMContentLoaded", function () {
    fetch("php/stats/get_chart_data.php")
        .then(response => response.json())
        .then(data => {
            console.log("Chart Data:", data); // Debugging

            if (data.error) {
                console.error("Error fetching chart data:", data.error);
                return;
            }

            // 🟢 User Growth Chart (Ignore appointments)
            const userMonths = data.usersPerMonth.map(item => item.month);
            const userCounts = data.usersPerMonth.map(item => item.count);

            const userChart = new Chart(document.getElementById("userRegistrationsChart"), {
                type: "bar",
                data: {
                    labels: userMonths,
                    datasets: [{
                        label: "Users Registered",
                        data: userCounts,
                        backgroundColor: "#495390",
                        borderColor: "#034f6e",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })
        .catch(error => console.error("Error fetching chart data:", error));
});


