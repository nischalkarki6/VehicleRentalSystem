<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['message']) || trim($input['message']) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

require_once __DIR__ . '/config/config.php';

$apiKey   = getenv('GROQ_API_KEY') ?: ($_ENV['GROQ_API_KEY'] ?? '');
$endpoint = 'https://api.groq.com/openai/v1/chat/completions';

if ($apiKey === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Chatbot API key is not configured.']);
    exit;
}

$history  = isset($input['history']) && is_array($input['history']) ? $input['history'] : [];
$userMsg  = trim($input['message']);

$messages = [];

// System context
$systemPrompt = "You are a helpful assistant for DriveEase, a premium vehicle rental company in Nepal. "
    . "You help customers with vehicle bookings, pricing, fleet information, travel tips in Nepal, "
    . "and general support. Be friendly, concise, and professional. "
    . "When helping with a booking, collect the needed booking details, summarize them, and ask the customer to confirm. "
    . "If the customer says yes, ask one final second confirmation before treating the booking as confirmed or directing them to complete it. "
    . "If asked about something unrelated to vehicle rental or travel, politely redirect the conversation.";

$messages[] = [
    'role'    => 'system',
    'content' => $systemPrompt
];

$messages[] = [
    'role'    => 'assistant',
    'content' => 'Understood! I\'m the DriveEase assistant. How can I help you today?'
];

foreach ($history as $turn) {
    if (isset($turn['role'], $turn['text'])) {
        $messages[] = [
            'role'    => $turn['role'] === 'user' ? 'user' : 'assistant',
            'content' => $turn['text']
        ];
    }
}

$messages[] = [
    'role'    => 'user',
    'content' => $userMsg
];

$payload = json_encode([
    'model'       => 'llama-3.3-70b-versatile',
    'messages'    => $messages,
    'temperature' => 0.7,
    'max_tokens'  => 512,
]);

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_TIMEOUT        => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection error: ' . $curlError]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode !== 200 || !isset($data['choices'][0]['message']['content'])) {
    http_response_code(500);
    $errMsg = $data['error']['message'] ?? 'Failed to get response from AI';
    echo json_encode(['error' => $errMsg]);
    exit;
}

$reply = $data['choices'][0]['message']['content'];
echo json_encode(['reply' => $reply]);
