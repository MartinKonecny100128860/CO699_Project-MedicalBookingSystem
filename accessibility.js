// Open Accessibility Popup
document.getElementById("accessibility-icon").addEventListener("click", function () {
    const popup = document.getElementById("accessibility-popup");
    popup.style.display = "block"; // Show popup
    setTimeout(() => (popup.style.opacity = "1"), 50); // Smooth fade-in
});

// Close Accessibility Popup
document.getElementById("accessibility-close").addEventListener("click", function () {
    const popup = document.getElementById("accessibility-popup");
    popup.style.opacity = "0"; // Smooth fade-out
    setTimeout(() => (popup.style.display = "none"), 400); // Hide after fade-out
});

// Synchronize Dark Mode Toggle Icon
function synchronizeToggleState() {
    const toggleIcon = document.getElementById("dark-mode-icon");
    const isDarkMode = document.body.classList.contains("dark-mode");

    if (toggleIcon) {
        if (isDarkMode) {
            toggleIcon.classList.remove("fa-toggle-off");
            toggleIcon.classList.add("fa-toggle-on");
            toggleIcon.style.color = "#ffdd93"; // Yellow for dark mode
            toggleIcon.style.cursor = "pointer";
        } else {
            toggleIcon.classList.remove("fa-toggle-on");
            toggleIcon.classList.add("fa-toggle-off");
            toggleIcon.style.color = "#0277bd"; // Blue for light mode
            toggleIcon.style.cursor = "pointer";
        }
    }
}

// Update Theme and Synchronize Toggle
function updateThemeAndLogo() {
    const isDarkMode = document.body.classList.contains("dark-mode");

    // Update logo based on theme
    const logo = document.getElementById("logo");
    if (logo) {
        logo.src = isDarkMode ? "assets/logo-dark.png" : "assets/logo-light.png";
    }

    // Apply dark mode styles to relevant elements
    document.querySelectorAll(
        ".login-container, .form-control, .btn-primary, .accessibility-options, .accessibility-option, .sidebar, .content, .table-container"
    ).forEach((el) => el.classList.toggle("dark-mode", isDarkMode));

    // Synchronize the toggle icon state
    synchronizeToggleState();
}

// Attach Event Listener for Dark Mode Toggle
const darkModeToggle = document.getElementById("dark-mode-toggle");
if (darkModeToggle) {
    darkModeToggle.addEventListener("click", function () {
        const isDarkModeEnabled = document.body.classList.toggle("dark-mode");

        // Update theme and logo
        updateThemeAndLogo();

        // Save theme state in localStorage
        localStorage.setItem("darkMode", isDarkModeEnabled ? "enabled" : "disabled");
    });
}

// Apply Theme on Page Load
window.addEventListener("load", function () {
    const isDarkModeSaved = localStorage.getItem("darkMode") === "enabled";

    if (isDarkModeSaved) {
        document.body.classList.add("dark-mode");
    }

    // Update theme and synchronize toggle on page load
    updateThemeAndLogo();
});
