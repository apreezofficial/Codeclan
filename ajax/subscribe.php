<?php
header('Content-Type: application/json; charset=utf-8');
include '../conn.php';

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    exit;
}

// Check for duplicates
$stmt = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already subscribed.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert subscriber
$stmt = $conn->prepare("INSERT INTO subscribers (email, subscribed_at) VALUES (?, NOW())");
$stmt->bind_param("s", $email);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Thank you for subscribing!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Subscription failed, please try again.']);
}

$stmt->close();
$conn->close();
?>