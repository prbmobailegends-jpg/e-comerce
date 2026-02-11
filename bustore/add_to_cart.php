<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// Cek apakah ini adalah request dari form dengan method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['qty'])) {
    $product_id = (int)$_POST['product_id'];
    $qty = (int)$_POST['qty'];
    
    // Validasi quantity
    if ($qty <= 0) {
        $_SESSION['error'] = "Quantity harus lebih dari 0";
        header("Location: product.php?id=$product_id");
        exit;
    }

    // Validasi produk
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id"));
    if (!$product) {
        $_SESSION['error'] = "Produk tidak ditemukan";
        header("Location: product.php?id=$product_id");
        exit;
    }

    // Validasi stok
    if ($product['stock'] < $qty) {
        $_SESSION['error'] = "Stok tidak mencukupi. Stok tersedia: " . $product['stock'];
        header("Location: product.php?id=$product_id");
        exit;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Tambahkan ke keranjang
    if (isset($_SESSION['cart'][$product_id])) {
        $new_qty = $_SESSION['cart'][$product_id] + $qty;
        // Cek jika stok mencukupi
        if ($new_qty <= $product['stock']) {
            $_SESSION['cart'][$product_id] = $new_qty;
        } else {
            $_SESSION['error'] = "Stok tidak mencukupi. Stok tersedia: " . $product['stock'];
            header("Location: product.php?id=$product_id");
            exit;
        }
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }

    $_SESSION['success'] = "Produk berhasil ditambahkan ke keranjang";
    header("Location: cart.php");
    exit;
} else {
    // Jika bukan POST, redirect ke index
    header("Location: index.php");
    exit;
}
?>