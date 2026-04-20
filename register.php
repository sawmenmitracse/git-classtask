<?php
require_once __DIR__ . '/../bootstrap.php';

require_post_method();

$data = get_request_data();
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$csrfToken = $data['csrf_token'] ?? '';

if ($email === '' || $password === '' || $csrfToken === '') {
    send_json([
        'success' => false,
        'message' => 'All fields are required'
    ], 422);
}

validate_csrf_token($csrfToken);

$conn = getDbConnection();
$stmt = $conn->prepare('SELECT id, name, password FROM parents WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    send_json([
        'success' => false,
        'message' => 'User not found'
    ], 404);
}

$parent = $result->fetch_assoc();

if (!password_verify($password, $parent['password'])) {
    $stmt->close();
    $conn->close();
    send_json([
        'success' => false,
        'message' => 'Invalid password'
    ], 401);
}

$existingCsrfToken = $_SESSION['csrf_token'] ?? '';
session_regenerate_id(true);
$_SESSION['parent_id'] = (int) $parent['id'];
$_SESSION['parent_name'] = $parent['name'];
if ($existingCsrfToken !== '') {
    $_SESSION['csrf_token'] = $existingCsrfToken;
}

$stmt->close();
$conn->close();

send_json([
    'success' => true,
    'id' => (int) $parent['id'],
    'name' => $parent['name']
]);
?>
