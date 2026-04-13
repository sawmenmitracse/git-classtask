<?php
require_once 'db.php';

if (!isset($_SESSION['parent_id'])) {
    header("Location: index.html");
    exit;
}

$child_name = $_GET['childName'] ?? '';
if (empty($child_name)) {
    header("Location: index.html");
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT id, screen_time FROM children WHERE name = ? AND parent_id = ?");
$stmt->bind_param("si", $child_name, $_SESSION['parent_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: index.html");
    exit;
}
$child = $result->fetch_assoc();
$child_id = $child['id'];
$screen_time = $child['screen_time'];
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Dashboard - Pre-Edu Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Baloo 2', cursive;
            background: linear-gradient(to bottom right, #b3e5fc, #81d4fa);
            color: #01579b;
            min-height: 100vh;
            padding: 20px;
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            color: #0277bd;
            margin-bottom: 30px;
        }

        .card {
            border: none;
            border-radius: 15px;
            background: #ffffff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card h4 {
            color: #0277bd;
            font-weight: 700;
        }

        .btn-outline-primary, .btn-outline-success, .btn-outline-warning, .btn-outline-info {
            font-size: 1.1rem;
            padding: 10px 20px;
            border-radius: 10px;
        }

        .btn-outline-primary:hover, .btn-outline-success:hover, .btn-outline-warning:hover, .btn-outline-info:hover {
            color: #fff;
        }

        .navbar {
            background-color: #0277bd;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .navbar-brand, .nav-link {
            color: #ffffff !important;
            font-weight: 500;
        }

        .navbar-brand:hover, .nav-link:hover {
            color: #b3e5fc !important;
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
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Pre-Edu Platform</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                      
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <h1>Welcome, <?= htmlspecialchars($child_name) ?>!</h1>

    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card p-4 text-center">
                    <h4>🎨 Learning Games</h4>
                    <p>Play and learn with colors and sounds!</p>
                    <a href="games/color_game.php?childName=<?= urlencode($child_name) ?>" class="btn btn-outline-primary mb-2">🟥 Color Matching</a><br>
                    <a href="games/animal_sound_game.php?childName=<?= urlencode($child_name) ?>" class="btn btn-outline-success">🐾 Animal Sounds</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center">
                    <h4>📚 Storytelling</h4>
                    <p>Watch animated Bangla stories.</p>
                    <a href="storytelling.php?childName=<?= urlencode($child_name) ?>" class="btn btn-outline-warning">📖 View Stories</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center">
                    <h4>🖌️ Drawing Tools</h4>
                    <p>Let your creativity shine with our pad.</p>
                    <a href="drawing.php?childName=<?= urlencode($child_name) ?>" class="btn btn-outline-info">🎨 Open Drawing Pad</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center">
                    <h4>🌟 Daily Challenges</h4>
                    <p>Complete fun daily tasks!</p>
                    <a href="daily_challenges.php?childId=<?= $child_id ?>" class="btn btn-outline-primary">🚀 Start Challenges</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center">
                    <h4>👶 Child Profile</h4>
                    <p>View your profile details.</p>
                    <a href="child_profile.php?childId=<?= $child_id ?>" class="btn btn-outline-success">📋 View Profile</a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        © 2025 Pre-Edu Platform | Designed for Fun Learning
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        localStorage.setItem('childId', '<?= $child_id ?>');
        localStorage.setItem('childName', '<?= addslashes($child_name) ?>');

        const screenTime = <?= $screen_time ?>;
        if (screenTime > 0) {
            setTimeout(() => {
                alert('Screen time limit reached!');
                window.location.href = 'index.html';
            }, screenTime * 60 * 1000);
        }
    </script>
</body>
</html>
