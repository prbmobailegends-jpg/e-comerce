<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

 $product_id = $_POST['product_id'];
 $order_id = $_POST['order_id'];
 $rating = $_POST['rating'];
 $review = $_POST['review'];
 $user_id = $_SESSION['user']['id'];

// Validate that the order belongs to the user and is completed
 $order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id AND status = 'Selesai'"));

if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak valid']);
    exit;
}

// Check if the product is in the order
 $order_item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $order_id AND product_id = $product_id"));

if (!$order_item) {
    echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan dalam pesanan']);
    exit;
}

// Save the rating
mysqli_query($conn, "INSERT INTO product_ratings (product_id, user_id, rating, review, created_at) VALUES ($product_id, $user_id, $rating, '$review', NOW())");

// Mark the item as rated
mysqli_query($conn, "UPDATE order_items SET rated = 1 WHERE order_id = $order_id AND product_id = $product_id");

// Update product rating
 $rating_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM product_ratings WHERE product_id = $product_id"));
 $new_rating = round($rating_data['avg_rating'], 1);
mysqli_query($conn, "UPDATE products SET rating = $new_rating WHERE id = $product_id");

echo json_encode(['status' => 'success', 'message' => 'Terima kasih atas penilaian Anda!']);
?>