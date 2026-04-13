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
  <title>Color Matching Game</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Comic Neue', cursive;
      text-align: center;
      background: linear-gradient(to bottom right, #b3e5fc, #81d4fa);
      padding: 20px;
      color: #01579b;
    }

    h2 {
      font-size: 2.5rem;
      color: #0277bd;
    }

    .game-area {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 40px;
      margin-top: 30px;
      flex-wrap: wrap;
    }

    .color-box, .drop-zone {
      width: 120px;
      height: 120px;
      border-radius: 10px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: bold;
      font-size: 18px;
      color: white;
      cursor: grab;
    }

    .color-box {
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .drop-zone {
      border: 3px dashed #aaa;
      background: #f4f4f4;
      color: #333;
    }

    #bonus {
      margin-top: 30px;
      font-size: 20px;
      font-weight: bold;
      color: green;
    }

    .hidden {
      display: none;
    }

    .notification {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      padding: 10px;
      margin-top: 20px;
      border-radius: 5px;
      width: fit-content;
      margin-inline: auto;
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
  <h2>🟥 Color Matching Game</h2>
  <p>Drag the color to the correct drop zone</p>

  <div class="game-area" id="colorBoxesContainer"></div>
  <div class="game-area" id="dropZonesContainer"></div>

  <div id="bonus" class="notification hidden">🎁 1 Reward Added!</div>

  <div class="text-center">
    <a href="/pre_edu_platform/child_dashboard.php?childName=<?= urlencode($child_name) ?>" class="btn btn-back">🔙 Back to Dashboard</a>
  </div>

  <script>
    const colors = ['red', 'blue', 'green', 'yellow', 'purple'];
    let matches = 0;

    const colorBoxesContainer = document.getElementById('colorBoxesContainer');
    const dropZonesContainer = document.getElementById('dropZonesContainer');
    const bonusEl = document.getElementById('bonus');

    function shuffleArray(arr) {
      for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
      }
      return arr;
    }

    function createGame() {
      colorBoxesContainer.innerHTML = '';
      dropZonesContainer.innerHTML = '';
      bonusEl.classList.add('hidden');
      matches = 0;

      const selectedColors = shuffleArray([...colors]).slice(0, 3);
      const shuffledColors = shuffleArray([...selectedColors]);

      shuffledColors.forEach(color => {
        const box = document.createElement('div');
        box.id = color;
        box.className = 'color-box';
        box.textContent = '';
        box.style.backgroundColor = color;
        box.draggable = true;
        colorBoxesContainer.appendChild(box);
      });

      selectedColors.forEach(color => {
        const dropZone = document.createElement('div');
        dropZone.className = 'drop-zone';
        dropZone.dataset.color = color;
        dropZone.textContent = `Drop ${color.charAt(0).toUpperCase() + color.slice(1)} Here`;
        dropZonesContainer.appendChild(dropZone);
      });

      enableDragAndDrop();
    }

    function enableDragAndDrop() {
      const colorBoxes = document.querySelectorAll('.color-box');
      const dropZones = document.querySelectorAll('.drop-zone');

      colorBoxes.forEach(box => {
        box.addEventListener('dragstart', (e) => {
          e.dataTransfer.setData('text/plain', box.id);
        });
      });

      dropZones.forEach(zone => {
        zone.addEventListener('dragover', (e) => e.preventDefault());

        zone.addEventListener('drop', function handler(e) {
          e.preventDefault();
          const draggedColor = e.dataTransfer.getData('text/plain');

          if (draggedColor === zone.dataset.color) {
            zone.textContent = '✅ Matched!';
            zone.style.backgroundColor = draggedColor;
            zone.style.color = 'white';
            zone.style.border = 'none';
            matches++;
            this.removeEventListener('drop', handler);

            if (matches === 3) {
              bonusEl.classList.remove('hidden');
              setTimeout(() => {
                createGame();
              }, 2000);
            }
          } else {
            alert('❌ Wrong match!');
          }
        });
      });
    }

    createGame();
  </script>
</body>
</html>