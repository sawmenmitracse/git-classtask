<?php

require_once 'db.php';

if (!isset($_SESSION['parent_id'])) {
    header("Location: /pre_edu_platform/index.html");
    exit;
}

$child_name = $_GET['childName'] ?? '';
if (empty($child_name)) {
    header("Location: /pre_edu_platform/index.html");
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT id FROM children WHERE name = ? AND parent_id = ?");
$stmt->bind_param("si", $child_name, $_SESSION['parent_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: /pre_edu_platform/index.html");
    exit;
}
$child = $result->fetch_assoc();
$child_id = $child['id'];
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>শিশুদের বাংলা গল্প</title>
  <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <style>
    body {
      font-family: 'Comic Neue', cursive;
      background: linear-gradient(to bottom right, #b3e5fc, #81d4fa);
      text-align: center;
      padding: 20px;
      color: #01579b;
    }

    h1 {
      font-size: 2.5rem;
      color: #0277bd;
      margin-bottom: 30px;
    }

    .story {
      margin: 30px auto;
      max-width: 700px;
      background-color: #ffffff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    h2 {
      color: #039be5;
      font-weight: bold;
    }

    iframe {
      width: 100%;
      height: 315px;
      border: none;
      border-radius: 12px;
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
  </style>
</head>
<body>
  <h1>📖 শিশুদের জন্য বাংলা গল্প</h1>

  <div class="story">
    <h2>গল্প ১: সিংহ ও ইঁদুর</h2>
    <iframe 
      src="https://www.youtube.com/embed/oH-qyF5dp4c?si=rPpTxjzmTHLMT6fJ"
      title="YouTube video player" 
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
      referrerpolicy="strict-origin-when-cross-origin" 
      allowfullscreen>
    </iframe>
  </div>

  <div class="story">
    <h2>গল্প ২: খরগোশ আর কচ্ছপ গল্প</h2>
    <iframe 
      src="https://www.youtube.com/embed/aZKD-gGM-DI?si=KMUyjfsITRmhK4_f" 
      title="YouTube video player" 
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
      referrerpolicy="strict-origin-when-cross-origin" 
      allowfullscreen>
    </iframe>
  </div>

  <div class="story">
    <h2>গল্প ৩: নেকড়ে আর বকের গল্প</h2>
    <iframe 
      src="https://www.youtube.com/embed/TkSa4xO3XUw?si=3nYvKuCCpArOvCzB" 
      title="YouTube video player" 
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
      referrerpolicy="strict-origin-when-cross-origin" 
      allowfullscreen>
    </iframe>
  </div>

  <div class="text-center">
    <a href="/pre_edu_platform/child_dashboard.php?childName=<?= urlencode($child_name) ?>" class="btn btn-back">🔙 Back to Dashboard</a>
  </div>
</body>
</html>
