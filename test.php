<?php
// --- CONFIGURATION ---
$botToken = "8496823054:AAEVs6LRJ9EpwiewkzHddn4X26Ki2v-PTg4"; // e.g., 123456789:ABCDefGhIJKlmNoPQRstUVwxyZ
$chatId   = "1212136600";   // e.g., 987654321 or @yourchannel
$message  = "Hello! This is a test message from my cPanel server.";
// ---------------------

$url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";

$data = [
    'chat_id' => $chatId,
    'text'    => $message
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Output results for debugging
echo "<h2>Telegram Test Results</h2>";
echo "HTTP Status Code: <b>" . $httpCode . "</b><br>";

if ($curlError) {
    echo "<p style='color:red;'>cURL Error: " . htmlspecialchars($curlError) . "</p>";
    echo "<p><em>This usually means your server's firewall (like CSF) is blocking outbound traffic to port 443 or DNS resolution is failing.</em></p>";
} else {
    echo "API Response: <pre>" . htmlspecialchars($response) . "</pre>";
    if ($httpCode == 200) {
        echo "<p style='color:green;'><b>Success!</b> Check your Telegram. The message should have arrived.</p>";
    } else {
        echo "<p style='color:orange;'><b>Warning:</b> Telegram received the request but returned an error. Check if your Bot Token and Chat ID are correct.</p>";
    }
}
?>