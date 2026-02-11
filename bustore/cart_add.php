<?php
session_start();
include 'config/database.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// Ambil ID produk dari GET (dari link + di cart.php) atau POST (dari form product.php)
 $product_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($product_id == 0) {
    $_SESSION['error'] = "Produk tidak valid.";
    header("Location: index.php");
    exit;
}

// Ambil data produk dari database
 $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id"));

if (!$product) {
    $_SESSION['error'] = "Produk tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// Cek stok produk
if ($product['stock'] <= 0) {
    $_SESSION['error'] = "Maaf, stok untuk produk '" . $product['name'] . "' sedang habis.";
    header("Location: product.php?id=$product_id");
    exit;
}

// Jika cart belum ada, buat baru
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Tambah produk ke cart
if (isset($_SESSION['cart'][$product_id])) {
    // Jika produk sudah ada, cek stok lagi
    if ($_SESSION['cart'][$product_id] < $product['stock']) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['error'] = "Stok tidak mencukupi untuk '" . $product['name'] . "'. Maksimal " . $product['stock'] . " item.";
        header("Location: product.php?id=$product_id");
        exit;
    }
} else {
    // Jika produk belum ada, tambahkan dengan quantity 1
    $_SESSION['cart'][$product_id] = 1;
}

// Set pesan sukses dan redirect ke cart
 $_SESSION['success'] = "'" . $product['name'] . "' berhasil ditambahkan ke keranjang!";
header("Location: cart.php");
exit;
?>