<?php
require_once 'db.php';

$csrf_token = $_SESSION['csrf_token'] ?? generateCsrfToken();
error_log("daily_challenges.php: CSRF Token used: " . $csrf_token . ", session_id=" . session_id() . ", session_parent_id=" . ($_SESSION['parent_id'] ?? 'not set'));

if (!isset($_SESSION['parent_id'])) {
    error_log("daily_challenges.php: Session parent_id not set");
    header("Location: /pre_edu_platform/index.html");
    exit;
}

$child_id = $_GET['childId'] ?? '';
error_log("daily_challenges.php: Received childId from GET=" . var_export($child_id, true));
if (!isset($child_id) || $child_id === '' || !is_numeric($child_id)) {
    error_log("daily_challenges.php: Invalid or missing childId=" . var_export($child_id, true));
    header("Location: /pre_edu_platform/index.html");
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT id, name FROM children WHERE id = ? AND parent_id = ?");
$stmt->bind_param("ii", $child_id, $_SESSION['parent_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    error_log("daily_challenges.php: No child found for childId=$child_id, parent_id=" . $_SESSION['parent_id']);
    $stmt->close();
    $conn->close();
    header("Location: /pre_edu_platform/index.html");
    exit;
}
$child = $result->fetch_assoc();
$child_name = $child['name'];
$stmt->close();

$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT id, title, description FROM daily_challenges WHERE date = ? OR date IS NULL LIMIT 1");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$challenge = $result->num_rows > 0 ? $result->fetch_assoc() : null;
if (!$challenge) {
    $result = $conn->query("SELECT id, title, description FROM daily_challenges WHERE date IS NULL ORDER BY RAND() LIMIT 1");
    $challenge = $result->fetch_assoc();
}
$stmt->close();
$conn->close();

$image_path = '';
$image_exists = false;
if ($challenge) {
    $base_path = __DIR__ . '/assets/' . $challenge['id'];
    $extensions = ['.png', '.PNG', '.jpg', '.JPG'];
    foreach ($extensions as $ext) {
        $test_path = "assets/{$challenge['id']}$ext";
        if (file_exists($base_path . $ext)) {
            $image_path = $test_path;
            $image_exists = true;
            break;
        }
    }
    if (!$image_exists) {
        error_log("daily_challenges.php: Image not found for challenge_id={$challenge['id']} at " . __DIR__ . '/assets/');
    }
}
error_log("daily_challenges.php: child_id=" . var_export($child_id, true) . ", challenge_id=" . ($challenge['id'] ?? 'none') . ", child_name=" . $child_name);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daily Challenge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous" />
    <style>
        body {
            background-color: #fef9f4;
            font-family: 'Comic Sans MS', cursive;
        }
        .challenge-card {
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 30px;
            background-color: #fff;
            margin-top: 30px;
        }
        .challenge-img {
            width: 100%;
            max-width: 300px;
            margin: 20px auto;
            display: block;
        }
        .btn-back {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: background-color 0.3s ease;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <h2 class="mt-5">🎯 Today's Daily Challenge!</h2>
        <div class="challenge-card">
            <?php if ($challenge): ?>
                <h4><?= htmlspecialchars($challenge['title']) ?></h4>
                <p><?= htmlspecialchars($challenge['description']) ?></p>
                <?php if ($image_exists): ?>
                    <img src="/pre_edu_platform/<?= htmlspecialchars($image_path) ?>" alt="Challenge Image" class="challenge-img" />
                <?php else: ?>
                    <p>No image available for this challenge.</p>
                <?php endif; ?>
                <button class="btn btn-success mt-3" onclick="completeChallenge(<?= $challenge['id'] ?>)">I Did It!</button>
            <?php else: ?>
                <p>No challenge available today.</p>
            <?php endif; ?>
        </div>
        <div style="margin-top: 100px; text-align: center;">
            <a href="/pre_edu_platform/child_dashboard.php?childName=<?= urlencode($child_name) ?>" class="btn-back">🔙 Back to Dashboard</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="/pre_edu_platform/challenge.js"></script>
    <script>
        window.challengeConfig = {
            childId: '<?= $child_id ?>',
            csrfToken: '<?= $csrf_token ?>',
            childName: '<?= urlencode($child_name) ?>'
        };
        console.log('CSRF Token in JS:', window.challengeConfig.csrfToken);
        console.log('Child ID in JS:', window.challengeConfig.childId);
    </script>
</body>
</html>
