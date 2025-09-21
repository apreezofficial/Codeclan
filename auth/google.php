<?php
session_start();
require_once "../conn.php";

// Load env vars
function getEnvVar($key) {
    return $_ENV[$key] ?? getenv($key);
}

$client_id     = getEnvVar('GOOGLE_CLIENT_ID');
$client_secret = getEnvVar('GOOGLE_CLIENT_SECRET');
$redirect_uri  = getEnvVar('GOOGLE_REDIRECT_URI');

try {
    if (isset($_GET['code'])) {
        $code = $_GET['code'];

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
        if (curl_errno($ch)) throw new Exception(curl_error($ch));
        curl_close($ch);

        $token_data = json_decode($response, true);
        $access_token = $token_data['access_token'] ?? null;
        if (!$access_token) throw new Exception("No access token");

        $userinfo_url = "https://www.googleapis.com/oauth2/v2/userinfo?access_token={$access_token}";
        $ch = curl_init($userinfo_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $user_response = curl_exec($ch);
        if (curl_errno($ch)) throw new Exception(curl_error($ch));
        curl_close($ch);

        $user_data = json_decode($user_response, true);

        $google_id = $user_data['id'] ?? null;
        $email     = $user_data['email'] ?? '';
        $name      = $user_data['name'] ?? 'Google User';
        $picture   = $user_data['picture'] ?? '';

        if (!$google_id) throw new Exception("Missing Google ID");

        $stmt = $pdo->prepare("SELECT id FROM users WHERE google_id = :gid OR email = :email");
        $stmt->execute([":gid" => $google_id, ":email" => $email]);
        $user_id = $stmt->fetchColumn();

        if ($user_id) {
            $stmt = $pdo->prepare("UPDATE users SET name=:name, email=:email, picture=:picture WHERE id=:id");
            $stmt->execute([":name"=>$name, ":email"=>$email, ":picture"=>$picture, ":id"=>$user_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (google_id, name, email, picture) VALUES (:gid,:name,:email,:pic)");
            $stmt->execute([":gid"=>$google_id, ":name"=>$name, ":email"=>$email, ":pic"=>$picture]);
            $user_id = $pdo->lastInsertId();
        }

        // ✅ Set cookie with full user data
        setcookie("user", json_encode([
            "id" => $google_id,
            "name" => $name,
            "email" => $email,
            "picture" => $picture
        ]), time() + (86400 * 30), "/", "", false, true);

        header("Location: ../dashboard/");
        exit;
    }

    if (isset($_GET['error'])) {
        throw new Exception($_GET['error']);
    }

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
    echo "Auth error: " . $e->getMessage();
}
