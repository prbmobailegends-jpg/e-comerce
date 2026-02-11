<?php
session_start();
include '../config/database.php';

 $order_id = $_GET['id'];
 $order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id"));

if (!$order || $order['user_id'] != $_SESSION['user']['id']) {
    die("Pesanan tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Konfirmasi Pembayaran - Bustore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .confirm-container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-left: 5px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-box h3 { margin-top: 0; color: #0d47a1; }
        .info-box p { margin-bottom: 0; }
        .account-details {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
        }
        .account-details h4 { margin-bottom: 10px; }
        .btn {
            padding: 12px 24px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.2s;
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
    
    <div class="confirm-container">
        <h2>Konfirmasi Pembayaran</h2>
        <p>Silakan lakukan transfer ke rekening berikut:</p>
        
        <div class="info-box">
            <h3>Informasi Rekening</h3>
            <p><strong>Bank:</strong> Bank Example</p>
            <p><strong>No. Rekening:</strong> 1234567890</p>
            <p><strong>Atas Nama:</strong> PT. Bustore Indonesia</p>
        </div>

        <div class="account-details">
            <h4>Rincian Pembayaran Anda</h4>
            <p><strong>No. Pesanan:</strong> #<?= $order_id ?></p>
            <p><strong>Total Pembayaran:</strong> Rp <?= number_format($order['total_price']) ?></p>
        </div>

        <form action="payment_success.php" method="post">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <p>Setelah melakukan transfer, Anda akan diarahkan ke halaman upload bukti.</p>
            <button type="submit" class="btn btn-primary">Saya Sudah Transfer</button>
        </form>
    </div>
</body>
</html>