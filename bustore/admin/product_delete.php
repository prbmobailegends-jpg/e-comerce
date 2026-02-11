<?php
session_start();
include '../config/database.php';

if ($_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}

 $id = $_GET['id'];

// Hapus gambar terkait jika ada
 $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM products WHERE id=$id"));
if ($product && !empty($product['image'])) {
    $image_path = "../assets/img/" . $product['image'];
    if (file_exists($image_path)) {
        unlink($image_path);
    }
}

mysqli_query($conn, "DELETE FROM products WHERE id=$id");

header("Location: products.php");
exit();