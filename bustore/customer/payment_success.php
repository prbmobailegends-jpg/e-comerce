<?php
session_start();
include '../config/database.php';

 $order_id = $_POST['order_id'];

// Update status pesanan menjadi 'Dibayar'
mysqli_query($conn, "UPDATE orders SET status='Dibayar' WHERE id=$order_id");

// Di sini, Anda bisa kirim email notifikasi ke admin jika diperlukan
// mail('admin@bustore.com', 'Pesanan Baru', 'Ada pesanan baru dengan ID '.$order_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pembayaran Berhasil - Bustore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .success-container {
            text-align: center;
            padding: 50px 20px;
        }
        .success-container h1 { color: #28a745; }
        .success-container p { font-size: 1.1rem; color: #555; }
        .btn {
            padding: 12px 24px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.2s;
            margin-top: 20px;
        }
        .btn-primary {
            background: #ff5722;
            color: white;
        }
        .btn-primary:hover {
            background: #e64a19;
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>
    
    <div class="success-container">
        <h1>Pembayaran Berhasil!</h1>
        <p>Pesanan Anda dengan ID #<?= $order_id ?> sedang diproses.</p>
        <p>Kami akan mengirim barang Anda secepat mungkin.</p>
        <a href="orders.php" class="btn btn-primary">Lihat Pesanan Saya</a>
    </div>
</body>
</html>