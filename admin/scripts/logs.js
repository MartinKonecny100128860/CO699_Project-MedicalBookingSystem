document.addEventListener("DOMContentLoaded", function () {
    let currentPage = 1;
    const logsPerPage = 5;

    function loadLogs() {
        $.ajax({
            url: "php/managing_logs.php",
            type: "GET",
            data: {
                page: currentPage,
                logs_per_page: logsPerPage,
                search: $("#searchLogs").val(),
                action_filter: $("#filterAction").val(),
                admin_filter: $("#filterAdmin").val()
            },
            dataType: "json",
            success: function (response) {
                $("#logsTableBody").empty();
                if (response.logs.length > 0) {
                    response.logs.forEach(log => {
                        $("#logsTableBody").append(`
                            <tr>
                                <td>${log.admin_id}</td>
                                <td>${log.action}</td>
                                <td>${log.timestamp}</td>
                                <td><button class="btn btn-danger btn-sm delete-log-btn" data-id="${log.log_id}">&#10005;</button></td>
                            </tr>
                        `);
                    });

                    // Re-attach event listener after dynamically loading logs
                    $(".delete-log-btn").click(function () {
                        let logId = $(this).data("id");
                        deleteLog(logId);
                    });

                } else {
                    $("#logsTableBody").append(`<tr><td colspan="4">No logs found.</td></tr>`);
                }

                $("#currentPage").text(`Page ${currentPage}`);
                $("#prevPage").prop("disabled", currentPage === 1);
                $("#nextPage").prop("disabled", !response.hasMorePages);
            },
            error: function () {
                alert("Error loading logs.");
            }
        });
    }

    function deleteLog(logId) {
        console.log("Attempting to delete log with ID:", logId); // Debugging

        if (confirm("Are you sure you want to delete this log?")) {
            $.ajax({
                url: "php/managing_logs.php",
                type: "POST",
                data: { action: "delete", log_id: logId },
                dataType: "json",
                success: function (response) {
                    console.log("Server Response:", response); // Debugging
                    alert(response.message);
                    loadLogs();
                },
                error: function (xhr, status, error) {
                    console.error("Error:", error);
                    console.log(xhr.responseText);
                    alert("Error deleting log.");
                }
            });
        }
    }

    $("#searchLogs, #filterAction, #filterAdmin").on("input change", function () {
        currentPage = 1;
        loadLogs();
    });

    $("#prevPage").click(function () {
        if (currentPage > 1) {
            currentPage--;
            loadLogs();
        }
    });

    $("#nextPage").click(function () {
        currentPage++;
        loadLogs();
    });

    loadLogs();
});
