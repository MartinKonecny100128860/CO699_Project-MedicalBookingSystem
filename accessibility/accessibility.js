// /* CODE FOR ACCESSIBILITY POPUP */
const accessibilityIcon = document.getElementById("accessibility-icon"); // Icon to open the accessibility popup
const accessibilityPopup = document.getElementById("accessibility-popup"); // The popup itself
const accessibilityClose = document.getElementById("accessibility-close"); // Close button inside the popup

// Open the accessibility popup
accessibilityIcon.addEventListener("click", () => togglePopup(true));

// Close the accessibility popup
accessibilityClose.addEventListener("click", () => togglePopup(false));

/**
 * Toggle the display of the accessibility popup.
 * @param {boolean} show - Whether to show or hide the popup
 */
function togglePopup(show) {
    if (show) {
        accessibilityPopup.style.display = "block";
        setTimeout(() => (accessibilityPopup.style.opacity = "1"), 50); // Smooth fade-in
    } else {
        accessibilityPopup.style.opacity = "0"; // Smooth fade-out
        setTimeout(() => (accessibilityPopup.style.display = "none"), 400);
    }
}

// /* CODE FOR DARK MODE */
const darkModeToggle = document.getElementById("dark-mode-toggle"); // Dark mode toggle button

if (darkModeToggle) {
    // Add click event to toggle dark mode
    darkModeToggle.addEventListener("click", toggleDarkMode);
}

/**
 * Toggles dark mode for the page and saves the state in localStorage.
 */
function toggleDarkMode() {
    document.body.classList.toggle("dark-mode");
    document.body.classList.remove("high-contrast-mode"); // Ensure high contrast is disabled

    localStorage.setItem("darkMode", document.body.classList.contains("dark-mode") ? "enabled" : "disabled");
    localStorage.setItem("highContrast", "disabled"); // Ensure high contrast mode is turned off

    updateThemeAndLogo();
    synchronizeDarkModeState();

    // Update theme elements
    updateThemeAndLogo();
    localStorage.setItem("darkMode", isDarkModeEnabled ? "enabled" : "disabled");

    // Ensure modals also change color in dark mode
    document.querySelectorAll(".modal-content").forEach((modal) => {
        modal.classList.toggle("dark-mode", isDarkModeEnabled);
    });

    // Fix modal backdrop issue in dark mode
    document.querySelectorAll(".modal-backdrop").forEach((backdrop) => {
        backdrop.style.backgroundColor = isDarkModeEnabled ? "rgba(0, 0, 0, 0.8)" : "rgba(0, 0, 0, 0.5)";
    });

    synchronizeDarkModeState();
}


/**
 * Synchronizes the dark mode toggle button and icon state.
 */
function synchronizeDarkModeState() {
    const toggleIcon = document.getElementById("dark-mode-icon");
    const isDarkMode = document.body.classList.contains("dark-mode");

    if (toggleIcon) {
        toggleIcon.classList.toggle("fa-toggle-on", isDarkMode); // Switch to 'on' icon
        toggleIcon.classList.toggle("fa-toggle-off", !isDarkMode); // Switch to 'off' icon
        toggleIcon.style.color = isDarkMode ? "#ffdd93" : "#0277bd"; // Update icon color
    }

    darkModeToggle.classList.toggle("active", isDarkMode); // Highlight the toggle button when active
}

/**
 * Updates the theme-related elements such as the logo and synchronizes dark mode state.
 */
function updateThemeAndLogo() {
    const isDarkMode = document.body.classList.contains("dark-mode");
    const logo = document.getElementById("logo");
    if (logo) {
        logo.src = isDarkMode ? "assets/logos/logo-dark.png" : "assets/logos/logo-light.png"; // Update logo based on theme
    }

    // Apply dark mode to multiple elements
    document.querySelectorAll(
        ".login-container, .form-control, .btn-primary, .accessibility-options, .accessibility-option, .sidebar, .content, .table-container"
    ).forEach((el) => el.classList.toggle("dark-mode", isDarkMode));

    synchronizeDarkModeState(); // Ensure all dark mode UI elements are synchronized
}

// /* CODE FOR HIGH CONTRAST MODE */
const highContrastButton = document.querySelector(".high-contrast-enable"); // High contrast toggle button

if (highContrastButton) {
    // Add click event to toggle high contrast mode
    highContrastButton.addEventListener("click", toggleHighContrast);
}

/**
 * Toggles high contrast mode and saves the state in localStorage.
 */
