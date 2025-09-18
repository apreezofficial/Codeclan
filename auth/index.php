<?php
session_start();
require_once "../conn.php"; // must define $pdo (PDO connection)

// Load env vars
function getEnvVar($key) {
    return $_ENV[$key] ?? getenv($key);
}

$client_id     = getEnvVar('GOOGLE_CLIENT_ID');
$client_secret = getEnvVar('GOOGLE_CLIENT_SECRET');
$redirect_uri  = getEnvVar('GOOGLE_REDIRECT_URI');

try {
    // If Google redirects back with authorization code
    if (isset($_GET['code'])) {
        $code = $_GET['code'];

        // 1. Exchange authorization code for access token
        $token_url = 'https://oauth2.googleapis.com/token';
        $post_data = [
            'code' => $code,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($token_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($post_data),
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Token request failed: ' . curl_error($ch));
        }
        curl_close($ch);

        $token_data = json_decode($response, true);
        if (!$token_data || isset($token_data['error'])) {
            throw new Exception('Google error: ' . ($token_data['error_description'] ?? 'Invalid token response'));
        }

        $access_token = $token_data['access_token'] ?? null;
        if (!$access_token) {
            throw new Exception('No access token received from Google');
        }

        // 2. Get user info from Google
        $userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token;
        $ch = curl_init($userinfo_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $user_response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('User info request failed: ' . curl_error($ch));
        }
        curl_close($ch);

        $user_data = json_decode($user_response, true);
        if (!$user_data || isset($user_data['error'])) {
            throw new Exception('Google error: ' . ($user_data['error']['message'] ?? 'Invalid user data'));
        }

        // Extract user data
        $google_id = $user_data['id'] ?? null;
        $email     = $user_data['email'] ?? '';
        $name      = $user_data['name'] ?? 'Google User';
        $picture   = $user_data['picture'] ?? '';

        if (!$google_id) {
            throw new Exception('Missing Google user ID');
        }

        // 3. Database operations
        $stmt = $pdo->prepare("SELECT id FROM users WHERE google_id = :google_id OR email = :email");
        $stmt->execute([':google_id' => $google_id, ':email' => $email]);
        $user_id = $stmt->fetchColumn();

        if ($user_id) {
            // Update user
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, picture = :picture WHERE id = :id");
            $stmt->execute([
                ':name'    => $name,
                ':email'   => $email,
                ':picture' => $picture,
                ':id'      => $user_id
            ]);
        } else {
            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (google_id, name, email, picture) VALUES (:google_id, :name, :email, :picture)");
            $stmt->execute([
                ':google_id' => $google_id,
                ':name'      => $name,
                ':email'     => $email,
                ':picture'   => $picture
            ]);
            $user_id = $pdo->lastInsertId();
        }

        // 4. Create session + cookie
        $_SESSION['user_id']     = $user_id;
        $_SESSION['user_name']   = $name;
        $_SESSION['user_avatar'] = $picture;
        $_SESSION['auth_provider'] = 'google';

        setcookie('user_id', $user_id, time() + (86400 * 30), "/", "", false, true);

        header("Location: /dashboard/");
        exit;
    }

    // Handle OAuth errors from Google
    if (isset($_GET['error'])) {
        $error = htmlspecialchars($_GET['error']);
        $error_desc = isset($_GET['error_description']) ? htmlspecialchars($_GET['error_description']) : 'No description provided';
        throw new Exception("Google OAuth Error: {$error} - {$error_desc}");
    }

    // Initial authorization request
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
    $params = [
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => $_SESSION['oauth_state'],
        'access_type' => 'online',
        'prompt' => 'consent'
    ];
    header("Location: https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params));
    exit;

} catch (Exception $e) {
    echo "<h2>Authentication Error</h2>";
    echo "<p>{$e->getMessage()}</p>";
    echo "<pre>Debug Token Data: " . print_r($token_data ?? [], true) . "</pre>";
    echo "<pre>Debug User Data: " . print_r($user_data ?? [], true) . "</pre>";
    echo '<p><a href="?retry=1">Try again</a></p>';
}
