<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['parent_id'])) {
    error_log("save_child.php: Session parent_id not set");
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$parent_id = $_SESSION['parent_id'];
$data = json_decode(file_get_contents("php://input"), true);
$child_name = $data['childName'] ?? '';
$screen_time = $data['screenTime'] ?? '';
$csrf_token = $data['csrf_token'] ?? '';

if (empty($child_name) || empty($screen_time) || !is_numeric($screen_time) || $screen_time < 0 || empty($csrf_token)) {
    error_log("save_child.php: Invalid input - child_name=" . ($child_name ? 'provided' : 'missing') . ", screen_time=" . var_export($screen_time, true) . ", csrf_token=" . ($csrf_token ? 'provided' : 'missing'));
    echo json_encode(["status" => "error", "message" => "Invalid or missing data"]);
    exit;
}

if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    error_log("save_child.php: Invalid CSRF token, received=$csrf_token, expected=" . ($_SESSION['csrf_token'] ?? 'not set'));
    echo json_encode(["status" => "error", "message" => "Invalid CSRF token"]);
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT id FROM children WHERE parent_id = ? AND name = ?");
$stmt->bind_param("is", $parent_id, $child_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $child = $result->fetch_assoc();
    $child_id = $child['id'];
    $update_stmt = $conn->prepare("UPDATE children SET screen_time = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $screen_time, $child_id);
    $update_stmt->execute();
    $update_stmt->close();
} else {
    $insert_stmt = $conn->prepare("INSERT INTO children (parent_id, name, screen_time, stars) VALUES (?, ?, ?, 0)");
    $insert_stmt->bind_param("isi", $parent_id, $child_name, $screen_time);
    $insert_stmt->execute();
    $child_id = $conn->insert_id;
    $insert_stmt->close();
    
    $stars_stmt = $conn->prepare("INSERT INTO stars (child_id, earned_stars) VALUES (?, 0)");
    $stars_stmt->bind_param("i", $child_id);
    $stars_stmt->execute();
    $stars_stmt->close();
}

$stmt->close();
$conn->close();
echo json_encode(["status" => "success", "child_id" => $child_id, "child_name" => $child_name]);
?>