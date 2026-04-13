<?php
require_once 'db.php';

header('Content-Type: application/json');
$token = generateCsrfToken();
error_log("get_csrf_token.php: CSRF Token served: " . $token . ", session_id=" . session_id());
echo json_encode(['csrf_token' => $token]);
?>