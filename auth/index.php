<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../conn.php"; // make sure $pdo is created inside conn.php

function getEnvVar($key) {
    return $_ENV[$key] ?? getenv($key);
}

$clientId     = getEnvVar('GOOGLE_CLIENT_ID');
$clientSecret = getEnvVar('GOOGLE_CLIENT_SECRET');
$redirectUri  = getEnvVar('GOOGLE_REDIRECT_URI');

// Check if user is already logged in via cookie
if (isset($_COOKIE['user'])) {
    $userData = json_decode($_COOKIE['user'], true);
    if ($userData && isset($userData['id'])) {
        $_SESSION['user'] = $userData;
        header("Location: /dashboard/");
        exit;
    }
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Exchange code for access token
    $tokenData = file_get_contents("https://oauth2.googleapis.com/token", false, stream_context_create([
        "http" => [
            "method"  => "POST",
            "header"  => "Content-Type: application/x-www-form-urlencoded",
            "content" => http_build_query([
                "code"          => $code,
                "client_id"     => $clientId,
                "client_secret" => $clientSecret,
                "redirect_uri"  => $redirectUri,
                "grant_type"    => "authorization_code",
            ])
        ]
    ]));
    
    $token = json_decode($tokenData, true);

    if (!empty($token['access_token'])) {
        // Fetch user info
        $userInfoResponse = file_get_contents("https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $token['access_token']);
        $user = json_decode($userInfoResponse, true);

        if ($user && isset($user['id'])) {
            try {
                // First, check if user exists
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE google_id = :google_id");
                $checkStmt->execute([":google_id" => $user['id']]);
                $existingUser = $checkStmt->fetch();

                if ($existingUser) {
                    // Update existing user
                    $updateStmt = $pdo->prepare("UPDATE users SET 
                        name = :name, 
                        email = :email, 
                        picture = :picture,
                        updated_at = NOW()
                        WHERE google_id = :google_id");
                    
                    $result = $updateStmt->execute([
                        ":google_id" => $user['id'],
                        ":name"      => $user['name'] ?? '',
                        ":email"     => $user['email'] ?? '',
                        ":picture"   => $user['picture'] ?? ''
                    ]);
                    
                    if ($result) {
                        error_log("✅ User updated successfully: " . ($user['email'] ?? 'No email'));
                    }
                } else {
                    // Insert new user
                    $insertStmt = $pdo->prepare("INSERT INTO users (google_id, name, email, picture, created_at, updated_at) 
                                               VALUES (:google_id, :name, :email, :picture, NOW(), NOW())");
                    
                    $result = $insertStmt->execute([
                        ":google_id" => $user['id'],
                        ":name"      => $user['name'] ?? '',
                        ":email"     => $user['email'] ?? '',
                        ":picture"   => $user['picture'] ?? ''
                    ]);
                    
                    if ($result && $insertStmt->rowCount() > 0) {
                        error_log("✅ New user inserted successfully: " . ($user['email'] ?? 'No email'));
                    } else {
                        error_log("⚠️ User insertion failed or no rows affected");
                        throw new Exception("Failed to insert user");
                    }
                }

                // Get the complete user data from database
                $getUserStmt = $pdo->prepare("SELECT * FROM users WHERE google_id = :google_id");
                $getUserStmt->execute([":google_id" => $user['id']]);
                $dbUser = $getUserStmt->fetch(PDO::FETCH_ASSOC);

                if ($dbUser) {
                    // Store session
                    $_SESSION['user'] = $dbUser;
                    
                    // Store cookie for 30 days
                    setcookie("user", json_encode($dbUser), time() + (86400 * 30), "/", "", false, true);
                    
                    error_log("✅ User session and cookie set successfully");
                    
                    // Redirect to dashboard
                    header("Location: /dashboard/");
                    exit;
                } else {
                    throw new Exception("Failed to retrieve user from database");
                }

            } catch (PDOException $e) {
                error_log("❌ DB Error: " . $e->getMessage());
                die("Database error occurred. Please try again.");
            } catch (Exception $e) {
                error_log("❌ Error: " . $e->getMessage());
                die("An error occurred during login. Please try again.");
            }
        } else {
            error_log("❌ No user info received from Google. Response: " . $userInfoResponse);
            die("❌ No user info received from Google.");
        }
    } else {
        error_log("❌ Failed to get access token. Response: " . $tokenData);
        die("❌ Failed to get access token from Google.");
    }
}

$googleAuthUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    "client_id"     => $clientId,
    "redirect_uri"  => $redirectUri,
    "response_type" => "code",
    "scope"         => "openid email profile",
    "access_type"   => "offline",
    "prompt"        => "consent"
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Login with Google</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'gradient-x': 'gradient-x 15s ease infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        'gradient-x': {
                            '0%, 100%': {
                                'background-size': '200% 200%',
                                'background-position': 'left center'
                            },
                            '50%': {
                                'background-size': '200% 200%',
                                'background-position': 'right center'
                            },
                        },
                        'float': {
                            '0%, 100%': { transform: 'translatey(0px)' },
                            '50%': { transform: 'translatey(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .bg-gradient-animated {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c, #4facfe, #00f2fe);
            background-size: 400% 400%;
            animation: gradient-x 15s ease infinite;
        }
        
        .glass {
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glow {
            box-shadow: 0 0 20px rgba(79, 172, 254, 0.3), 
                        0 0 40px rgba(79, 172, 254, 0.2),
                        0 0 60px rgba(79, 172, 254, 0.1);
        }
    </style>
</head>
<body class="bg-gradient-animated min-h-screen flex items-center justify-center p-4 overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-float"></div>
        <div class="absolute top-3/4 right-1/4 w-72 h-72 bg-purple-400 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-1/4 left-1/2 w-72 h-72 bg-pink-400 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-float" style="animation-delay: 4s;"></div>
    </div>

    <!-- Main content -->
    <div class="relative z-10 text-center">
        <!-- Logo/Title -->
        <div class="mb-8 animate-pulse-slow">
            <h1 class="text-6xl md:text-8xl font-bold text-white mb-4 drop-shadow-lg">
                Welcome
            </h1>
            <p class="text-xl md:text-2xl text-white/90 font-light">
                Sign in to continue your journey
            </p>
        </div>

        <!-- Login card -->
        <div class="glass rounded-3xl p-8 md:p-12 shadow-2xl max-w-md mx-auto transform hover:scale-105 transition-all duration-300">
            <div class="mb-8">
                <div class="w-20 h-20 mx-auto mb-6 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Secure Login</h2>
                <p class="text-white/80 text-sm">
                    Connect with your Google account for a seamless experience
                </p>
            </div>

            <!-- Google Login Button -->
            <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" 
               class="group relative inline-flex items-center justify-center w-full px-8 py-4 bg-white text-gray-700 font-semibold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 glow hover:bg-gray-50">
                <svg class="w-6 h-6 mr-3 group-hover:rotate-12 transition-transform duration-300" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span class="relative">
                    Continue with Google
                    <span class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 text-transparent bg-clip-text opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        Continue with Google
                    </span>
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
            </a>

            <div class="mt-6 text-center">
                <p class="text-white/60 text-xs">
                    By continuing, you agree to our Terms of Service and Privacy Policy
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-white/60 text-sm">
            <p>Secure • Fast • Reliable</p>
        </div>
    </div>

    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll and entrance animation
            document.body.style.opacity = '0';
            document.body.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                document.body.style.transition = 'all 0.8s ease';
                document.body.style.opacity = '1';
                document.body.style.transform = 'translateY(0)';
            }, 100);
            
            // Add click ripple effect to the login button
            const loginBtn = document.querySelector('a[href*="accounts.google.com"]');
            loginBtn.addEventListener('click', function(e) {
                const ripple = document.createElement('div');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255,255,255,0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
        
        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
