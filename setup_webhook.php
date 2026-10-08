<?php
require_once 'config.php';

$actionMessage = '';
$webhookResult = null;

// Force HTTPS since Telegram requires secure webhooks
$protocol = "https://";
$host = $_SERVER['HTTP_HOST'];
$basePath = dirname($_SERVER['PHP_SELF']);
$webhookUrl = $protocol . $host . rtrim($basePath, '/\\') . '/webhook.php';

$apiUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/setWebhook?url=" . urlencode($webhookUrl);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
$response = curl_exec($ch);
curl_close($ch);

$webhookResult = json_decode($response, true);

// Get current webhook info to display status
$infoUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/getWebhookInfo";
$ch2 = curl_init($infoUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch2, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
$infoResponse = curl_exec($ch2);
curl_close($ch2);
$infoData = json_decode($infoResponse, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Webhook Setup</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 650px;">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Telegram Webhook Setup</h4>
        </div>
        <div class="card-body">
            <?php if ($webhookResult && isset($webhookResult['ok']) && $webhookResult['ok']): ?>
                <div class="alert alert-success">
                    <b>Success!</b> Webhook registered successfully over secure HTTPS.
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <b>Error!</b> Could not register webhook. Check if your domain uses a valid SSL certificate.
                </div>
            <?php endif; ?>

            <p><strong>Target Webhook URL:</strong><br>
            <span class="text-primary font-weight-bold"><?= htmlspecialchars($webhookUrl) ?></span></p>

            <hr>

            <h5 class="mt-4">Telegram Webhook Info:</h5>
            <pre class="bg-dark text-light p-3 rounded"><code><?php print_r($infoData); ?></code></pre>

            <a href="index.php" class="btn btn-secondary btn-block mt-4">Proceed to Login Page</a>
        </div>
    </div>
</div>
</body>
</html>
