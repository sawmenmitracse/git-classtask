<?php
require_once 'db.php';

if (!isset($_SESSION['parent_id'])) {
    error_log("child_profile.php: Session parent_id not set");
    header("Location: /pre_edu_platform/index.html");
    exit;
}

$child_id = $_GET['childId'] ?? '';
if (empty($child_id)) {
    error_log("child_profile.php: childId not provided in URL");
    header("Location: /pre_edu_platform/index.html");
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT c.id, c.name, c.screen_time, s.earned_stars 
                        FROM children c 
                        LEFT JOIN stars s ON c.id = s.child_id 
                        WHERE c.id = ? AND c.parent_id = ?");
$stmt->bind_param("ii", $child_id, $_SESSION['parent_id']);
$stmt->execute();
$result = $stmt->get_result();
$child = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$child) {
    error_log("child_profile.php: No child found for childId=$child_id, parent_id=" . $_SESSION['parent_id']);
    header("Location: /pre_edu_platform/index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Profile - Pre-Edu Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Baloo 2', cursive;
            background: linear-gradient(to bottom right, #b3e5fc, #81d4fa);
            color: #01579b;
            min-height: 100vh;
            padding: 20px;
            text-align: center;
        }

        h2 {
            font-size: 2.5rem;
            color: #0277bd;
            margin-bottom: 20px;
        }

        .profile-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 0 auto;
        }

        .info {
            font-size: 18px;
            margin-bottom: 12px;
        }

        .btn-back {
            margin-top: 30px;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #0056b3;
            color: white;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 0.9rem;
            color: #01579b;
        }
    </style>
</head>
<body>
    <div class="profile-box">
        <h2>Child Profile</h2>
        <div class="info"><strong>Name:</strong> <?= htmlspecialchars($child['name']) ?></div>
        <div class="info"><strong>Remaining Screen Time:</strong> <?= htmlspecialchars($child['screen_time']) ?> minutes</div>
        <div class="info"><strong>Stars Earned:</strong> <?= htmlspecialchars($child['earned_stars'] ?? 0) ?></div>
        <div class="text-center">
            <a href="/pre_edu_platform/child_dashboard.php?childName=<?= urlencode($child['name']) ?>" class="btn-back">🔙 Back to Dashboard</a>
        </div>
    </div>

    <div class="footer">
        © 2025 Pre-Edu Platform | Designed for Fun Learning
    </div>
</body>
</html>