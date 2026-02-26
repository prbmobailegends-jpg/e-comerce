<?php
session_start();
// Gunakan path relatif
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../image_helper.php';

// proteksi role
if (!isset($_SESSION['user']) || 
   ($_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'petugas')) {
    header("Location: ../auth/login.php");
    exit;
}

// ambil id order
 $id = $_GET['id'];

// ambil data order
 $order = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM orders WHERE id=$id")
);

// ambil item pesanan
 $items = mysqli_query(
    $conn, "SELECT * FROM order_items WHERE order_id=$id"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $order['id'] ?> - Bustore Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .detail-container {
            max-width: 900px;
            margin: 100px auto 40px;
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* --- STYLE TOMBOL KEMBALI --- */
        .btn-dashboard-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #555;
            border: 1px solid #e0e0e0;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 20px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .btn-dashboard-back:hover {
            background: #f8f9fa;
            color: #ff5722;
            border-color: #ff5722;
            transform: translateX(-3px);
        }
        /* ----------------------------- */

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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
        <!-- Tombol Kembali ke Dashboard -->
        <a href="dashboard.php" class="btn-dashboard-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Dashboard
        </a>

        <div class="detail-header">
            <h2>Detail Pesanan #<?= $order['id'] ?></h2>
            <p>
                Status Saat Ini: 
                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>"><?= $order['status'] ?></span>
            </p>
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
                    $p = mysqli_fetch_assoc(
                        mysqli_query($conn, 
                        "SELECT name FROM products WHERE id=".$i['product_id'])
                    );
                    $subtotal = $i['qty'] * $i['price'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?= $p['name'] ?></td>
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
                <?php if (!empty($order['payment_proof'])) { ?>
                    <img src="../assets/img/<?= $order['payment_proof'] ?>" alt="Bukti Pembayaran">
                <?php } else { ?>
                    <p><i>Customer belum upload bukti pembayaran</i></p>
                <?php } ?>
            </div>
        </div>

        <!-- Di dalam div detail-section, tambahkan link edit -->
<div class="detail-section">
    <h3>Update Status Pesanan</h3>
    <a href="order_edit.php?id=<?= $order['id'] ?>" class="btn btn-secondary" style="margin-bottom: 15px; display: inline-block;">
        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEgOEw3IDE0TDEzIDgiIHN0cm9rZT0iIzU1NTU1NSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+" alt="Edit">
        Edit Status
    </a>
    <form action="order_update.php" method="post">
        <input type="hidden" name="id" value="<?= $order['id'] ?>">
        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="Menunggu Pembayaran" <?= $order['status']=='Menunggu Pembayaran'?'selected':'' ?>>Menunggu Pembayaran</option>
                <option value="Dibayar" <?= $order['status']=='Dibayar'?'selected':'' ?>>Dibayar</option>
                <option value="Diproses" <?= $order['status']=='Diproses'?'selected':'' ?>>Diproses</option>
                <option value="Dikirim" <?= $order['status']=='Dikirim'?'selected':'' ?>>Dikirim</option>
                <option value="Selesai" <?= $order['status']=='Selesai'?'selected':'' ?>>Selesai</option>
            </select>
        </div>
        <button type="submit" class="btn btn-green">Update Status</button>
    </form>
</div>

</body>
</html>