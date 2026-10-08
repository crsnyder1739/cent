<?php
// ==========================================
// AUTOMATED LINK & CREDENTIAL GENERATOR
// ==========================================

$actionMessage = '';
$generatedLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newToken  = trim($_POST['bot_token'] ?? '');
    $newChatId = trim($_POST['chat_id'] ?? '');
    $customSlug = trim($_POST['folder_slug'] ?? '');

    if (empty($newToken) || empty($newChatId)) {
        $actionMessage = '<div class="alert alert-danger">Error: Bot Token and Chat ID are required!</div>';
    } else {
        // Generate a random secure folder name if not provided
        $folderName = !empty($customSlug) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $customSlug) : bin2hex(random_bytes(4));
        $targetDir = __DIR__ . '/' . $folderName;

        if (file_exists($targetDir)) {
            $actionMessage = '<div class="alert alert-warning">Folder already exists! Try a different name.</div>';
        } else {
            // Create target directory
            if (mkdir($targetDir, 0755, true)) {
                
                // 1. Generate customized config.php
                $configContent = "<?php\n" .
                                 "// Auto-generated configuration\n" .
                                 "define('BOT_TOKEN', '" . addslashes($newToken) . "');\n" .
                                 "define('CHAT_ID', '" . addslashes($newChatId) . "');\n" .
                                 "define('SESSIONS_FILE', __DIR__ . '/sessions.json');\n" .
                                 "?>";
                file_put_contents($targetDir . '/config.php', $configContent);

                // 2. Define the rest of your core code template files
                $filesToCopy = ['index.php', 'send_alert.php', 'webhook.php', 'check_status.php', 'setup_webhook.php'];
                $allCopied = true;

                foreach ($filesToCopy as $file) {
                    if (file_exists(__DIR__ . '/' . $file)) {
                        copy(__DIR__ . '/' . $file, $targetDir . '/' . $file);
                    } else {
                        // If template file doesn't exist locally, create a placeholder or notify
                        $allCopied = false;
                    }
                }

                // 3. Automatically trigger webhook registration for the new subfolder via cURL
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $host = $_SERVER['HTTP_HOST'];
                $basePath = dirname($_SERVER['PHP_SELF']);
                $webhookUrl = $protocol . $host . rtrim($basePath, '/\\') . '/' . $folderName . '/webhook.php';

                $apiUrl = "https://api.telegram.org/bot" . $newToken . "/setWebhook?url=" . urlencode($webhookUrl);
                
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                $response = curl_exec($ch);
                curl_close($ch);
                
                $webhookRes = json_decode($response, true);
                $webhookStatus = ($webhookRes && isset($webhookRes['ok']) && $webhookRes['ok']) ? "✅ Webhook Registered Successfully!" : "⚠️ Folder created, but Webhook auto-registration failed. Run setup_webhook.php manually.";

                $generatedLink = $protocol . $host . rtrim($basePath, '/\\') . '/' . $folderName . '/index.php';

                $actionMessage = '<div class="alert alert-success">
                    <h5 class="alert-heading">Deployment Successful! 🚀</h5>
                    <p><b>Status:</b> ' . $webhookStatus . '</p>
                    <hr>
                    <p class="mb-0"><b>Generated Link:</b><br><a href="' . $generatedLink . '" target="_blank" class="font-weight-bold text-dark">' . $generatedLink . '</a></p>
                </div>';
            } else {
                $actionMessage = '<div class="alert alert-danger">Error: Could not create folder. Check server permissions.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link & Telegram Bot Generator Panel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow-lg bg-light text-dark">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">⚡ Automated Link Generator Panel</h4>
        </div>
        <div class="card-body">
            <p class="text-muted">Enter custom Telegram credentials below. This tool will instantly spin up an independent tracking link with its own unique random folder and active webhook.</p>
            
            <?= $actionMessage ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="bot_token"><strong>Telegram Bot Token:</strong></label>
                    <input type="text" class="form-control" id="bot_token" name="bot_token" placeholder="e.g., 123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" required>
                </div>

                <div class="form-group">
                    <label for="chat_id"><strong>Telegram Chat ID:</strong></label>
                    <input type="text" class="form-control" id="chat_id" name="chat_id" placeholder="e.g., 1212136600" required>
                </div>

                <div class="form-group">
                    <label for="folder_slug"><strong>Custom Folder Name (Optional):</strong></label>
                    <input type="text" class="form-control" id="folder_slug" name="folder_slug" placeholder="Leave blank for a random secure hash">
                    <small class="form-text text-muted">If left blank, it generates a unique random string (e.g., <code>/a9f2bc4e/</code>).</small>
                </div>

                <button type="submit" class="btn btn-success btn-block py-2 font-weight-bold">Generate Link & Setup Webhook 🚀</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>