<?php
session_start();
include 'config/database.php'; // Include jika perlu query produk

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// Ambil ID produk
 $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
    // Kurangi quantity
    $_SESSION['cart'][$product_id]--;
    
    // Jika quantity sudah 0 atau kurang, hapus dari cart
    if ($_SESSION['cart'][$product_id] <= 0) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['success'] = "Produk dihapus dari keranjang.";
    }
}

// Redirect kembali ke halaman cart
header("Location: cart.php");
exit;
?>