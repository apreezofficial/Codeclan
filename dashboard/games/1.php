<?php
session_start();

include '../top_bar.php';

$user_id = $dbUser['id'] ?? null; 
$game_id = 1;

// Handle backend AJAX requests
if (isset($_GET['action'])) {
    header("Content-Type: application/json");

    try {
        if ($_GET['action'] === 'get_question') {
            $difficulty = $_GET['difficulty'] ?? 'medium';
            $prompt = "Generate a {$difficulty} coding multiple-choice question with 4 options and specify the correct one in JSON format with keys: question, options, answer.";

            // FIX: Removed the space after 'ai/'
            $url = "https://text.pollinations.ai/" . urlencode($prompt);
            $response = @file_get_contents($url);
            $data = json_decode($response, true);

            if (!$data || !isset($data['question']) || !isset($data['options']) || !isset($data['answer'])) {
                $data = [
                    "question" => "Fallback: What is 2 + 2?",
                    "options" => ["3", "4", "5", "22"],
                    "answer" => "4"
                ];
            }
            echo json_encode($data);
            exit;
        }

        if ($_GET['action'] === 'save_result' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $_POST['result'] ?? null;

            if (!$result) {
                echo json_encode(["success" => false, "message" => "No result provided"]);
                exit;
            }

            if (!$user_id) {
                echo json_encode(["success" => false, "message" => "User not authenticated"]);
                exit;
            }

            // Save result to game_results table
            $stmt = $pdo->prepare("INSERT INTO game_results (game_id, user_id, result) VALUES (?, ?, ?)");
            $stmt->execute([$game_id, $user_id, $result]);

            // Update user stats
            if ($result === "win") {
                $pdo->prepare("UPDATE user_stats 
                               SET xp = xp + 50, games_played = games_played + 1, games_won = games_won + 1 
                               WHERE user_id = ?")->execute([$user_id]);
            } else {
                $pdo->prepare("UPDATE user_stats 
                               SET xp = xp + 10, games_played = games_played + 1 
                               WHERE user_id = ?")->execute([$user_id]);
            }

            echo json_encode(["success" => true]);
            exit;
        }

        // If action is not recognized
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        exit;

    } catch (Exception $e) {
        // Log the error for debugging (optional)
        error_log("AJAX Error: " . $e->getMessage());
        // Return a JSON error to the frontend instead of a 500
        echo json_encode(["success" => false, "message" => "An internal error occurred"]);
        exit;
    }
}

// If not an AJAX request, output the HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Code Challenge</title>
  <!-- FIX: Removed the space in the CDN URL -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    if (localStorage.getItem("theme") === "dark") {
      document.documentElement.classList.add("dark");
    }
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
  
  <!-- Top Bar (Already included and rendered by top_bar.php) -->
  <!-- The content of top_bar.php is output here because of the include statement above -->

  <!-- Game Container -->
  <div class="flex-grow flex items-center justify-center p-6">
    <div class="w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 space-y-4">
      <h2 class="text-2xl font-bold">Choose Difficulty</h2>
      <select id="difficulty" class="w-full p-2 border rounded">
        <option value="easy">Easy</option>
        <option value="medium" selected>Medium</option>
        <option value="hard">Hard</option>
      </select>
      <button onclick="fetchQuestion()" class="w-full py-2 bg-blue-600 text-white rounded">Start Game</button>
      
      <!-- Quiz Area -->
      <div id="quiz" class="hidden space-y-4 mt-6">
        <div id="timer" class="text-red-500 font-bold text-lg">60s</div>
        <h3 id="question" class="text-xl font-semibold"></h3>
        <div id="options" class="space-y-2"></div>
        <button onclick="submitAnswer()" class="w-full py-2 bg-green-600 text-white rounded">Submit Answer</button>
      </div>
    </div>
  </div>

<script>
let correctAnswer = "";
let countdown;

// Toggle light/dark theme
function toggleTheme() {
  document.documentElement.classList.toggle("dark");
  localStorage.setItem("theme", document.documentElement.classList.contains("dark") ? "dark" : "light");
}

// Fetch question from backend
async function fetchQuestion() {
  const diff = document.getElementById("difficulty").value;
  const res = await fetch("?action=get_question&difficulty=" + diff); // Use relative path "?"
  const data = await res.json();

  document.getElementById("question").innerText = data.question;
  document.getElementById("options").innerHTML = "";
  data.options.forEach(opt => {
    document.getElementById("options").innerHTML += `
      <label class="block">
        <input type="radio" name="answer" value="${opt}" class="mr-2"> ${opt}
      </label>`;
  });

  correctAnswer = data.answer;
  document.getElementById("quiz").classList.remove("hidden");

  startTimer();
}

// Start countdown timer
function startTimer() {
  let time = 60;
  document.getElementById("timer").innerText = time + "s";
  clearInterval(countdown);
  countdown = setInterval(() => {
    time--;
    document.getElementById("timer").innerText = time + "s";
    if (time <= 0) {
      clearInterval(countdown);
      submitAnswer(true);
    }
  }, 1000);
}

// Handle answer submission
async function submitAnswer(timeout = false) {
  clearInterval(countdown);
  let selected = document.querySelector("input[name='answer']:checked");
  let answer = selected ? selected.value : null;

  let result = (answer === correctAnswer && !timeout) ? "win" : "lose";

  await fetch("?action=save_result", { // Use relative path "?"
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `result=${encodeURIComponent(result)}`
  });

  alert("Game Over! You " + result);
  location.reload();
}
</script>
</body>
</html>
