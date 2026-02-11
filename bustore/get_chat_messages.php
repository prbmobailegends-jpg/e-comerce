<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

 $user_id = $_SESSION['user']['id'];

// Get chat messages
 $messages = mysqli_query($conn, "
    SELECT * FROM chat_messages 
    WHERE (user_id = $user_id OR user_id IS NULL) 
    ORDER BY created_at ASC
");

while ($message = mysqli_fetch_assoc($messages)) {
    $isUser = $message['user_id'] == $user_id;
    $time = date('H:i', strtotime($message['created_at']));
    
    echo '<div class="message ' . ($isUser ? 'user' : 'admin') . '">';
    echo '<div class="message-bubble">';
    echo $message['message'];
    echo '<div class="message-time">' . $time . '</div>';
    echo '</div>';
    echo '</div>';
}
?>