function toggleHighContrast() {
    document.body.classList.toggle("high-contrast-mode");
    document.body.classList.remove("dark-mode"); // Ensure dark mode is disabled

    localStorage.setItem("highContrast", document.body.classList.contains("high-contrast-mode") ? "enabled" : "disabled");
    localStorage.setItem("darkMode", "disabled"); // Ensure dark mode is turned off

    synchronizeHighContrastState();
}


/**
 * Updates the high contrast button text based on the mode state.
 */
function synchronizeHighContrastState() {
    const isHighContrastEnabled = document.body.classList.contains("high-contrast-mode");
    highContrastButton.textContent = isHighContrastEnabled ? "Disable" : "Enable"; // Toggle button text
}

// /* CODE FOR TEXT RESIZING */
let currentFontSize = 16; // Default font size

const textResizeDecrease = document.querySelector(".text-resize-decrease"); // Button to decrease text size
const textResizeIncrease = document.querySelector(".text-resize-increase"); // Button to increase text size

if (textResizeDecrease && textResizeIncrease) {
    textResizeDecrease.addEventListener("click", () => resizeText(-2)); // Decrease font size
    textResizeIncrease.addEventListener("click", () => resizeText(2)); // Increase font size
}

/**
 * Adjusts the text size of the page within a specified range and saves the size in localStorage.
 * @param {number} amount - The amount to change the font size by.
 */
function resizeText(amount) {
    const newFontSize = currentFontSize + amount;
    if (newFontSize >= 12 && newFontSize <= 24) {
        currentFontSize = newFontSize;
        document.body.style.fontSize = `${currentFontSize}px`; // Apply new font size
        localStorage.setItem("fontSize", currentFontSize); // Save font size
    }
}

// /* CODE FOR TEXT-TO-SPEECH */
let ttsOnClickEnabled = false; // Text-to-speech state
const ttsButton = document.querySelector(".tts-on-click-enable"); // Text-to-speech toggle button

if (ttsButton) {
    ttsButton.addEventListener("click", toggleTextToSpeech); // Toggle TTS mode
}

/**
 * Toggles text-to-speech mode and updates the button text.
 */
function toggleTextToSpeech() {
    ttsOnClickEnabled = !ttsOnClickEnabled;
    ttsButton.textContent = ttsOnClickEnabled ? "Disable" : "Enable"; // Update button text
    document.body.classList.toggle("tts-enabled", ttsOnClickEnabled);
}

// Handle clicks on the page for text-to-speech functionality
document.addEventListener("click", (event) => {
    if (ttsOnClickEnabled) {
        const target = event.target;
        if (isTextReadable(target)) {
            speakText(target.innerText.trim()); // Speak readable text
        } else if (isInputWithPlaceholder(target)) {
            speakText(`Input field: ${target.placeholder.trim()}`); // Speak input placeholder
            highlightElement(target); // Highlight the input field
        }
    }
});

// Utility function to check if an element has readable text
function isTextReadable(target) {
    return ["BUTTON", "A", "H1", "H2", "H3", "H4", "H5", "H6", "P"].includes(target.tagName);
}

// Utility function to check if an input element has a placeholder
function isInputWithPlaceholder(target) {
    return ["INPUT", "TEXTAREA"].includes(target.tagName) && target.placeholder.trim();
}

// Function to speak the given text
function speakText(text) {
    if (text) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 1;
        utterance.pitch = 1;
        utterance.volume = 1;
        window.speechSynthesis.cancel(); // Stop any ongoing speech
        window.speechSynthesis.speak(utterance); // Speak the text
    }
}

// Function to highlight an element temporarily
function highlightElement(element) {
    element.classList.add("tts-highlight");
    setTimeout(() => element.classList.remove("tts-highlight"), 1500);
}

// /* APPLY SAVED STATES ON PAGE LOAD */
window.addEventListener("load", () => {
    // Apply saved dark mode
    if (localStorage.getItem("darkMode") === "enabled") {
        document.body.classList.add("dark-mode");
    }

    // Apply saved high contrast mode
    if (localStorage.getItem("highContrast") === "enabled") {
        document.body.classList.add("high-contrast-mode");
    }

    // Apply saved font size
    const savedFontSize = localStorage.getItem("fontSize");
    if (savedFontSize) {
        currentFontSize = parseInt(savedFontSize, 10);
        document.body.style.fontSize = `${currentFontSize}px`;
    }

    // Synchronize all states
    updateThemeAndLogo();
    synchronizeHighContrastState();
    synchronizeDarkModeState();
});
