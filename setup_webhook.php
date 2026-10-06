<?php
require_once 'config.php';

// Auto-detect the current public URL for webhook.php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
$webhookUrl = $protocol . $host . rtrim($path, '/\\') . '/webhook.php';

// Register webhook with Telegram
$apiUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/setWebhook?url=" . urlencode($webhookUrl);
$response = @file_get_contents($apiUrl);
$result = json_decode($response, true);

// Get webhook status info
$infoUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/getWebhookInfo";
$infoResponse = @file_get_contents($infoUrl);
$infoResult = json_decode($infoResponse, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Webhook Setup Status</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 650px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Telegram Webhook Setup</h4>
        </div>
        <div class="card-body">
            <?php if (isset($result['ok']) && $result['ok']): ?>
                <div class="alert alert-success">
                    <strong>Success!</strong> Webhook configured successfully. You are now good to go! 🚀
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong> Could not register webhook. Make sure you are using an HTTPS public URL (e.g., Ngrok).
                </div>
            <?php endif; ?>

            <p class="mb-2"><strong>Target Webhook URL:</strong><br>
                <code><?= htmlspecialchars($webhookUrl) ?></code>
            </p>
            <hr>
            <h5 class="mb-2">Telegram Webhook Info:</h5>
            <pre class="bg-dark text-white p-3 rounded" style="font-size: 12px;"><?= htmlspecialchars(print_r($infoResult, true)) ?></pre>
            
            <a href="index.php" class="btn btn-secondary btn-block mt-3">Proceed to Login Page</a>
        </div>
    </div>
</div>
</body>
</html>