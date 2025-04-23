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
        "what's your name?" => "I'm your friendly Medical Chatbot!",
        "what can you do?" => "I can help you with your medical sysmptoms before you see your doctor",
    ];
    // Check if the message matches predefined responses
    if (array_key_exists($message, $predefinedResponses)) {
        echo json_encode(['reply' => $predefinedResponses[$message], 'instant' => true]);
        exit;
    }

    // OpenAI API integration for dynamic responses - OPEN AI blocked the API Key
    // because i posted the whole source code on github, not sure how ill go about it
    $apiKey = 'sk-proj-';
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
    
    // Make API call using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Handle cURL or API-level errors
    if (!$response) {
        echo json_encode(['reply' => 'Error contacting OpenAI. Please try again.', 'instant' => false]);
        exit;
    }
    
    $responseData = json_decode($response, true);
    
    // Show OpenAI error message if present
    if (isset($responseData['error'])) {
        echo json_encode(['reply' => 'OpenAI Error: ' . $responseData['error']['message'], 'instant' => false]);
        exit;
    }
    
    // Return successful reply
    if ($httpCode === 200 && isset($responseData['choices'][0]['message']['content'])) {
        echo json_encode([
            'reply' => $responseData['choices'][0]['message']['content'],
            'instant' => false
        ]);
    } else {
        echo json_encode(['reply' => 'Sorry, I couldn’t process that. Please try again later.', 'instant' => false]);
    }
    ?>