// Function to toggle theme and update styles
function updateThemeAndLogo() {
    const isDarkMode = document.body.classList.contains("dark-mode");

    // Update logo
    const logo = document.getElementById("logo");
    if (logo) {
        logo.src = isDarkMode ? "assets/logo-dark.png" : "assets/logo-light.png";
    }

    // Update dark mode for shared elements
    document.querySelectorAll(
        ".login-container, .form-control, .btn-primary, .accessibility-options, .accessibility-option"
    ).forEach((el) => el.classList.toggle("dark-mode", isDarkMode));
}

// Toggle Accessibility Menu
document.getElementById("accessibility-icon").addEventListener("click", function () {
    const menu = document.getElementById("accessibility-options");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
});

// Dark Mode Toggle
document.getElementById("dark-mode-toggle").addEventListener("click", function () {
    const isDarkModeEnabled = document.body.classList.toggle("dark-mode");

    // Synchronize theme and logo
    updateThemeAndLogo();

    // Save theme state
    localStorage.setItem("darkMode", isDarkModeEnabled ? "enabled" : "disabled");
});

// Apply Theme on Page Load
window.addEventListener("load", function () {
    const isDarkModeSaved = localStorage.getItem("darkMode") === "enabled";

    if (isDarkModeSaved) {
        document.body.classList.add("dark-mode");
    }

    // Synchronize theme and logo on page load
    updateThemeAndLogo();
});
