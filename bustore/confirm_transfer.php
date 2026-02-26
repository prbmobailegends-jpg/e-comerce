<?php
session_start();
require_once 'config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Cek role (hanya admin dan petugas)
if ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'petugas') {
    header('Location: index.php');
    exit;
}

 $order_id = $_GET['id'] ?? 0;
 $order = null;

// Ambil data order
if ($order_id > 0) {
    $query = "SELECT o.*, u.name as customer_name, u.email as customer_email 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = $order_id";
    $result = mysqli_query($conn, $query);
    $order = mysqli_fetch_assoc($result);
}

// Proses konfirmasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $rejection_reason = $_POST['rejection_reason'] ?? '';
    
    if ($action == 'approve') {
        // Approve payment
        $update = "UPDATE orders SET 
                   payment_status = 'paid', 
                   status = 'Diproses',
                   payment_rejection_reason = NULL 
                   WHERE id = $order_id";
        mysqli_query($conn, $update);
        
        // Update stock (reduce)
        $items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
        $items_result = mysqli_query($conn, $items_query);
        
        while ($item = mysqli_fetch_assoc($items_result)) {
            $update_stock = "UPDATE products SET stock = stock - {$item['qty']} WHERE id = {$item['product_id']}";
            mysqli_query($conn, $update_stock);
        }
        
        $_SESSION['success'] = 'Pembayaran berhasil dikonfirmasi!';
    } elseif ($action == 'reject') {
        // Reject payment
        $update = "UPDATE orders SET 
                   payment_status = 'rejected', 
                   status = 'Dibatalkan',
                   payment_rejection_reason = '$rejection_reason' 
                   WHERE id = $order_id";
        mysqli_query($conn, $update);
        
        $_SESSION['success'] = 'Pembayaran ditolak!';
    }
    
    header('Location: admin_orders.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - Bustore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .confirm-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .confirm-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .payment-proof {
            max-width: 400px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px;
            background: #f8f9fa;
        }
        .payment-proof img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .btn-approve {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        }
        .btn-reject {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-unpaid {
            background: #fff3cd;
            color: #856404;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-store"></i> Bustore Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_orders.php">
                            <i class="fas fa-shopping-bag"></i> Pesanan
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= $_SESSION['user_name'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="confirm-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-credit-card"></i> Konfirmasi Pembayaran</h2>
            <a href="admin_orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <?php if ($order): ?>
            <!-- Detail Pesanan -->
            <div class="confirm-section">
                <h3 class="section-title"><i class="fas fa-info-circle"></i> Informasi Pesanan</h3>
                
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>No. Pesanan:</strong></td>
                                <td><?= $order['order_id'] ?? '#' . $order['id'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pelanggan:</strong></td>
                                <td><?= $order['customer_name'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?= $order['customer_email'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal:</strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Total:</strong></td>
                                <td>Rp <?= number_format($order['total_price']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Metode:</strong></td>
                                <td><?= $order['payment_method'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status Pembayaran:</strong></td>
                                <td>
                                    <span class="status-badge status-<?= $order['payment_status'] ?>">
                                        <?= ucfirst($order['payment_status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status Pesanan:</strong></td>
                                <td><?= $order['status'] ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Bukti Pembayaran -->
            <?php if ($order['payment_proof']): ?>
                <div class="confirm-section">
                    <h3 class="section-title"><i class="fas fa-image"></i> Bukti Pembayaran</h3>
                    
                    <div class="payment-proof">
                        <img src="uploads/<?= $order['payment_proof'] ?>" alt="Bukti Pembayaran">
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Alamat Pengiriman -->
            <div class="confirm-section">
                <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</h3>
                
                <p>
                    <strong><?= $order['customer_name'] ?></strong><br>
                    <?= $order['shipping_address'] ?><br>
                    <i class="fas fa-phone"></i> <?= $order['shipping_phone'] ?>
                </p>
            </div>
            
            <!-- Produk yang Dipesan -->
            <div class="confirm-section">
                <h3 class="section-title"><i class="fas fa-box"></i> Produk yang Dipesan</h3>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $items_query = "SELECT oi.*, p.name, p.image 
                                          FROM order_items oi 
                                          JOIN products p ON oi.product_id = p.id 
                                          WHERE oi.order_id = $order_id";
                            $items_result = mysqli_query($conn, $items_query);
                            
                            while ($item = mysqli_fetch_assoc($items_result)):
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="uploads/<?= $item['image'] ?>" width="50" class="me-2 rounded">
                                            <?= $item['name'] ?>
                                        </div>
                                    </td>
                                    <td>Rp <?= number_format($item['price']) ?></td>
                                    <td><?= $item['qty'] ?></td>
                                    <td>Rp <?= number_format($item['price'] * $item['qty']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Aksi Konfirmasi -->
            <?php if ($order['payment_status'] == 'unpaid' && $order['payment_proof']): ?>
                <div class="confirm-section">
                    <h3 class="section-title"><i class="fas fa-check-circle"></i> Konfirmasi Pembayaran</h3>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Silakan periksa bukti pembayaran di atas. Jika sudah benar, klik "Setujui". 
                        Jika ada masalah, klik "Tolak" dan berikan alasan.
                    </div>
                    
                    <form method="POST" onsubmit="return confirmSubmit()">
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan (jika ditolak)</label>
                            <textarea class="form-control" name="rejection_reason" rows="3" 
                                    placeholder="Contoh: Bukti pembayaran tidak jelas, nominal tidak sesuai, dll."></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="approve" class="btn btn-approve">
                                <i class="fas fa-check"></i> Setujui Pembayaran
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-reject">
                                <i class="fas fa-times"></i> Tolak Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            <?php elseif ($order['payment_status'] == 'paid'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Pembayaran sudah disetujui!
                </div>
            <?php elseif ($order['payment_status'] == 'rejected'): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> Pembayaran ditolak!
                    <?php if ($order['payment_rejection_reason']): ?>
                        <br><strong>Alasan:</strong> <?= $order['payment_rejection_reason'] ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Pesanan tidak ditemukan!
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmSubmit() {
            const action = event.submitter.value;
            const reason = document.querySelector('textarea[name="rejection_reason"]').value;
            
            if (action === 'reject' && !reason.trim()) {
                alert('Silakan isi alasan penolakan!');
                return false;
            }
            
            const confirmMsg = action === 'approve' 
                ? 'Apakah Anda yakin ingin menyetujui pembayaran ini?' 
                : 'Apakah Anda yakin ingin menolak pembayaran ini?';
            
            return confirm(confirmMsg);
        }
    </script>
</body>
</html>