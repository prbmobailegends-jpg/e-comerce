<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

 $user_id = $_SESSION['user']['id'];

// Get orders by status
 $all_orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC");
 $unpaid_orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id AND status='Menunggu Pembayaran'");
 $processing_orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id AND status IN ('Dibayar', 'Diproses')");
 $shipped_orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id AND status='Dikirim'");
 $completed_orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id AND status='Selesai'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Bustore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .orders-header h2 {
            font-size: 2rem;
            color: #333;
            position: relative;
            display: inline-block;
        }
        
        .orders-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: #ff5722;
            border-radius: 2px;
        }
        
        .search-bar {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 25px;
            padding: 10px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            width: 300px;
            border: 1px solid #e0e0e0;
        }
        
        .search-bar input {
            border: none;
            outline: none;
            flex: 1;
            padding: 5px;
        }
        
        .search-bar button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-left: 8px;
        }
        
        .orders-tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 1px solid #f0f0f0;
            overflow-x: auto;
            gap: 5px;
        }
        
        .tab-btn {
            padding: 15px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #757575;
            position: relative;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            font-size: 14px;
        }
        
        .tab-btn:hover {
            color: #ff5722;
            background: rgba(255, 87, 34, 0.05);
        }
        
        .tab-btn.active {
            color: #ff5722;
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #ff5722;
        }
        
        .tab-count {
            background: #f0f0f0;
            color: #666;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            min-width: 20px;
            text-align: center;
        }
        
        .tab-btn.active .tab-count {
            background: #ff5722;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .orders-list {
            display: grid;
            gap: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .order-info {
            flex: 1;
        }
        
        .order-id {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .order-date {
            color: #757575;
            font-size: 13px;
            margin-top: 2px;
        }
        
        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .status-unpaid { background: #fff3cd; color: #856404; }
        .status-paid { background: #cce5ff; color: #004085; }
        .status-dibayar { background: #cce5ff; color: #004085; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-diproses { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-dikirim { background: #d4edda; color: #155724; }
        .status-completed { background: #e2e3e5; color: #383d41; }
        .status-selesai { background: #e2e3e5; color: #383d41; }
        
        .order-items {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            overflow-x: auto;
            padding: 5px 0;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 200px;
            background: #fafafa;
            padding: 10px;
            border-radius: 8px;
        }
        
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            background: white;
        }
        
        .order-item-details {
            flex: 1;
            min-width: 0;
        }
        
        .order-item-name {
            font-weight: 500;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 14px;
        }
        
        .order-item-qty {
            color: #757575;
            font-size: 12px;
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .order-total {
            font-weight: 600;
            color: #ff5722;
            font-size: 16px;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
        }
        
        .btn-primary {
            background: #ff5722;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e64a19;
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #555;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f0f0f0;
        }
        
        .empty-orders-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            background: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #ccc;
        }
        
        .empty-orders h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .empty-orders p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            padding: 8px 16px;
            background: #f0f0f0;
            color: #555;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .back-button:hover {
            background: #e0e0e0;
        }
        
        @media (max-width: 768px) {
            .orders-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-bar {
                width: 100%;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="orders-container">
        <a href="../index.php" class="back-button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Beranda
        </a>
        
        <div class="orders-header">
            <h2>Pesanan Saya</h2>
            <div class="search-bar">
                <input type="text" placeholder="Cari pesanan..." id="search_input">
                <button type="button" id="search_btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="orders-tabs">
            <button class="tab-btn active" onclick="openTab('all')">
                Semua
                <span class="tab-count"><?= mysqli_num_rows($all_orders) ?></span>
            </button>
            <button class="tab-btn" onclick="openTab('unpaid')">
                Menunggu Pembayaran
                <span class="tab-count"><?= mysqli_num_rows($unpaid_orders) ?></span>
            </button>
            <button class="tab-btn" onclick="openTab('processing')">
                Diproses
                <span class="tab-count"><?= mysqli_num_rows($processing_orders) ?></span>
            </button>
            <button class="tab-btn" onclick="openTab('shipped')">
                Dikirim
                <span class="tab-count"><?= mysqli_num_rows($shipped_orders) ?></span>
            </button>
            <button class="tab-btn" onclick="openTab('completed')">
                Selesai
                <span class="tab-count"><?= mysqli_num_rows($completed_orders) ?></span>
            </button>
        </div>
        
        <!-- Semua Pesanan Tab -->
        <div id="all" class="tab-content active">
            <?php if (mysqli_num_rows($all_orders) > 0): ?>
                <div class="orders-list">
                    <?php 
                    // Reset pointer
                    mysqli_data_seek($all_orders, 0);
                    while ($order = mysqli_fetch_assoc($all_orders)): 
                    ?>
                        <?php
                        // Get order items
                        $order_items = mysqli_query($conn, "SELECT oi.*, p.name, p.image, p.category_id FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = {$order['id']}");
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <div class="order-id">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
                                    <div class="order-date"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                                </div>
                                <span class="order-status status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>"><?= $order['status'] ?></span>
                            </div>
                            
                            <div class="order-items">
                                <?php while ($item = mysqli_fetch_assoc($order_items)): ?>
                                    <div class="order-item">
                                        <?php 
                                        // Menentukan path gambar tanpa image_helper
                                        $image_path = '../assets/img/products/' . $item['image'];
                                        if (!file_exists($image_path)) {
                                            // Jika file tidak ada, gunakan gambar default
                                            $image_path = '../assets/img/products/default.jpg';
                                        }
                                        ?>
                                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <div class="order-item-details">
                                            <div class="order-item-name" title="<?= htmlspecialchars($item['name']) ?>"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="order-item-qty">Qty: <?= $item['qty'] ?></div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="order-footer">
                                <div class="order-total">Total: Rp <?= number_format($order['total_price'], 0, ',', '.') ?></div>
                                <div class="order-actions">
                                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary">Detail</a>
                                    <?php if ($order['status'] == 'Menunggu Pembayaran'): ?>
                                        <a href="upload_payment.php?id=<?= $order['id'] ?>" class="btn btn-secondary">Upload Bukti</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-orders">
                    <div class="empty-orders-icon">📦</div>
                    <h3>Anda belum memiliki pesanan</h3>
                    <p>Belanja sekarang dan dapatkan produk terbaik dari kami</p>
                    <a href="../index.php" class="btn btn-primary">Belanja Sekarang</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Tab lainnya dengan struktur yang sama... -->
        <!-- Menunggu Pembayaran Tab -->
        <div id="unpaid" class="tab-content">
            <?php if (mysqli_num_rows($unpaid_orders) > 0): ?>
                <div class="orders-list">
                    <?php 
                    mysqli_data_seek($unpaid_orders, 0);
                    while ($order = mysqli_fetch_assoc($unpaid_orders)): 
                    ?>
                        <?php
                        $order_items = mysqli_query($conn, "SELECT oi.*, p.name, p.image, p.category_id FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = {$order['id']}");
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <div class="order-id">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
                                    <div class="order-date"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                                </div>
                                <span class="order-status status-unpaid">Menunggu Pembayaran</span>
                            </div>
                            
                            <div class="order-items">
                                <?php while ($item = mysqli_fetch_assoc($order_items)): ?>
                                    <div class="order-item">
                                        <?php 
                                        $image_path = '../assets/img/products/' . $item['image'];
                                        if (!file_exists($image_path)) {
                                            $image_path = '../assets/img/products/default.jpg';
                                        }
                                        ?>
                                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <div class="order-item-details">
                                            <div class="order-item-name" title="<?= htmlspecialchars($item['name']) ?>"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="order-item-qty">Qty: <?= $item['qty'] ?></div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="order-footer">
                                <div class="order-total">Total: Rp <?= number_format($order['total_price'], 0, ',', '.') ?></div>
                                <div class="order-actions">
                                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary">Detail</a>
                                    <a href="upload_payment.php?id=<?= $order['id'] ?>" class="btn btn-secondary">Upload Bukti</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-orders">
                    <div class="empty-orders-icon">💳</div>
                    <h3>Tidak ada pesanan yang menunggu pembayaran</h3>
                    <p>Semua pesanan Anda telah dibayar atau tidak ada pesanan yang aktif</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Diproses Tab -->
        <div id="processing" class="tab-content">
            <?php if (mysqli_num_rows($processing_orders) > 0): ?>
                <div class="orders-list">
                    <?php 
                    mysqli_data_seek($processing_orders, 0);
                    while ($order = mysqli_fetch_assoc($processing_orders)): 
                    ?>
                        <?php
                        $order_items = mysqli_query($conn, "SELECT oi.*, p.name, p.image, p.category_id FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = {$order['id']}");
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <div class="order-id">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
                                    <div class="order-date"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                                </div>
                                <span class="order-status status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>"><?= $order['status'] ?></span>
                            </div>
                            
                            <div class="order-items">
                                <?php while ($item = mysqli_fetch_assoc($order_items)): ?>
                                    <div class="order-item">
                                        <?php 
                                        $image_path = '../assets/img/products/' . $item['image'];
                                        if (!file_exists($image_path)) {
                                            $image_path = '../assets/img/products/default.jpg';
                                        }
                                        ?>
                                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <div class="order-item-details">
                                            <div class="order-item-name" title="<?= htmlspecialchars($item['name']) ?>"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="order-item-qty">Qty: <?= $item['qty'] ?></div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="order-footer">
                                <div class="order-total">Total: Rp <?= number_format($order['total_price'], 0, ',', '.') ?></div>
                                <div class="order-actions">
                                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary">Detail</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-orders">
                    <div class="empty-orders-icon">⚙️</div>
                    <h3>Tidak ada pesanan yang sedang diproses</h3>
                    <p>Pesanan Anda akan muncul di sini setelah pembayaran dikonfirmasi</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Dikirim Tab -->
        <div id="shipped" class="tab-content">
            <?php if (mysqli_num_rows($shipped_orders) > 0): ?>
                <div class="orders-list">
                    <?php 
                    mysqli_data_seek($shipped_orders, 0);
                    while ($order = mysqli_fetch_assoc($shipped_orders)): 
                    ?>
                        <?php
                        $order_items = mysqli_query($conn, "SELECT oi.*, p.name, p.image, p.category_id FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = {$order['id']}");
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <div class="order-id">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
                                    <div class="order-date"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                                </div>
                                <span class="order-status status-shipped">Dikirim</span>
                            </div>
                            
                            <div class="order-items">
                                <?php while ($item = mysqli_fetch_assoc($order_items)): ?>
                                    <div class="order-item">
                                        <?php 
                                        $image_path = '../assets/img/products/' . $item['image'];
                                        if (!file_exists($image_path)) {
                                            $image_path = '../assets/img/products/default.jpg';
                                        }
                                        ?>
                                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <div class="order-item-details">
                                            <div class="order-item-name" title="<?= htmlspecialchars($item['name']) ?>"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="order-item-qty">Qty: <?= $item['qty'] ?></div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="order-footer">
                                <div class="order-total">Total: Rp <?= number_format($order['total_price'], 0, ',', '.') ?></div>
                                <div class="order-actions">
                                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary">Detail</a>
                                    <a href="#" class="btn btn-secondary" onclick="trackOrder(<?= $order['id'] ?>)">Lacak</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-orders">
                    <div class="empty-orders-icon">🚚</div>
                    <h3>Tidak ada pesanan yang sedang dikirim</h3>
                    <p>Pesanan Anda akan muncul di sini setelah dikirim oleh penjual</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Selesai Tab -->
        <div id="completed" class="tab-content">
            <?php if (mysqli_num_rows($completed_orders) > 0): ?>
                <div class="orders-list">
                    <?php 
                    mysqli_data_seek($completed_orders, 0);
                    while ($order = mysqli_fetch_assoc($completed_orders)): 
                    ?>
                        <?php
                        $order_items = mysqli_query($conn, "SELECT oi.*, p.name, p.image, p.category_id FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = {$order['id']}");
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <div class="order-id">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
                                    <div class="order-date"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                                </div>
                                <span class="order-status status-completed">Selesai</span>
                            </div>
                            
                            <div class="order-items">
                                <?php while ($item = mysqli_fetch_assoc($order_items)): ?>
                                    <div class="order-item">
                                        <?php 
                                        $image_path = '../assets/img/products/' . $item['image'];
                                        if (!file_exists($image_path)) {
                                            $image_path = '../assets/img/products/default.jpg';
                                        }
                                        ?>
                                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <div class="order-item-details">
                                            <div class="order-item-name" title="<?= htmlspecialchars($item['name']) ?>"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="order-item-qty">Qty: <?= $item['qty'] ?></div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="order-footer">
                                <div class="order-total">Total: Rp <?= number_format($order['total_price'], 0, ',', '.') ?></div>
                                <div class="order-actions">
                                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary">Detail</a>
                                    <a href="#" class="btn btn-secondary" onclick="rateProducts(<?= $order['id'] ?>)">Rating</a>
                                    <a href="#" class="btn btn-secondary" onclick="buyAgain(<?= $order['id'] ?>)">Beli Lagi</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-orders">
                    <div class="empty-orders-icon">✅</div>
                    <h3>Belum ada pesanan yang selesai</h3>
                    <p>Pesanan Anda yang telah selesai akan muncul di sini</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function openTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show the selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to the clicked button
            event.target.classList.add('active');
        }
        
        function searchOrders() {
            const searchTerm = document.getElementById('search_input').value.toLowerCase();
            const tableRows = document.querySelectorAll('.orders-table tbody tr');
            
            tableRows.forEach(row => {
                const orderId = row.querySelector('.order-id').textContent.toLowerCase();
                const orderDate = row.querySelector('.order-date').textContent.toLowerCase();
                
                const matchesSearch = orderId.includes(searchTerm) || orderDate.includes(searchTerm);
                
                if (matchesSearch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Add event listener for search input
        document.getElementById('search_input').addEventListener('input', searchOrders);
        
        function trackOrder(orderId) {
            alert('Lacak pengiriman untuk pesanan #' + orderId + '. Fitur ini akan segera tersedia.');
        }
        
        function rateProducts(orderId) {
            window.location.href = '../profile.php#ratings';
        }
        
        function buyAgain(orderId) {
            alert('Menambahkan produk dari pesanan #' + orderId + ' ke keranjang. Fitur ini akan segera tersedia.');
        }
    </script>
</body>
</html>