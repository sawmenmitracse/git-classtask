<?php
require_once 'db.php';

header('Content-Type: application/json');

$raw_post_data = file_get_contents("php://input");
error_log("log_challenge_completion.php: Raw POST data=" . var_export($raw_post_data, true));
error_log("log_challenge_completion.php: PHP version=" . phpversion() . ", POST array=" . var_export($_POST, true));

$data = json_decode($raw_post_data, true);
$child_id = $data['child_id'] ?? '';
$challenge_id = $data['challenge_id'] ?? '';
$stars = $data['stars'] ?? '';
$csrf_token = $data['csrf_token'] ?? '';

error_log("log_challenge_completion.php: Parsed - child_id=" . var_export($child_id, true) . ", challenge_id=" . var_export($challenge_id, true) . ", stars=" . var_export($stars, true) . ", csrf_token=" . ($csrf_token ? 'provided' : 'missing') . ", session_parent_id=" . ($_SESSION['parent_id'] ?? 'not set') . ", session_csrf_token=" . ($_SESSION['csrf_token'] ?? 'not set') . ", session_id=" . session_id());

if (!isset($child_id) || $child_id === '' || !is_numeric($child_id)) {
    error_log("log_challenge_completion.php: Invalid or missing child_id=" . var_export($child_id, true));
    echo json_encode(["status" => "error", "message" => "Invalid child_id"]);
    exit;
}
if (!is_numeric($challenge_id)) {
    error_log("log_challenge_completion.php: Invalid challenge_id=" . var_export($challenge_id, true));
    echo json_encode(["status" => "error", "message" => "Invalid challenge_id"]);
    exit;
}
if (!is_numeric($stars) || $stars < 0) {
    error_log("log_challenge_completion.php: Invalid stars=" . var_export($stars, true));
    echo json_encode(["status" => "error", "message" => "Invalid stars"]);
    exit;
}
if (empty($csrf_token)) {
    error_log("log_challenge_completion.php: CSRF token missing");
    echo json_encode(["status" => "error", "message" => "CSRF token missing"]);
    exit;
}
if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    error_log("log_challenge_completion.php: Invalid CSRF token for child_id=$child_id, received=$csrf_token, expected=" . ($_SESSION['csrf_token'] ?? 'not set'));
    echo json_encode(["status" => "error", "message" => "Invalid CSRF token"]);
    exit;
}
if (!isset($_SESSION['parent_id'])) {
    error_log("log_challenge_completion.php: Session parent_id not set");
    echo json_encode(["status" => "error", "message" => "Session expired"]);
    exit;
}

$child_id = (int)$child_id;
$challenge_id = (int)$challenge_id;
$stars = (int)$stars;

$conn = getDbConnection();

$stmt = $conn->prepare("SELECT id, name FROM children WHERE id = ? AND parent_id = ?");
$stmt->bind_param("ii", $child_id, $_SESSION['parent_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    error_log("log_challenge_completion.php: Child not found for child_id=$child_id, parent_id=" . $_SESSION['parent_id']);
    echo json_encode(["status" => "error", "message" => "Child not found"]);
    $stmt->close();
    $conn->close();
    exit;
}
$child = $result->fetch_assoc();
error_log("log_challenge_completion.php: Child found - id=$child_id, name=" . $child['name']);
$stmt->close();

$stmt = $conn->prepare("SELECT id FROM daily_challenges WHERE id = ?");
$stmt->bind_param("i", $challenge_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    error_log("log_challenge_completion.php: Challenge not found for challenge_id=$challenge_id");
    echo json_encode(["status" => "error", "message" => "Challenge not found"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO challenge_progress (child_id, challenge_id, stars_earned) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $child_id, $challenge_id, $stars);
if (!$stmt->execute()) {
    error_log("log_challenge_completion.php: Failed to insert into challenge_progress: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Failed to log challenge progress"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO stars (child_id, earned_stars) VALUES (?, ?) ON DUPLICATE KEY UPDATE earned_stars = earned_stars + ?");
$stmt->bind_param("iii", $child_id, $stars, $stars);
if (!$stmt->execute()) {
    error_log("log_challenge_completion.php: Failed to update stars: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Failed to update stars"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

$stmt = $conn->prepare("UPDATE children SET stars = stars + ? WHERE id = ?");
$stmt->bind_param("ii", $stars, $child_id);
if (!$stmt->execute()) {
    error_log("log_challenge_completion.php: Failed to update children.stars: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Failed to update child stars"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

$conn->close();
echo json_encode(["status" => "success"]);
?>