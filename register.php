<?php
require_once 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$csrf_token = $data['csrf_token'] ?? '';

$response = [];

if (empty($name) || empty($email) || empty($password) || empty($csrf_token)) {
    $response = ["success" => false, "message" => "All fields are required"];
    echo json_encode($response);
    exit;
}

if ($csrf_token !== $_SESSION['csrf_token']) {
    $response = ["success" => false, "message" => "Invalid CSRF token"];
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response = ["success" => false, "message" => "Invalid email format"];
    echo json_encode($response);
    exit;
}

if (strlen($password) < 8) {
    $response = ["success" => false, "message" => "Password must be at least 8 characters"];
    echo json_encode($response);
    exit;
}

$conn = getDbConnection();

// Check for duplicate email
$stmt = $conn->prepare("SELECT id FROM parents WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $response = ["success" => false, "message" => "Email already registered"];
    echo json_encode($response);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO parents (name, email, password) VALUES (?, ?, ?)");
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$stmt->bind_param("sss", $name, $email, $hashedPassword);

if ($stmt->execute()) {
    $response = ["success" => true];
} else {
    $response = ["success" => false, "message" => "Error: " . $conn->error];
}

$stmt->close();
$conn->close();
echo json_encode($response);
?>