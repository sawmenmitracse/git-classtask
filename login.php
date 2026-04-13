<?php
require_once 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$csrf_token = $data['csrf_token'] ?? '';

$response = [];

if (empty($email) || empty($password) || empty($csrf_token)) {
    error_log("login.php: Missing fields - email=" . ($email ? 'provided' : 'missing') . ", password=" . ($password ? 'provided' : 'missing') . ", csrf_token=" . ($csrf_token ? 'provided' : 'missing'));
    $response = ["success" => false, "message" => "All fields are required"];
    echo json_encode($response);
    exit;
}

if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    error_log("login.php: Invalid CSRF token, received=$csrf_token, expected=" . ($_SESSION['csrf_token'] ?? 'not set'));
    $response = ["success" => false, "message" => "Invalid CSRF token"];
    echo json_encode($response);
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT id, name, password FROM parents WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        $existing_csrf_token = $_SESSION['csrf_token'] ?? '';
        session_regenerate_id(true);
        $_SESSION['parent_id'] = $row['id'];
        $_SESSION['parent_name'] = $row['name'];
        if ($existing_csrf_token) {
            $_SESSION['csrf_token'] = $existing_csrf_token;
        }
        error_log("login.php: Login successful for parent_id={$row['id']}, session_id=" . session_id());
        $response = ["success" => true, "name" => $row['name'], "id" => $row['id']];
    } else {
        error_log("login.php: Invalid password for email=$email");
        $response = ["success" => false, "message" => "Invalid password"];
    }
} else {
    error_log("login.php: User not found for email=$email");
    $response = ["success" => false, "message" => "User not found"];
}

$stmt->close();
$conn->close();
echo json_encode($response);
?>