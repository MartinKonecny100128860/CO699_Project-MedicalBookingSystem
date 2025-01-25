// Function to close the chat container
function closeChat() {
    document.getElementById('chat-container').style.display = 'none'; // Hides the chat container when the close button is clicked
}

// Load the chat interface dynamically into the placeholder on page load
window.onload = function () {
    const chatPlaceholder = document.getElementById('chat-placeholder'); // Placeholder for loading the chat UI
    fetch('chat.html') // Fetch the chat HTML file
        .then((response) => response.text())
        .then((html) => {
            chatPlaceholder.innerHTML = html; // Insert the fetched HTML into the placeholder
            initializeChat(); // Initialize chat functionalities (event listeners, etc.)
        })
        .catch((error) => console.error('Error loading the chat:', error)); // Handle errors in loading the chat
};

// Function to initialize chat functionality
function initializeChat() {
    // Toggle chat container visibility when the chat icon is clicked
    document.getElementById('chat-icon').onclick = function () {
        const chatContainer = document.getElementById('chat-container'); // Main chat container
        const badge = document.getElementById('notification-badge'); // Notification badge
        if (chatContainer.style.display === 'none') {
            chatContainer.style.display = 'block'; // Show the chat container
            badge.style.display = 'none'; // Hide the notification badge
        } else {
            chatContainer.style.display = 'none'; // Hide the chat container
        }
    };

    // Handle message submission when the Enter key is pressed in the input field
    document.getElementById('chat-input').addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent default form submission
            const input = this.value.trim(); // Get the input value and remove extra spaces
            if (input) {
                sendMessage(input); // Send the message
                this.value = ''; // Clear the input field after sending the message
            }
        }
    });
}

// Function to send a user message and fetch the bot's response
function sendMessage(text) {
    const messagesContainer = document.getElementById('messages'); // Container for displaying messages

    // Helper function to create message bubbles (for user and bot messages)
    function createMessageBubble(text, isUser) {
        const messageDiv = document.createElement('div'); // Container for the message bubble
        messageDiv.style.width = '100%';
        messageDiv.style.display = 'flex';
        messageDiv.style.justifyContent = isUser ? 'flex-end' : 'flex-start'; // Align user messages to the right and bot messages to the left
        messageDiv.style.marginBottom = '4px';

        const bubble = document.createElement('div'); // The actual message bubble
        bubble.textContent = text;
        bubble.style.padding = '10px 20px';
        bubble.style.borderRadius = '20px';
        bubble.style.margin = '5px';
        bubble.style.color = isUser ? 'white' : '#495057'; // User messages are white; bot messages are dark gray
        bubble.style.backgroundColor = isUser ? '#007aff' : '#e5e5ea'; // Different background colors for user and bot messages
        bubble.style.maxWidth = '80%'; // Limit message bubble width
        bubble.style.wordWrap = 'break-word'; // Ensure long messages break into multiple lines

        messageDiv.appendChild(bubble); // Append the bubble to the container
        return messageDiv;
    }

    // Display the user's message
    const userMessage = createMessageBubble(text, true); // Create a user message bubble
    messagesContainer.appendChild(userMessage); // Append the message to the messages container

    // Scroll to the latest message
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    // Simulate a typing indicator while waiting for the bot's response
    let typingBubble = createMessageBubble('', false); // Temporary bubble for the bot's typing indicator
    messagesContainer.appendChild(typingBubble);

    // Fetch the bot's response from the server
    fetch('chatgpt_api.php', {
        method: 'POST', // Send the data as a POST request
        headers: { 'Content-Type': 'application/json' }, // Specify JSON content type
        body: JSON.stringify({ message: text }) // Send the user's message to the server
    })
        .then((response) => response.json()) // Parse the server's response as JSON
        .then((data) => {
            // Remove the typing bubble before displaying the bot's response
            messagesContainer.removeChild(typingBubble);

            // Simulate a live typing effect for the bot's response
            const botMessage = createMessageBubble('', false); // Create a bot message bubble
            messagesContainer.appendChild(botMessage);
            const replyText = data.reply; // Get the bot's reply
            let j = 0;
            const typingEffect = setInterval(() => {
                if (j < replyText.length) {
                    botMessage.children[0].textContent += replyText[j]; // Append one character at a time
                    j++;
                } else {
                    clearInterval(typingEffect); // Stop the typing effect when the reply is complete
                }
            }, 50); // Typing speed (milliseconds per character)

            // Scroll to the latest message after the bot's reply
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        })
        .catch((error) => {
            // Handle errors (e.g., server is unavailable)
            console.error('Error:', error);
            messagesContainer.removeChild(typingBubble); // Remove the typing indicator
            const errorMessage = createMessageBubble('Sorry, something went wrong.', false); // Display an error message
            messagesContainer.appendChild(errorMessage);
        });
}
