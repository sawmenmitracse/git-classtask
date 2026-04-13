<?php
require_once '../db.php';

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
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Animal Sound Game</title>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Baloo 2', cursive;
      background: linear-gradient(to bottom right, #b3e5fc, #81d4fa);
      color: #01579b;
      text-align: center;
      padding: 20px;
    }

    h1 {
      font-size: 2.5rem;
      margin-bottom: 20px;
      color: #0277bd;
    }

    .game-box {
      max-width: 600px;
      background-color: #ffffff;
      margin: 0 auto;
      padding: 30px;
      border-radius: 30px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .animal-button {
      padding: 12px 25px;
      margin: 10px;
      background-color: #4fc3f7;
      color: white;
      font-size: 20px;
      border: none;
      cursor: pointer;
      border-radius: 12px;
      transition: transform 0.2s, background-color 0.3s ease;
    }

    .animal-button:hover {
      background-color: #039be5;
      transform: scale(1.05);
    }

    .animal-options {
      margin-top: 20px;
    }

    .result {
      margin-top: 30px;
      font-size: 24px;
      font-weight: bold;
    }

    .footer {
      margin-top: 40px;
      font-size: 14px;
      color: #01579b;
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
  <h1>🎶 Animal Sound Game 🐾</h1>

  <div class="game-box">
    <button id="playBtn" onclick="playSound()" class="animal-button">🔊 Play Animal Sound</button>

    <div class="animal-options">
      <button class="animal-button" onclick="checkAnswer('dog')">🐶 Dog</button>
      <button class="animal-button" onclick="checkAnswer('cat')">🐱 Cat</button>
      <button class="animal-button" onclick="checkAnswer('cow')">🐮 Cow</button>
    </div>

    <div class="result" id="result">Click play to start the game!</div>
  </div>

  <div class="text-center">
    <a href="/pre_edu_platform/child_dashboard.php?childName=<?= urlencode($child_name) ?>" class="btn btn-back">🔙 Back to Dashboard</a>
  </div>
  
  <div class="footer">© 2025 Pre-Edu Games | Fun Learning for Kids</div>

  <script>
    let currentSound = '';

    function playSound() {
      const animals = ['dog', 'cat', 'cow'];
      const randomAnimal = animals[Math.floor(Math.random() * animals.length)];
      currentSound = randomAnimal;

      const sound = new Audio(`sounds/${randomAnimal}.wav`);
      const resultDiv = document.getElementById('result');
      const playBtn = document.getElementById('playBtn');

      resultDiv.textContent = '🎧 Listen carefully and choose the animal!';
      resultDiv.style.color = '#0277bd';

      playBtn.disabled = true;
      playBtn.textContent = '🔈 Playing...';

      sound.play().then(() => {
        sound.onended = () => {
          playBtn.disabled = false;
          playBtn.textContent = '🔊 Play Animal Sound';
        };
      }).catch(error => {
        resultDiv.textContent = '⚠️ Failed to play sound!';
        resultDiv.style.color = 'red';
        playBtn.disabled = false;
        playBtn.textContent = '🔊 Play Animal Sound';
        console.error('Sound error:', error);
      });
    }

    function checkAnswer(selectedAnimal) {
      const resultDiv = document.getElementById('result');
      if (!currentSound) {
        resultDiv.textContent = '⛔ Please play a sound first!';
        resultDiv.style.color = 'orange';
        return;
      }

      if (selectedAnimal === currentSound) {
        resultDiv.textContent = '🎉 Correct! Great job!';
        resultDiv.style.color = 'green';
      } else {
        resultDiv.textContent = '❌ Oops! That was not right.';
        resultDiv.style.color = 'red';
      }
    }
  </script>
</body>
</html>