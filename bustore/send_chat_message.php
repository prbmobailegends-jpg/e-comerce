<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

 $user_id = $_SESSION['user']['id'];
 $message = $_POST['message'];

// Insert message
mysqli_query($conn, "INSERT INTO chat_messages (user_id, message, created_at) VALUES ($user_id, '$message', NOW())");

// Notify admin/petugas (in a real app, you might use WebSocket or push notifications)
// For this example, we'll just return success
echo json_encode(['status' => 'success']);
?>