<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['parent_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$child_id = $_POST['childId'] ?? '';
if (empty($child_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Child ID not provided']);
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT name FROM children WHERE id = ? AND parent_id = ?");
$stmt->bind_param("ii", $child_id, $_SESSION['parent_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $child = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'childName' => $child['name']]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Child not found']);
}
$stmt->close();
$conn->close();
?>