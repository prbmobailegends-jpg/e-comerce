<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

 $order_id = $_GET['id'];
 $user_id = $_SESSION['user']['id'];

// Ambil data order
 $order = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM orders 
     WHERE id=$order_id AND user_id=$user_id"
));

if (!$order) {
    die("Pesanan tidak ditemukan");
}

// Ambil item pesanan
 $items = mysqli_query($conn,
    "SELECT * FROM order_items WHERE order_id=$order_id"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $order['id'] ?> - Bustore</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .detail-container {
            max-width: 900px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .detail-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .detail-header h2 {
            color: #333;
        }
        .detail-section {
            margin-bottom: 25px;
        }
        .detail-section h3 {
            margin-bottom: 15px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f9f9f9;
            font-weight: 600;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .status-menunggu { background: #fff3cd; color: #856404; }
        .status-dibayar { background: #cce5ff; color: #004085; }
        .status-diproses { background: #d1ecf1; color: #0c5460; }
        .status-dikirim { background: #d4edda; color: #155724; }
        .status-selesai { background: #d1ecf1; color: #0c5460; }
        
        .payment-proof img {
            max-width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .btn {
            padding: 10px 18px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-green {
            background: #ee4d2d;
            color: white;
        }
        .btn-green:hover {
            background: #d6371f;
        }
        .btn-gray {
            background: #f0f0f0;
            color: #555;
        }
        .btn-gray:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="detail-container">
        <div class="detail-header">
            <h2>Detail Pesanan #<?= $order['id'] ?></h2>
            <p>Status: <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>"><?= $order['status'] ?></span></p>
            <p>Total: <strong>Rp <?= number_format($order['total_price']) ?></strong></p>
        </div>

        <div class="detail-section">
            <h3>Daftar Produk</h3>
            <table>
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
                <?php
                $total = 0;
                while ($i = mysqli_fetch_assoc($items)) {
                    $subtotal = $i['qty'] * $i['price'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td>
                        <!-- Ambil nama produk dari tabel products -->
                        <?php 
                        $product = mysqli_fetch_assoc(
                            mysqli_query($conn, 
                            "SELECT name FROM products WHERE id=".$i['product_id'])
                        ); 
                        ?>
                        <?= $product['name'] ?>
                    </td>
                    <td><?= $i['qty'] ?></td>
                    <td>Rp <?= number_format($i['price']) ?></td>
                    <td>Rp <?= number_format($subtotal) ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <th colspan="3">Total</th>
                    <th>Rp <?= number_format($total) ?></th>
                </tr>
            </table>
        </div>

        <div class="detail-section">
            <h3>Bukti Pembayaran</h3>
            <div class="payment-proof">
                <?php if ($order['payment_proof']) { ?>
                    <img src="../assets/img/<?= $order['payment_proof'] ?>" alt="Bukti Pembayaran">
                <?php } else { ?>
                    <p><i>Belum ada bukti pembayaran</i></p>
                <?php } ?>
            </div>
        </div>
        
        <a href="orders.php" class="btn btn-gray">⬅ Kembali ke Pesanan Saya</a>
    </div>

</body>
</html>