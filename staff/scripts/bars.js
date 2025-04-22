document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.getElementById("toggleSidebarBtn");

    // Load sidebar state from localStorage
    if (localStorage.getItem("sidebarState") === "closed") {
        sidebar.classList.add("sidebar-hidden");
        toggleBtn.textContent = "☰";
    }

    // Toggle sidebar and update localStorage
    toggleBtn.addEventListener("click", function () {
        sidebar.classList.toggle("sidebar-hidden");

        if (sidebar.classList.contains("sidebar-hidden")) {
            toggleBtn.textContent = "☰";
            localStorage.setItem("sidebarState", "closed");
        } else {
            toggleBtn.textContent = "✖";
            localStorage.setItem("sidebarState", "open");
        }
    });
});