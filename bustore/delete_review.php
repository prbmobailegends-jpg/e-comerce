<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['product_id'])) {
    header("Location: index.php");
    exit;
}

 $review_id = (int)$_GET['id'];
 $product_id = (int)$_GET['product_id'];
 $user_id = $_SESSION['user']['id'];

// 1. Pastikan ulasan ini milik user yang login
 $check = mysqli_query($conn, "SELECT * FROM product_reviews WHERE id = $review_id AND user_id = $user_id");

if (mysqli_num_rows($check) > 0) {
    // 2. Hapus ulasan
    $delete_query = mysqli_query($conn, "DELETE FROM product_reviews WHERE id = $review_id");
    
    if ($delete_query) {
        // 3. Recalculate ulang rata-rata rating produk agar akurat
        $avg_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg_r, COUNT(*) as count FROM product_reviews WHERE product_id = $product_id"));
        
        // Jika count 0, set rating ke 0, bulatkan jika ada
        if ($avg_data['count'] > 0) {
            $new_avg = round($avg_data['avg_r'], 1);
        } else {
            $new_avg = 0;
        }
        
        mysqli_query($conn, "UPDATE products SET rating = $new_avg WHERE id = $product_id");
        
        // Redirect dengan pesan sukses
        header("Location: product_detail.php?id=$product_id#reviews-section");
        exit;
    }
}

// Jika gagal atau bukan milik user, kembali ke produk
header("Location: product_detail.php?id=$product_id");
exit;
?>