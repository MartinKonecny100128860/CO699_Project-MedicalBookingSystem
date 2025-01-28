<?php
header('Content-Type: application/json');

// Decode the incoming JSON payload
$data = json_decode(file_get_contents('php://input'), true);

// Check if the message is set and sanitize it
$message = isset($data['message']) ? strtolower(trim($data['message'])) : '';

// Handle empty messages
if (empty($message)) {
    echo json_encode(['reply' => 'I didn’t catch that. Could you say it again?']);
    exit;
}
// Predefined responses
$predefinedResponses = [
    "who is stronger? gemini or you?" => "If Gemini regained all his powers, he might cause me a little trouble.",
    "but would you lose?" => "NAH ID WIN 🔵🫸🫷🔴🤞🤌🫴🟣

⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⡀⠀⠀⠀⢠⡀⠀⠀⠀⠀⣀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢸⠄⠀⠀⠇⠀⠀⠀⠇⢑⠀⠀⢀⠏⢘⠀⠀⠀⠀⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⠅⡇⠀⡅⠀⠀⠄⠁⠈⠐⠉⠀⠀⠃⠀⣀⠤⢊⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⡇⠝⣀⠅⢀⠀⠀⠀⠀⠀⠀⠄⡀⠀⠀⠀⢀⠊⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⡦⡐⠁⠂⠑⠀⠀⠀⠀⠀⠀⡘⢀⠀⠀⠀⠀⡂⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢑⡀⠁⠀⠀⠀⠀⠀⠀⠀⡐⠀⡌⠀⠀⢀⡔⡀⠄⠔⠄⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠘⣓⢫⡨⢾⣻⣇⣿⡿⡅⣔⣁⡄⡁⠁⡇⡠⠊⠀⠀⠀⠀⣠⢄⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣿⣭⣮⣫⣖⣂⡑⣣⣯⡇⠷⠍⡓⡐⠋⠉⢢⠀⠀⠀⠀⣇⣏⠙⡇⣀⠤⣤⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢯⣇⣞⡯⠫⠙⠉⠑⠱⢩⢿⡄⠳⣄⠀⢀⠎⠀⠀⠀⠀⠙⣽⠀⠋⢁⡰⠃⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠸⡀⠀⠈⡋⠉⠃⠀⠀⠀⠀⢈⡗⠓⠊⠁⠀⠀⠀⠀⠀⠀⢹⣀⠌⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠳⡀⡀⠈⠦⠇⢀⢀⢂⡒⠊⠒⣬⣄⣄⣀⡦⠀⠀⠀⠀⠀⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣈⣝⣥⡀⠤⢈⠁⣀⣀⣤⣿⡿⠋⡩⠍⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠴⣝⡿⠝⢧⣇⣶⣿⠟⠿⠟⠉⠁⠀⣼⠍⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠙⣆⠀⠀⠀⠀⠋⠀⠀⠀⠒⠃⠉⠧⣄⠀⣠⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⠡⡓⠤⠀⠀⠀⠀⠀⠀⠀⠀⠄⠺⣙⠋⠩⢧⣅⡃⠁⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⣀⢴⣧⡅⢇⡀⡀⡀⠀⠀⢀⠄⠞⠄⠁⠀⠁⠥⢕⡂⡌⠓⢤⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⡄⡄⡸⡙⠁⡂⡃⡠⠠⠓⢁⢠⡔⡂⠀⠈⠀⠀⠀⠀⠀⠀⠀⠀⠘⡁⡤⠘⠔⠃⠂⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠩⢇⡪⢡⢄⠄⡃⠇⡁⢄⣀⢓⢊⠅⠀⠀⠀⠀⠀⠀⠀⠀⢀⣀⡤⠶⠓⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣮⢇⠥⣣⡵⢑⣁⣒⡒⢖⢒⡒⢆⡖⠖⠃⠆⠃⠚⠂⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀",
    "what's your name?" => "I'm your friendly Income Tracker AI!",
    "what can you do?" => "I can help you track your income, answer questions, and chat about almost anything!",
];
// Check if the message matches predefined responses
if (array_key_exists($message, $predefinedResponses)) {
    echo json_encode(['reply' => $predefinedResponses[$message], 'instant' => true]);
    exit;
}

// OpenAI API integration for dynamic responses
$apiKey = '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';
$apiUrl = 'https://api.openai.com/v1/chat/completions';

$postFields = [
    "model" => "gpt-4",
    "messages" => [["role" => "user", "content" => $message]],
    "temperature" => 0.7
];

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    echo json_encode(['reply' => 'Sorry, there was an error connecting to the API: ' . $error, 'instant' => false]);
    exit;
}

curl_close($ch);

// Decode the response from OpenAI
$responseData = json_decode($response, true);

// Validate the response
if (isset($responseData['choices'][0]['message']['content'])) {
    $reply = $responseData['choices'][0]['message']['content'];
    echo json_encode(['reply' => $reply, 'instant' => false]);
} else {
    echo json_encode(['reply' => 'Sorry, I couldn’t process that. Please try again later.', 'instant' => false]);
}
?>