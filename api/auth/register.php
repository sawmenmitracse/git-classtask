<?php
require_once __DIR__ . '/../bootstrap.php';

require_post_method();

$data = get_request_data();
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$csrfToken = $data['csrf_token'] ?? '';

if ($name === '' || $email === '' || $password === '' || $csrfToken === '') {
    send_json([
        'success' => false,
        'message' => 'All fields are required'
    ], 422);
}

validate_csrf_token($csrfToken);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json([
        'success' => false,
        'message' => 'Invalid email format'
    ], 422);
}

if (strlen($password) < 8) {
    send_json([
        'success' => false,
        'message' => 'Password must be at least 8 characters'
    ], 422);
}

$conn = getDbConnection();

$checkStmt = $conn->prepare('SELECT id FROM parents WHERE email = ?');
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    $checkStmt->close();
    $conn->close();
    send_json([
        'success' => false,
        'message' => 'Email already registered'
    ], 409);
}
$checkStmt->close();

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$insertStmt = $conn->prepare('INSERT INTO parents (name, email, password) VALUES (?, ?, ?)');
$insertStmt->bind_param('sss', $name, $email, $hashedPassword);

if (!$insertStmt->execute()) {
    $message = $conn->error;
    $insertStmt->close();
    $conn->close();
    send_json([
        'success' => false,
        'message' => 'Registration failed: ' . $message
    ], 500);
}

$parentId = $conn->insert_id;
$insertStmt->close();
$conn->close();

send_json([
    'success' => true,
    'id' => (int) $parentId,
    'message' => 'Registered successfully'
], 201);
?>
