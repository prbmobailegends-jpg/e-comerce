<?php
session_start();
include '../config/database.php';

if ($_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle date range filter
 $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
 $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
 $report_type = isset($_GET['type']) ? $_GET['type'] : 'sales';

// Get report data based on type
if ($report_type == 'sales') {
    $report_data = mysqli_query($conn, "
        SELECT DATE(created_at) as date, 
               COUNT(*) as total_orders,
               SUM(total_price) as total_revenue,
               COUNT(CASE WHEN status = 'Selesai' THEN 1 END) as completed_orders
        FROM orders 
        WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    
    $chart_data = [];
    while ($row = mysqli_fetch_assoc($report_data)) {
        $chart_data[] = [
            'date' => $row['date'],
            'orders' => $row['total_orders'],
            'revenue' => $row['total_revenue'],
            'completed' => $row['completed_orders']
        ];
    }
} elseif ($report_type == 'products') {
    $report_data = mysqli_query($conn, "
        SELECT p.*, c.name as category_name,
               (SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = p.id) as total_sold,
               (SELECT SUM(oi.qty) FROM order_items oi WHERE oi.product_id = p.id) as total_quantity
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY total_sold DESC
    ");
} elseif ($report_type == 'customers') {
    $report_data = mysqli_query($conn, "
        SELECT u.*,
               COUNT(o.id) as total_orders,
               SUM(o.total_price) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE u.role = 'customer'
        GROUP BY u.id
        ORDER BY total_spent DESC
    ");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Bustore Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .reports-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            font-size: 2rem;
            color: #333;
            position: relative;
            padding-bottom: 10px;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: #ff5722;
            border-radius: 2px;
        }
        
        .page-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #ff5722;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e64a19;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 87, 34, 0.2);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #555;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }
        
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 500;
            color: #555;
        }
        
        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .chart-wrapper {
            position: relative;
            height: 400px;
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .data-table {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background-color: #f9f9f9;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .data-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="reports-container">
        <div class="page-header">
            <h1 class="page-title">Laporan</h1>
            <div class="page-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9V2h12v7"></path>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Cetak
                </button>
            </div>
        </div>
        
        <div class="filter-section">
            <form method="get" class="filter-form">
                <div class="form-group">
                    <label for="type">Jenis Laporan</label>
                    <select name="type" id="type" onchange="this.form.submit()">
                        <option value="sales" <?= $report_type == 'sales' ? 'selected' : '' ?>>Penjualan</option>
                        <option value="products" <?= $report_type == 'products' ? 'selected' : '' ?>>Produk</option>
                        <option value="customers" <?= $report_type == 'customers' ? 'selected' : '' ?>>Pelanggan</option>
                    </select>
                </div>
                
                <?php if ($report_type == 'sales'): ?>
                <div class="form-group">
                    <label for="start_date">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" value="<?= $start_date ?>" onchange="this.form.submit()">
                </div>
                <div class="form-group">
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" value="<?= $end_date ?>" onchange="this.form.submit()">
                </div>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if ($report_type == 'sales'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(255, 87, 34, 0.1); color: #ff5722;">📊</div>
                    <div class="stat-value">
                        <?php 
                        $total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'"))['total'];
                        echo "Rp " . number_format($total_revenue ?? 0);
                        ?>
                    </div>
                    <div class="stat-label">Total Pendapatan</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(33, 150, 243, 0.1); color: #2196F3;">📦</div>
                    <div class="stat-value">
                        <?php 
                        $total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'"))['total'];
                        echo number_format($total_orders ?? 0);
                        ?>
                    </div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(76, 175, 80, 0.1); color: #4caf50;">✅</div>
                    <div class="stat-value">
                        <?php 
                        $completed_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Selesai' AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'"))['total'];
                        echo number_format($completed_orders ?? 0);
                        ?>
                    </div>
                    <div class="stat-label">Pesanan Selesai</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">💰</div>
                    <div class="stat-value">
                        <?php 
                        $avg_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(total_price) as avg FROM orders WHERE status = 'Selesai' AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'"))['avg'];
                        echo "Rp " . number_format($avg_order ?? 0);
                        ?>
                    </div>
                    <div class="stat-label">Rata-rata Pesanan</div>
                </div>
            </div>
            
            <div class="chart-container">
                <h3 style="margin-bottom: 20px;">Grafik Penjualan</h3>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            
            <div class="data-table">
                <h3 style="margin-bottom: 20px;">Detail Penjualan Harian</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Total Pesanan</th>
                            <th>Pesanan Selesai</th>
                            <th>Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chart_data as $data): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($data['date'])) ?></td>
                            <td><?= number_format($data['orders']) ?></td>
                            <td><?= number_format($data['completed']) ?></td>
                            <td>Rp <?= number_format($data['revenue']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($report_type == 'products'): ?>
            <div class="data-table">
                <h3 style="margin-bottom: 20px;">Laporan Produk</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Terjual</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($report_data, 0);
                        while ($product = mysqli_fetch_assoc($report_data)): 
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= $product['category_name'] ?></td>
                            <td>Rp <?= number_format($product['price']) ?></td>
                            <td><?= number_format($product['stock']) ?></td>
                            <td><?= number_format($product['total_sold']) ?></td>
                            <td>Rp <?= number_format($product['price'] * $product['total_sold']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($report_type == 'customers'): ?>
            <div class="data-table">
                <h3 style="margin-bottom: 20px;">Laporan Pelanggan</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Total Pesanan</th>
                            <th>Total Belanja</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($report_data, 0);
                        while ($customer = mysqli_fetch_assoc($report_data)): 
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['name']) ?></td>
                            <td><?= htmlspecialchars($customer['email']) ?></td>
                            <td><?= number_format($customer['total_orders']) ?></td>
                            <td>Rp <?= number_format($customer['total_spent']) ?></td>
                            <td>
                                <?php if (isset($customer['is_blacklisted']) && $customer['is_blacklisted'] == 1): ?>
                                    <span class="badge badge-danger">Blacklist</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($report_type == 'sales'): ?>
    <script>
        // Chart.js configuration
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_map(function($item) {
                    return date('d M', strtotime($item['date']));
                }, $chart_data)) ?>,
                datasets: [{
                    label: 'Pendapatan',
                    data: <?= json_encode(array_map(function($item) {
                        return $item['revenue'];
                    }, $chart_data)) ?>,
                    borderColor: '#ff5722',
                    backgroundColor: 'rgba(255, 87, 34, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Pesanan',
                    data: <?= json_encode(array_map(function($item) {
                        return $item['orders'];
                    }, $chart_data)) ?>,
                    borderColor: '#2196F3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>