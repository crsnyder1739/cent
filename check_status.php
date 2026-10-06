<?php
require_once 'config.php';
header('Content-Type: application/json');
$sid = $_GET['sid'] ?? '';

if ($sid && file_exists(SESSIONS_FILE)) {
    $data = json_decode(file_get_contents(SESSIONS_FILE), true);
    if (isset($data[$sid])) {
        echo json_encode(['status' => $data[$sid]]);
        exit;
    }
}
echo json_encode(['status' => 'waiting']);
?>