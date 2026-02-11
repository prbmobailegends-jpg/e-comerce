<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Include file koneksi database
include '../config/database.php';

// Handle blacklist action
if (isset($_GET['action']) && $_GET['action'] == 'blacklist' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Don't allow admin to blacklist themselves
    if ($user_id != $_SESSION['user']['id']) {
        mysqli_query($conn, "UPDATE users SET is_blacklisted = 1 WHERE id = $user_id");
        $_SESSION['success'] = "User berhasil di-blacklist";
    } else {
        $_SESSION['error'] = "Anda tidak bisa mem-blacklist diri sendiri";
    }
    
    header("Location: dashboard.php");
    exit;
}

// Handle unblacklist action
if (isset($_GET['action']) && $_GET['action'] == 'unblacklist' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE users SET is_blacklisted = 0 WHERE id = $user_id");
    $_SESSION['success'] = "User berhasil di-unblacklist";
    
    header("Location: dashboard.php");
    exit;
}

// Get category statistics for dashboard
 $category_stats = mysqli_query($conn, "
    SELECT c.name, c.icon, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY product_count DESC
");

// Get user statistics
 $total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'customer'"))['total'];
 $active_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'customer' AND (is_blacklisted IS NULL OR is_blacklisted = 0)"))['total'];
 $blacklisted_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'customer' AND is_blacklisted = 1"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Bustore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Dashboard Container */
        .dashboard-wrapper {
            background: #f5f7fa;
            min-height: 100vh;
            padding-top: 90px;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }
        
        /* Dashboard Header */
        .dashboard-header {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            border: 1px solid #f0f0f0;
        }
        
        .header-content h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .header-content p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid #f0f0f0;
        }
        
        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #ff5722, #ff9800);
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        }
        
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, rgba(255, 87, 34, 0.1), rgba(255, 152, 0, 0.1));
        }
        
        .card-icon img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .dashboard-card:hover .card-icon img {
            transform: scale(1.1);
        }
        
        .card-title {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .card-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .card-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #ff5722;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        
        .card-link:hover {
            color: #e64a19;
            transform: translateX(3px);
        }
        
        .card-link img {
            width: 16px;
            height: 16px;
            object-fit: contain;
            transition: transform 0.2s ease;
        }
        
        .card-link:hover img {
            transform: translateX(2px);
        }
        
        /* Dashboard Menu */
        .dashboard-menu {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 30px;
            border: 1px solid #f0f0f0;
        }
        
        .menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        
        .menu-header h3 {
            font-size: 1.4rem;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 18px;
            border-radius: 12px;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #2c3e50;
            border: 1px solid transparent;
        }
        
        .menu-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
            border-color: #ff5722;
        }
        
        .menu-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(255, 87, 34, 0.1), rgba(255, 152, 0, 0.1));
            border-radius: 10px;
            margin-right: 15px;
        }
        
        .menu-icon img {
            width: 24px;
            height: 24px;
            object-fit: contain;
            transition: transform 0.2s ease;
        }
        
        .menu-item:hover .menu-icon img {
            transform: scale(1.1);
        }
        
        .menu-text {
            font-weight: 500;
        }
        
        /* Categories Section */
        .categories-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 30px;
            border: 1px solid #f0f0f0;
        }
        
        .categories-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .category-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: #2c3e50;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .category-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .category-count {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* User Management Section */
        .user-management {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 30px;
            border: 1px solid #f0f0f0;
        }
        
        .user-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .user-stat-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .user-stat-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 15px;
        }
        
        .user-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .user-stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .user-table th {
            background-color: #f9f9f9;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .user-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .user-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f0f0f0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-name {
            font-weight: 500;
            color: #333;
        }
        
        .user-email {
            font-size: 0.85rem;
            color: #666;
        }
        
        .role-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .role-admin {
            background: #ffebee;
            color: #d32f2f;
        }
        
        .role-petugas {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .role-customer {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .blacklisted-badge {
            background: #ffebee;
            color: #d32f2f;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-small {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-blacklist {
            background: #f44336;
            color: white;
        }
        
        .btn-blacklist:hover {
            background: #d32f2f;
        }
        
        .btn-unblacklist {
            background: #4caf50;
            color: white;
        }
        
        .btn-unblacklist:hover {
            background: #388e3c;
        }
        
        /* Recent Activity */
        .recent-activity {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #f0f0f0;
        }
        
        .activity-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        
        .activity-header h3 {
            font-size: 1.4rem;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-item:hover {
            background: #f8f9fa;
            margin: 0 -30px;
            padding-left: 30px;
            padding-right: 30px;
        }
        
        .activity-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 10px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .activity-icon img {
            width: 24px;
            height: 24px;
            object-fit: contain;
            transition: transform 0.2s ease;
        }
        
        .activity-item:hover .activity-icon img {
            transform: scale(1.1);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 500;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .activity-time {
            color: #7f8c8d;
            font-size: 0.85rem;
        }
        
        /* Buttons */
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .btn img {
            width: 16px;
            height: 16px;
            object-fit: contain;
            transition: transform 0.2s ease;
        }
        
        .btn:hover img {
            transform: scale(1.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff5722, #ff9800);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 87, 34, 0.3);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #555;
            border: 1px solid #e0e0e0;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
        }
        
        /* Alert */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
            
            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
            
            .user-stats {
                grid-template-columns: 1fr;
            }
            
            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .user-table {
                font-size: 0.9rem;
            }
            
            .user-table th, .user-table td {
                padding: 10px 8px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>
    
    <div class="dashboard-wrapper">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div class="header-content">
                    <h1>Dashboard Admin</h1>
                    <p>Selamat datang, <?= $_SESSION['user']['name'] ?>!</p>
                </div>
                <div class="header-actions">
                    <a href="../index.php" class="btn btn-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <?= $_SESSION['success'] ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <span style="font-size: 2.5rem;">📦</span>
                    </div>
                    <div class="card-title">Total Produk</div>
                    <div class="card-value">
                        <?php 
                        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
                        $row = mysqli_fetch_assoc($result);
                        echo $row['total'];
                        ?>
                    </div>
                    <a href="products.php" class="card-link">
                        Kelola Produk
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 13h6m-3 0v6m0 0h6m-6 0v6m0 0h6m-6 0v6"></path>
                        </svg>
                    </a>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">
                        <span style="font-size: 2.5rem;">📊</span>
                    </div>
                    <div class="card-title">Pesanan Baru</div>
                    <div class="card-value">
                        <?php 
                        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='Menunggu Pembayaran'");
                        $row = mysqli_fetch_assoc($result);
                        echo $row['total'];
                        ?>
                    </div>
                    <a href="orders.php" class="card-link">
                        Lihat Pesanan
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <path d="M9 14l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </a>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">
                        <span style="font-size: 2.5rem;">💰</span>
                    </div>
                    <div class="card-title">Pendapatan Bulan Ini</div>
                    <div class="card-value">
                        <?php 
                        $current_month = date('m');
                        $current_year = date('Y');
                        $result = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE status='Selesai' AND MONTH(created_at)=$current_month AND YEAR(created_at)=$current_year");
                        $row = mysqli_fetch_assoc($result);
                        echo "Rp " . number_format($row['total'] ?? 0);
                        ?>
                    </div>
                    <a href="reports.php" class="card-link">
                        Lihat Laporan
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 17V7H7a2 2 0 0 0-2-2V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9z"></path>
                            <polyline points="9 11 15 4 7 11 11"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Categories Section -->
            <div class="categories-section">
                <div class="categories-header">
                    <h3>Kategori Produk</h3>
                </div>
                <div class="categories-grid">
                    <?php while ($category = mysqli_fetch_assoc($category_stats)): ?>
                    <a href="categories.php" class="category-card">
                        <div class="category-icon"><?= $category['icon'] ?></div>
                        <div class="category-name"><?= $category['name'] ?></div>
                        <div class="category-count"><?= $category['product_count'] ?> produk</div>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="dashboard-menu">
                <div class="menu-header">
                    <h3>Menu Admin</h3>
                </div>
                <div class="menu-grid">
                    <a href="products.php" class="menu-item">
                        <div class="menu-icon">
                            <span style="font-size: 1.5rem;">📦</span>
                        </div>
                        <div class="menu-text">Kelola Produk</div>
                    </a>
                    <a href="orders.php" class="menu-item">
                        <div class="menu-icon">
                            <span style="font-size: 1.5rem;">📊</span>
                        </div>
                        <div class="menu-text">Data Pesanan</div>
                    </a>
                    <a href="categories.php" class="menu-item">
                        <div class="menu-icon">
                            <span style="font-size: 1.5rem;">📂</span>
                        </div>
                        <div class="menu-text">Kategori</div>
                    </a>
                    <a href="users.php" class="menu-item">
                        <div class="menu-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-284 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="menu-text">Pengguna</div>
                    </a>
                    <a href="reports.php" class="menu-item">
                        <div class="menu-icon">
                            <span style="font-size: 1.5rem;">📊</span>
                        </div>
                        <div class="menu-text">Laporan</div>
                    </a>
                    <a href="upload_image.php" class="menu-item">
                        <div class="menu-icon">
                            <span style="font-size: 1.5rem;">📤</span>
                        </div>
                        <div class="menu-text">Upload Gambar Produk</div>
                    </a>
                    <a href="backup.php" class="menu-item">
                        <div class="menu-icon">
                            <span style="font-size: 1.5rem;">🛡️</span>
                        </div>
                        <div class="menu-text">Backup Database</div>
                    </a>
                    <a href="../auth/logout.php" class="menu-item">
                        <div class="menu-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1 2 2z"></path>
                                <polyline points="16 17 21 12 14 12 21"></polyline>
                            </svg>
                        </div>
                        <div class="menu-text">Logout</div>
                    </a>
                </div>
            </div>
            
            <!-- User Management Section -->
            <div class="user-management">
                <div class="activity-header">
                    <h3>Manajemen Pengguna</h3>
                </div>
                
                <div class="user-stats">
                    <div class="user-stat-card">
                        <div class="user-stat-icon">
                            <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-284 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="user-stat-value"><?= $total_users ?></div>
                        <div class="user-stat-label">Total Pengguna</div>
                    </div>
                    
                    <div class="user-stat-card">
                        <div class="user-stat-icon">
                            <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="user-stat-value"><?= $active_users ?></div>
                        <div class="user-stat-label">Aktif</div>
                    </div>
                    
                    <div class="user-stat-card">
                        <div class="user-stat-icon">
                            <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <div class="user-stat-value"><?= $blacklisted_users ?></div>
                        <div class="user-stat-label">Di-blacklist</div>
                    </div>
                </div>
                
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Pengguna</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users_query = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC LIMIT 10");
                        while ($user = mysqli_fetch_assoc($users_query)) {
                            $is_current_user = $user['id'] == $_SESSION['user']['id'];
                        ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <img src="<?= !empty($user['profile_pic']) ? '../assets/img/profiles/' . $user['profile_pic'] : '../assets/img/icons/icon-user.png' ?>" alt="Profile" class="user-avatar">
                                    <div>
                                        <div class="user-name"><?= $user['name'] ?></div>
                                        <div class="user-email"><?= $user['email'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-<?= $user['role'] ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (isset($user['is_blacklisted']) && $user['is_blacklisted'] == 1): ?>
                                    <span class="blacklisted-badge">Di-blacklist</span>
                                <?php else: ?>
                                    <span style="color: #4caf50; font-weight: 500;">Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if (!$is_current_user): ?>
                                        <?php if (isset($user['is_blacklisted']) && $user['is_blacklisted'] == 1): ?>
                                            <a href="?action=unblacklist&id=<?= $user['id'] ?>" class="btn-small btn-unblacklist">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Unblacklist
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=blacklist&id=<?= $user['id'] ?>" class="btn-small btn-blacklist" onclick="return confirm('Apakah Anda yakin ingin mem-blacklist user ini?')">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                                </svg>
                                                Blacklist
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <div class="recent-activity">
                <div class="activity-header">
                    <h3>Aktivitas Terkini</h3>
                </div>
                <ul class="activity-list">
                    <?php
                    $recent_orders = mysqli_query($conn, "SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
                    while ($order = mysqli_fetch_assoc($recent_orders)) {
                    ?>
                    <li class="activity-item">
                        <div class="activity-icon">
                            <span style="font-size: 1.5rem;">📦</span>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Pesanan baru dari <?= $order['customer_name'] ?? 'Guest' ?></div>
                            <div class="activity-time"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>