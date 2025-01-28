// Accessibility Popup Handlers
const accessibilityIcon = document.getElementById("accessibility-icon");
const accessibilityPopup = document.getElementById("accessibility-popup");
const accessibilityClose = document.getElementById("accessibility-close");

if (accessibilityIcon && accessibilityPopup && accessibilityClose) {
    accessibilityIcon.addEventListener("click", () => togglePopup(true));
    accessibilityClose.addEventListener("click", () => togglePopup(false));
}

function togglePopup(show) {
    if (show) {
        accessibilityPopup.style.display = "block";
        setTimeout(() => (accessibilityPopup.style.opacity = "1"), 50);
    } else {
        accessibilityPopup.style.opacity = "0";
        setTimeout(() => (accessibilityPopup.style.display = "none"), 400);
    }
}

// Dark Mode Handlers
const darkModeToggle = document.getElementById("dark-mode-toggle");

if (darkModeToggle) {
    darkModeToggle.addEventListener("click", toggleDarkMode);
}

function toggleDarkMode() {
    const isDarkModeEnabled = document.body.classList.toggle("dark-mode");
    updateThemeAndLogo();
    localStorage.setItem("darkMode", isDarkModeEnabled ? "enabled" : "disabled");
    synchronizeDarkModeState();
}

function synchronizeDarkModeState() {
    const toggleIcon = document.getElementById("dark-mode-icon");
    const isDarkMode = document.body.classList.contains("dark-mode");

    if (toggleIcon) {
        toggleIcon.classList.toggle("fa-toggle-on", isDarkMode);
        toggleIcon.classList.toggle("fa-toggle-off", !isDarkMode);
        toggleIcon.style.color = isDarkMode ? "#ffdd93" : "#0277bd";
    }

    if (darkModeToggle) {
        darkModeToggle.classList.toggle("active", isDarkMode);
    }
}

function updateThemeAndLogo() {
    const isDarkMode = document.body.classList.contains("dark-mode");
    const logo = document.getElementById("logo");
    if (logo) {
        logo.src = isDarkMode ? "assets/logo-dark.png" : "assets/logo-light.png";
    }

    document.querySelectorAll(
        ".login-container, .form-control, .btn-primary, .accessibility-options, .accessibility-option, .sidebar, .content, .table-container"
    ).forEach((el) => el.classList.toggle("dark-mode", isDarkMode));
    synchronizeDarkModeState();
}

// High Contrast Handlers
const highContrastButton = document.querySelector(".high-contrast-enable");

if (highContrastButton) {
    highContrastButton.addEventListener("click", toggleHighContrast);
}

function toggleHighContrast() {
    const isHighContrastEnabled = document.body.classList.toggle("high-contrast-mode");
    localStorage.setItem("highContrast", isHighContrastEnabled ? "enabled" : "disabled");
    synchronizeHighContrastState();
}

function synchronizeHighContrastState() {
    const isHighContrastEnabled = document.body.classList.contains("high-contrast-mode");
    if (highContrastButton) {
        highContrastButton.textContent = isHighContrastEnabled ? "Disable" : "Enable";
    }
}

// Text Resizing Handlers
let currentFontSize = 16;

const textResizeDecrease = document.querySelector(".text-resize-decrease");
const textResizeIncrease = document.querySelector(".text-resize-increase");

if (textResizeDecrease && textResizeIncrease) {
    textResizeDecrease.addEventListener("click", () => resizeText(-2));
    textResizeIncrease.addEventListener("click", () => resizeText(2));
}

function resizeText(amount) {
    const newFontSize = currentFontSize + amount;
    if (newFontSize >= 12 && newFontSize <= 24) {
        currentFontSize = newFontSize;
        document.body.style.fontSize = `${currentFontSize}px`;
        localStorage.setItem("fontSize", currentFontSize);
    }
}

// Text-to-Speech Handlers
let ttsOnClickEnabled = false;
const ttsButton = document.querySelector(".tts-on-click-enable");

if (ttsButton) {
    ttsButton.addEventListener("click", toggleTextToSpeech);
}

function toggleTextToSpeech() {
    ttsOnClickEnabled = !ttsOnClickEnabled;
    if (ttsButton) {
        ttsButton.textContent = ttsOnClickEnabled ? "Disable" : "Enable";
    }
    document.body.classList.toggle("tts-enabled", ttsOnClickEnabled);
}

document.addEventListener("click", (event) => {
    if (ttsOnClickEnabled) {
        const target = event.target;
        if (isTextReadable(target)) {
            speakText(target.innerText.trim());
        } else if (isInputWithPlaceholder(target)) {
            speakText(`Input field: ${target.placeholder.trim()}`);
            highlightElement(target);
        }
    }
});

function isTextReadable(target) {
    return ["BUTTON", "A", "H1", "H2", "H3", "H4", "H5", "H6", "P"].includes(target.tagName);
}

function isInputWithPlaceholder(target) {
    return ["INPUT", "TEXTAREA"].includes(target.tagName) && target.placeholder.trim();
}

function speakText(text) {
    if (text) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 1;
        utterance.pitch = 1;
        utterance.volume = 1;
        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(utterance);
    }
}

function highlightElement(element) {
    element.classList.add("tts-highlight");
    setTimeout(() => element.classList.remove("tts-highlight"), 1500);
}

// Apply Saved States on Page Load
window.addEventListener("load", () => {
    if (localStorage.getItem("darkMode") === "enabled") {
        document.body.classList.add("dark-mode");
    }

    if (localStorage.getItem("highContrast") === "enabled") {
        document.body.classList.add("high-contrast-mode");
    }

    const savedFontSize = localStorage.getItem("fontSize");
    if (savedFontSize) {
        currentFontSize = parseInt(savedFontSize, 10);
        document.body.style.fontSize = `${currentFontSize}px`;
    }

    // Synchronize all states
    updateThemeAndLogo();
    synchronizeHighContrastState();
});
