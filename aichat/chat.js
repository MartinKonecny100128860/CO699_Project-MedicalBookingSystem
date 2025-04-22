// /* CODE TO HANDLE AI CHAT POPUP */

// Function to close the chat container
function closeChat() {
    document.getElementById('chat-container').style.display = 'none'; // Hides the chat container
}

// Load the chat interface dynamically into the placeholder on page load
window.onload = function () {
    const chatPlaceholder = document.getElementById('chat-placeholder');
    fetch("../aichat/chat.html") // ✅ CORRECT
        .then((response) => response.text())
        .then((html) => {
            chatPlaceholder.innerHTML = html; // Load chat interface
            initializeChat(); // Initialize chat functionalities
        })
        .catch((error) => console.error('Error loading the chat:', error));
};

// /* CODE TO INITIALIZE CHAT FUNCTIONALITY */

// Function to initialize chat functionality
function initializeChat() {
    const chatIcon = document.getElementById('chat-icon'); // Icon to toggle chat visibility
    const chatContainer = document.getElementById('chat-container'); // Chat container
    const notificationBadge = document.getElementById('notification-badge'); // Notification badge
    const chatInput = document.getElementById('chat-input'); // Chat input field

    // Toggle chat visibility when the chat icon is clicked
    chatIcon.addEventListener('click', () => {
        const isHidden = chatContainer.style.display === 'none';
        chatContainer.style.display = isHidden ? 'block' : 'none'; // Toggle visibility
        if (isHidden) notificationBadge.style.display = 'none'; // Hide notification badge
    });

    // Handle message submission when pressing Enter in the input field
    chatInput.addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            const inputText = chatInput.value.trim();
            if (inputText) {
                sendMessage(inputText); // Send the user's message
                chatInput.value = ''; // Clear the input field
            }
        }
    });
}

// /* CODE TO HANDLE MESSAGES */

// Function to send a user message and handle the bot's response
function sendMessage(text) {
    const messagesContainer = document.getElementById('messages'); // Messages container

    // Display the user's message
    appendMessage(text, true);

    // Scroll to the latest message
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    // Add typing indicator for the bot
    const typingBubble = appendMessage('', false, true); // Add a typing bubble

    // Fetch bot's response from the server
    fetch('aichat/chatgpt_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text }) // Send user message as JSON
    })
        .then((response) => response.json())
        .then((data) => {
            // Remove the typing bubble
            typingBubble.remove();

            // Display the bot's reply with typing animation
            animateBotResponse(data.reply || 'Sorry, I couldn’t understand that.');
        })
        .catch((error) => {
            console.error('Error:', error);
            typingBubble.remove(); // Remove typing indicator on error
            appendMessage('Sorry, something went wrong.', false); // Display error message
        });
}

// Function to append a message bubble
function appendMessage(text, isUser = false, isTyping = false) {
    const messagesContainer = document.getElementById('messages');
    const messageDiv = document.createElement('div'); // Create message container
    messageDiv.style.display = 'flex';
    messageDiv.style.justifyContent = isUser ? 'flex-end' : 'flex-start';
    messageDiv.style.marginBottom = '4px';

    const bubble = document.createElement('div'); // Create the message bubble
    bubble.style.padding = '10px 20px';
    bubble.style.borderRadius = '20px';
    bubble.style.margin = '5px';
    bubble.style.color = isUser ? 'white' : '#495057';
    bubble.style.backgroundColor = isUser ? '#007aff' : '#e5e5ea';
    bubble.style.maxWidth = '80%';
    bubble.style.wordWrap = 'break-word';
    if (!isTyping) bubble.textContent = text; // Set text for non-typing bubbles

    messageDiv.appendChild(bubble);
    messagesContainer.appendChild(messageDiv);

    // Scroll to the latest message
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    return messageDiv;
}

// Function to animate the bot's response (simulates typing)
function animateBotResponse(replyText) {
    const messagesContainer = document.getElementById('messages');
    const botMessage = appendMessage('', false); // Create a bot message bubble

    let charIndex = 0; // Typing effect for bot response
    const typingEffect = setInterval(() => {
        if (charIndex < replyText.length) {
            botMessage.children[0].textContent += replyText[charIndex];
            charIndex++;
        } else {
            clearInterval(typingEffect); // Stop animation after full response
        }
    }, 50); // Typing speed

    // Scroll to the latest message
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

