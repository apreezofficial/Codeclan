<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../conn.php";

function getEnvVar($key) {
    return $_ENV[$key] ?? getenv($key);
}

$clientId     = getEnvVar('GOOGLE_CLIENT_ID');
$clientSecret = getEnvVar('GOOGLE_CLIENT_SECRET');
$redirectUri  = getEnvVar('GOOGLE_REDIRECT_URI');

/**
 * Handle callback
 */
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
        $userInfo = file_get_contents("https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $token['access_token']);
        $user = json_decode($userInfo, true);

        if ($user && isset($user['id'])) {
            // Insert or update user in DB
            $stmt = $pdo->prepare("INSERT INTO users (google_id, name, email, picture) 
                                   VALUES (:google_id, :name, :email, :picture)
                                   ON DUPLICATE KEY UPDATE 
                                     name = VALUES(name), 
                                     email = VALUES(email), 
                                     picture = VALUES(picture)");
            $stmt->execute([
                ":google_id" => $user['id'],
                ":name"      => $user['name'] ?? '',
                ":email"     => $user['email'] ?? '',
                ":picture"   => $user['picture'] ?? ''
            ]);

            // Store cookie for 30 days
            setcookie("user", json_encode($user), time() + (86400 * 30), "/");

            // Redirect to dashboard
            header("Location: /dashboard/");
            exit;
        }
    }
}

/**
 * If already logged in via cookie
 */
if (isset($_COOKIE['user'])) {
    header("Location: /dashboard/");
    exit;
}

/**
 * Login button page
 */
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
  <title>Login with Google</title>
  <style>
    body { display: flex; height: 100vh; justify-content: center; align-items: center; background: #f9f9f9; }
    a.login-btn { padding: 15px 30px; background: #4285F4; color: white; font-weight: bold; text-decoration: none; border-radius: 5px; }
    a.login-btn:hover { background: #357AE8; }
  </style>
</head>
<body>
  <a class="login-btn" href="<?php echo htmlspecialchars($googleAuthUrl); ?>">Login with Google</a>
</body>
</html>
