<?php
session_start();
include '../config/database.php';

// Check permissions
if ($_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Get order ID
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID pesanan tidak valid";
    header("Location: orders.php");
    exit;
}

// Get order data
 $order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id=$id"));

if (!$order) {
    $_SESSION['error'] = "Pesanan tidak ditemukan";
    header("Location: orders.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validate status
    $valid_statuses = ['Menunggu Pembayaran', 'Dibayar', 'Diproses', 'Dikirim', 'Selesai'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Status tidak valid";
    } else {
        // Update order status
        $update_query = "UPDATE orders SET status='$status' WHERE id=$id";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = "Status pesanan berhasil diperbarui";
            
            // Log the activity (optional)
            $admin_id = $_SESSION['user']['id'];
            $log_query = "INSERT INTO activity_logs (user_id, action, table_name, record_id, details, created_at) 
                          VALUES ('$admin_id', 'UPDATE', 'orders', '$id', 'Updated status to: $status', NOW())";
            mysqli_query($conn, $log_query);
            
        } else {
            $_SESSION['error'] = "Gagal memperbarui status: " . mysqli_error($conn);
        }
    }
    
    // Redirect to order detail
    header("Location: order_detail.php?id=$id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pesanan #<?= $order['id'] ?> - Bustore Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .edit-container {
            max-width: 700px;
            margin: 100px auto 40px;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .edit-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .edit-header h2 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .edit-header p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #ff5722;
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
        }
        
        .order-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .order-info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .order-info-item:last-child {
            margin-bottom: 0;
        }
        
        .order-info-label {
            font-weight: 500;
            color: #555;
        }
        
        .order-info-value {
            color: #333;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #ff5722;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e64a19;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #555;
            margin-right: 10px;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .status-preview {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-left: 10px;
        }
        
        .status-menunggu { background: #fff3cd; color: #856404; }
        .status-dibayar { background: #cce5ff; color: #004085; }
        .status-diproses { background: #d1ecf1; color: #0c5460; }
        .status-dikirim { background: #d4edda; color: #155724; }
        .status-selesai { background: #d1ecf1; color: #0c5460; }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-timeline {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .status-timeline h4 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ddd;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 15px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ddd;
        }
        
        .timeline-item.completed::before {
            background: #4caf50;
        }
        
        .timeline-item.active::before {
            background: #ff5722;
        }
        
        .timeline-label {
            font-weight: 500;
            margin-bottom: 2px;
        }
        
        .timeline-time {
            font-size: 0.8rem;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="edit-container">
        <div class="edit-header">
            <h2>Edit Pesanan #<?= $order['id'] ?></h2>
            <p>Perbarui status pesanan dan informasi lainnya</p>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <div class="order-info">
            <div class="order-info-item">
                <span class="order-info-label">ID Pesanan:</span>
                <span class="order-info-value">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="order-info-item">
                <span class="order-info-label">Tanggal:</span>
                <span class="order-info-value"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="order-info-item">
                <span class="order-info-label">Total:</span>
                <span class="order-info-value">Rp <?= number_format($order['total_price']) ?></span>
            </div>
            <div class="order-info-item">
                <span class="order-info-label">Status Saat Ini:</span>
                <span class="order-info-value">
                    <?= $order['status'] ?>
                    <span class="status-preview status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>"><?= $order['status'] ?></span>
                </span>
            </div>
        </div>
        
        <div class="status-timeline">
            <h4>Alur Status Pesanan</h4>
            <div class="timeline">
                <div class="timeline-item <?= in_array($order['status'], ['Menunggu Pembayaran', 'Dibayar', 'Diproses', 'Dikirim', 'Selesai']) ? 'completed' : '' ?>">
                    <div class="timeline-label">Menunggu Pembayaran</div>
                    <div class="timeline-time">Status awal pesanan</div>
                </div>
                <div class="timeline-item <?= in_array($order['status'], ['Dibayar', 'Diproses', 'Dikirim', 'Selesai']) ? 'completed' : '' ?>">
                    <div class="timeline-label">Dibayar</div>
                    <div class="timeline-time">Pembayaran dikonfirmasi</div>
                </div>
                <div class="timeline-item <?= in_array($order['status'], ['Diproses', 'Dikirim', 'Selesai']) ? 'completed' : '' ?>">
                    <div class="timeline-label">Diproses</div>
                    <div class="timeline-time">Pesanan sedang diproses</div>
                </div>
                <div class="timeline-item <?= in_array($order['status'], ['Dikirim', 'Selesai']) ? 'completed' : '' ?>">
                    <div class="timeline-label">Dikirim</div>
                    <div class="timeline-time">Pesanan dalam pengiriman</div>
                </div>
                <div class="timeline-item <?= $order['status'] == 'Selesai' ? 'completed' : ($order['status'] == 'Dikirim' ? 'active' : '') ?>">
                    <div class="timeline-label">Selesai</div>
                    <div class="timeline-time">Pesanan selesai</div>
                </div>
            </div>
        </div>
        
        <form method="post" id="editForm">
            <div class="form-group">
                <label for="status">Status Pesanan</label>
                <select name="status" id="status" required>
                    <option value="">-- Pilih Status --</option>
                    <option value="Menunggu Pembayaran" <?= $order['status']=='Menunggu Pembayaran'?'selected':'' ?>>Menunggu Pembayaran</option>
                    <option value="Dibayar" <?= $order['status']=='Dibayar'?'selected':'' ?>>Dibayar</option>
                    <option value="Diproses" <?= $order['status']=='Diproses'?'selected':'' ?>>Diproses</option>
                    <option value="Dikirim" <?= $order['status']=='Dikirim'?'selected':'' ?>>Dikirim</option>
                    <option value="Selesai" <?= $order['status']=='Selesai'?'selected':'' ?>>Selesai</option>
                </select>
            </div>
            
            <div style="display: flex; margin-top: 30px;">
                <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali
                </a>
                <button type="submit" name="update_status" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Update Status
                </button>
            </div>
        </form>
    </div>
    
    <script>
        // Form validation before submit
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const statusSelect = document.getElementById('status');
            
            if (!statusSelect.value) {
                e.preventDefault();
                alert('Silakan pilih status pesanan');
                statusSelect.focus();
                return false;
            }
            
            // Confirm before updating
            const currentStatus = '<?= $order['status'] ?>';
            const newStatus = statusSelect.value;
            
            if (currentStatus !== newStatus) {
                const confirmUpdate = confirm(`Apakah Anda yakin ingin mengubah status dari "${currentStatus}" menjadi "${newStatus}"?`);
                if (!confirmUpdate) {
                    e.preventDefault();
                    return false;
                }
            }
        });
        
        // Add loading state to button
        document.querySelector('button[type="submit"]').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid #ffffff; border-radius: 50%; border-top-color: transparent; animation: spin 1s linear infinite;"></span> Memperbarui...';
        });
    </script>
    
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</body>
</html>