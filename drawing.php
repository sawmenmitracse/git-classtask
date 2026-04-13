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
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Drawing Pad</title>
  <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <style>
    body {
      font-family: 'Comic Neue', cursive;
      background: linear-gradient(to right, #dceefb, #e0f7fa);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      text-align: center;
      padding: 20px;
      color: #01579b;
    }

    h2 {
      font-size: 2.4em;
      margin-bottom: 16px;
      color: #0277bd;
      text-shadow: 1px 1px 3px rgba(255,255,255,0.5);
    }

    canvas {
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      box-shadow: 0 10px 30px rgba(0, 149, 255, 0.2);
      cursor: crosshair;
      width: 700px;
      height: 450px;
      max-width: 90vw;
    }

    .controls {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      gap: 12px;
      margin-top: 30px;
      padding: 10px;
      max-width: 90vw;
    }

    .control-group {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .control-group label {
      font-size: 14px;
      color: #0277bd;
      margin-bottom: 4px;
    }

    .controls input[type="color"],
    .controls button,
    .controls input[type="range"],
    .controls select {
      font-size: 16px;
      padding: 10px 18px;
      border-radius: 8px;
      border: none;
      outline: none;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .controls input[type="color"] {
      width: 50px;
      height: 50px;
      border: 2px solid #cce7f9;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .controls button {
      background-color: #00aaff;
      color: white;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(0, 170, 255, 0.3);
    }

    .controls button:hover {
      background-color: #007ecc;
    }

    .controls input[type="range"] {
      width: 100px;
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
  <h2>☁️ Drawing Pad</h2>
  <canvas id="drawCanvas" width="700" height="450"></canvas>

  <div class="controls">
    <div class="control-group">
      <label for="colorPicker">Color</label>
      <input type="color" id="colorPicker" value="#000000" />
    </div>

    <div class="control-group">
      <label for="brushSize">Brush Size</label>
      <input type="range" id="brushSize" min="1" max="20" value="5" />
    </div>

    <div class="control-group">
      <label for="bgPattern">Background</label>
      <select id="bgPattern">
        <option value="none">None</option>
        <option value="watercolor">Watercolor</option>
        <option value="grid">Grid</option>
      </select>
    </div>

    <div class="control-group">
      <label>&nbsp;</label>
      <button id="clearBtn">🧼 Clear</button>
    </div>

    <div class="control-group">
      <label>&nbsp;</label>
      <button id="downloadBtn">💾 Download</button>
    </div>
  </div>

  <div class="text-center">
    <a href="/pre_edu_platform/child_dashboard.php?childName=<?= urlencode($child_name) ?>" class="btn btn-back">🔙 Back to Dashboard</a>
  </div>

  <script>
    const canvas = document.getElementById("drawCanvas");
    const ctx = canvas.getContext("2d");
    const colorPicker = document.getElementById("colorPicker");
    const clearBtn = document.getElementById("clearBtn");
    const brushSize = document.getElementById("brushSize");
    const bgPattern = document.getElementById("bgPattern");
    const downloadBtn = document.getElementById("downloadBtn");

    let drawing = false;
    let currentBrushSize = brushSize.value;

    const setBackgroundPattern = (pattern) => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      if (pattern === 'watercolor') {
        const img = new Image();
        img.src = 'https://www.transparenttextures.com/patterns/diagonal-stripes.png';
        img.onload = () => ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      } else if (pattern === 'grid') {
        ctx.beginPath();
        ctx.strokeStyle = '#a1c4e9';
        for (let i = 0; i < canvas.width; i += 40) {
          ctx.moveTo(i, 0);
          ctx.lineTo(i, canvas.height);
        }
        for (let i = 0; i < canvas.height; i += 40) {
          ctx.moveTo(0, i);
          ctx.lineTo(canvas.width, i);
        }
        ctx.stroke();
      }
    };

    bgPattern.addEventListener('change', (e) => {
      setBackgroundPattern(e.target.value);
    });

    brushSize.addEventListener('input', (e) => {
      currentBrushSize = e.target.value;
    });

    const getPos = (e) => {
      const rect = canvas.getBoundingClientRect();
      return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
      };
    };

    canvas.addEventListener("pointerdown", (e) => {
      drawing = true;
      const pos = getPos(e);
      ctx.beginPath();
      ctx.moveTo(pos.x, pos.y);
    });

    canvas.addEventListener("pointerup", () => {
      drawing = false;
      ctx.closePath();
    });

    canvas.addEventListener("pointerleave", () => {
      drawing = false;
      ctx.closePath();
    });

    canvas.addEventListener("pointermove", (e) => {
      if (!drawing) return;
      const pos = getPos(e);
      ctx.lineWidth = currentBrushSize;
      ctx.lineCap = "round";
      ctx.strokeStyle = colorPicker.value;
      ctx.lineTo(pos.x, pos.y);
      ctx.stroke();
    });

    clearBtn.addEventListener("click", () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      setBackgroundPattern(bgPattern.value);
      ctx.beginPath();
    });

    downloadBtn.addEventListener("click", () => {
      const dataURL = canvas.toDataURL("image/png");
      const link = document.createElement("a");
      link.href = dataURL;
      link.download = "drawing.png";
      link.click();
    });
  </script>
</body>
</html>