<?php
include 'top_bar.php';

// Game config
$points_per_game = 50; 
// Initialize game in database
$stmt = $pdo->prepare("SELECT id FROM games WHERE title = ?");
$stmt->execute(['Memory Card Game']);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    $pdo->prepare("INSERT INTO games (title, description) VALUES (?, ?)")
        ->execute(['Memory Card Game', 'Match pairs of cards to win!']);
    $game_id = $pdo->lastInsertId();
} else {
    $game_id = $game['id'];
}

// AJAX handler for game completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'game_complete') {
    header('Content-Type: application/json');
    
    try {
        // Insert into game_results
        $pdo->prepare("INSERT INTO game_results (game_id, user_id, score, result) VALUES (?, ?, ?, 'win')")
            ->execute([$game_id, $user_id, $points_per_game]);

        // Update user_stats
        $pdo->prepare("
            INSERT INTO user_stats (user_id, xp, games_played, games_won) 
            VALUES (?, ?, 1, 1) 
            ON DUPLICATE KEY UPDATE 
                xp = xp + ?,
                games_played = games_played + 1,
                games_won = games_won + 1,
                updated_at = CURRENT_TIMESTAMP
        ")->execute([$user_id, $points_per_game, $points_per_game]);

        echo json_encode(['success' => true, 'xp_earned' => $points_per_game]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧠 Memory Card Game | Purple Theme</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: "#6B21A8",
                        'brand-light': '#8B5CF6',
                        'brand-dark': '#4C1D95',
                        'brand-darker': '#37196B'
                    },
                    animation: {
                        'flip': 'flip 0.5s ease-in-out',
                        'shake': 'shake 0.35s ease-in-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'pop-in': 'pop-in 0.3s ease-out'
                    },
                    keyframes: {
                        flip: {
                            '0%': { transform: 'rotateY(0deg)' },
                            '100%': { transform: 'rotateY(180deg)' }
                        },
                        shake: {
                            '0%, 100%': { transform: 'translateX(0)' },
                            '20%': { transform: 'translateX(-13px)' },
                            '40%': { transform: 'translateX(13px)' },
                            '60%': { transform: 'translateX(-8px)' },
                            '80%': { transform: 'translateX(8px)' }
                        },
                        'pop-in': {
                            '0%': { transform: 'scale(0.8)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        }
                    }
                }
            }
        };
    </script>
    <style>
        .card.flip .back-view {
            transform: rotateY(0deg);
        }
        .card.flip .front-view {
            transform: rotateY(180deg);
        }
        .card.shake {
            animation: shake 0.35s ease-in-out;
        }
        .view {
            transition: transform 0.25s linear;
            backface-visibility: hidden;
        }
        .front-view {
            transform: rotateY(0deg);
        }
        .back-view {
            transform: rotateY(-180deg);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Victory Modal -->
    <div id="victoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-8 text-center shadow-2xl max-w-md mx-4 animate-pop-in">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-3xl font-bold text-brand mb-2">You Won!</h2>
            <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">+<span id="xpEarned" class="font-bold text-2xl text-green-500">50</span> XP Earned</p>
            <button id="playAgainBtn" class="px-6 py-3 bg-brand hover:bg-brand-dark text-white font-semibold rounded-lg shadow transition transform hover:scale-105">
                Play Again
            </button>
        </div>
    </div>

    <div class="wrapper bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-2xl max-w-lg w-full">
        <h1 class="text-3xl md:text-4xl font-extrabold text-center mb-6 text-brand">
            🧠 Memory Card Game
        </h1>
        <p class="text-center text-gray-600 dark:text-gray-300 mb-6">Match all pairs to win!</p>

        <ul id="cards" class="cards grid grid-cols-4 gap-3 h-80 md:h-96">
            <!-- Cards will be generated by JavaScript -->
        </ul>
    </div>

    <script>
const cardImages = [
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQPPCphsPfqKyMxF2dZycOTJhca5t7GW5jtMypniBvtuU2mrOwU--NtvU0&s',
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRb7wbR6rpuhAKL7CabY6a8zG0ItKw0m7-zX8kqGIUSVARjmKdE9KAXY0I&s',
    'https://codingnepalweb.com/demos/memory-card-game-javascript/images/img-3.png',
    'https://codingnepalweb.com/demos/memory-card-game-javascript/images/img-4.png',
    'https://codingnepalweb.com/demos/memory-card-game-javascript/images/img-5.png',
    'https://codingnepalweb.com/demos/memory-card-game-javascript/images/img-6.png',
    'https://codingnepalweb.com/demos/memory-card-game-javascript/images/img-7.png',
    'https://codingnepalweb.com/demos/memory-card-game-javascript/images/img-8.png'
];

        let matched = 0;
        let cardOne, cardTwo;
        let disableDeck = false;
        const cardsContainer = document.getElementById('cards');
        const victoryModal = document.getElementById('victoryModal');
        const xpEarnedSpan = document.getElementById('xpEarned');
        const playAgainBtn = document.getElementById('playAgainBtn');

        // Initialize the game
        function initGame() {
            matched = 0;
            disableDeck = false;
            cardOne = cardTwo = null;

            // Create 16 cards (8 pairs)
            let cardArray = [...cardImages, ...cardImages];
            // Shuffle array
            cardArray.sort(() => Math.random() > 0.5 ? 1 : -1);

            // Clear container
            cardsContainer.innerHTML = '';

            // Generate cards
            cardArray.forEach((img, index) => {
                const li = document.createElement('li');
                li.className = 'card cursor-pointer relative w-full aspect-square perspective-1000 preserve-3d rounded-xl bg-white dark:bg-gray-700 shadow-md';
                li.innerHTML = `
                    <div class="view front-view absolute inset-0 flex items-center justify-center bg-brand rounded-xl">
                        <img src="https://codingnepalweb.com/demos/memory-card-game-javascript/images/que_icon.svg" alt="icon" class="w-6 md:w-8">
                    </div>
                    <div class="view back-view absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 rounded-xl">
      
<img src="${img.trim()}" alt="card-img" class="max-w-12 md:max-w-16">
</div>
                `;
                li.addEventListener('click', flipCard);
                cardsContainer.appendChild(li);
            });
        }

        function flipCard(e) {
            const clickedCard = e.currentTarget;
            if (clickedCard === cardOne || disableDeck) return;

            clickedCard.classList.add('flip');

            if (!cardOne) {
                cardOne = clickedCard;
                return;
            }

            cardTwo = clickedCard;
            disableDeck = true;

            const cardOneImg = cardOne.querySelector('.back-view img').src;
            const cardTwoImg = cardTwo.querySelector('.back-view img').src;

            matchCards(cardOneImg, cardTwoImg);
        }

        function matchCards(img1, img2) {
            if (img1 === img2) {
                matched++;
                cardOne.removeEventListener('click', flipCard);
                cardTwo.removeEventListener('click', flipCard);

                if (matched === 8) {
                    // Show victory modal after 1 second
                    setTimeout(() => {
                        showVictoryModal();
                    }, 500);
                }

                resetCards();
                return;
            }

            // Shake if not matched
            setTimeout(() => {
                cardOne.classList.add('shake');
                cardTwo.classList.add('shake');
            }, 400);

            setTimeout(() => {
                cardOne.classList.remove('shake', 'flip');
                cardTwo.classList.remove('shake', 'flip');
                resetCards();
            }, 1200);
        }

        function resetCards() {
            cardOne = cardTwo = null;
            disableDeck = false;
        }

        function showVictoryModal() {
            xpEarnedSpan.textContent = <?= $points_per_game ?>;
            victoryModal.classList.remove('hidden');
            // Send completion to backend
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=game_complete'
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to save game result:', data.error);
                }
            })
            .catch(err => console.error('Network error:', err));
        }

        // Play again button
        playAgainBtn.addEventListener('click', () => {
            victoryModal.classList.add('hidden');
            initGame();
        });

        // Initialize on load
        window.addEventListener('DOMContentLoaded', initGame);
    </script>
</body>
</html>
