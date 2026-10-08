<?php
require_once 'config.php';
header('Content-Type: application/json');
$sid = $_GET['sid'] ?? '';

if ($sid && file_exists(SESSIONS_FILE)) {
    $sessions = json_decode(file_get_contents(SESSIONS_FILE), true);
    
    if (isset($sessions[$sid])) {
        $status = $sessions[$sid];
        
        // CRITICAL FIX: Delete or reset this session status immediately 
        // after reading it so it never loops or auto-triggers again!
        unset($sessions[$sid]);
        file_put_contents(SESSIONS_FILE, json_encode($sessions));

        echo json_encode(['status' => $status]);
        exit;
    }
}

echo json_encode(['status' => 'waiting']);
?>