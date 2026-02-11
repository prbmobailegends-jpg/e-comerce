<?php
session_start();
include '../config/database.php';

if ($_SESSION['user']['role'] != 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pesanan - Bustore Petugas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container {
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
            background: #2196F3;
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
            background: #2196F3;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1976D2;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.2);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #555;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }
        
        .filter-bar {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filter-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-group label {
            font-weight: 500;
            color: #555;
        }
        
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            min-width: 150px;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background: #f9f9f9;
            border-radius: 25px;
            padding: 8px 15px;
            width: 300px;
            border: 1px solid #e0e0e0;
        }
        
        .search-box input {
            border: none;
            background: none;
            outline: none;
            flex: 1;
            padding: 5px;
        }
        
        .search-box button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-left: 8px;
        }
        
        .orders-table-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th {
            background-color: #f9f9f9;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #f0f0f0;
            position: sticky;
            top: 0;
        }
        
        .orders-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .orders-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .order-id {
            font-weight: 600;
            color: #333;
        }
        
        .customer-name {
            font-weight: 500;
        }
        
        .order-total {
            font-weight: 600;
            color: #2196F3;
        }
        
        .order-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .status-menunggu { background: #fff3cd; color: #856404; }
        .status-dibayar { background: #cce5ff; color: #004085; }
        .status-diproses { background: #d1ecf1; color: #0c5460; }
        .status-dikirim { background: #d4edda; color: #155724; }
        .status-selesai { background: #e2e3e5; color: #383d41; }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-detail {
            background: #007bff;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        .btn-detail:hover {
            background: #0056b3;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #212529;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        .btn-edit:hover {
            background: #e0a800;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 25px;
            gap: 5px;
        }
        
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .pagination a:hover {
            background: #2196F3;
            color: white;
            border-color: #2196F3;
        }
        
        .pagination a.active {
            background: #2196F3;
            color: white;
            border-color: #2196F3;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                width: 100%;
            }
            
            .orders-table {
                font-size: 0.9rem;
            }
            
            .orders-table th, .orders-table td {
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

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Data Pesanan</h1>
            <div class="page-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali ke Dashboard
                </a>
                <a href="export_orders.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Export Data
                </a>
            </div>
        </div>
        
        <div class="filter-bar">
            <div class="filter-options">
                <div class="filter-group">
                    <label for="status-filter">Status:</label>
                    <select id="status-filter">
                        <option value="">Semua Status</option>
                        <option value="Menunggu Pembayaran">Menunggu Pembayaran</option>
                        <option value="Dibayar">Dibayar</option>
                        <option value="Diproses">Diproses</option>
                        <option value="Dikirim">Dikirim</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="date-filter">Tanggal:</label>
                    <select id="date-filter">
                        <option value="">Semua Tanggal</option>
                        <option value="today">Hari Ini</option>
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan Ini</option>
                        <option value="year">Tahun Ini</option>
                    </select>
                </div>
            </div>
            <div class="search-box">
                <input type="text" placeholder="Cari pesanan..." id="search-input">
                <button type="button" id="search-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="orders-table-container">
            <?php
            $no = 1;
            $q = mysqli_query($conn, "SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC");
            $order_count = mysqli_num_rows($q);
            
            if ($order_count > 0) {
            ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Order</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($o = mysqli_fetch_assoc($q)) { ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="order-id">#<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></td>
                        <td class="customer-name"><?= $o['customer_name'] ?? 'Guest' ?></td>
                        <td class="order-total">Rp <?= number_format($o['total_price']) ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $o['status'])) ?>">
                                <?= $o['status'] ?>
                            </span>
                        </td>
                        <td class="order-date"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="order_detail.php?id=<?= $o['id'] ?>" class="btn btn-detail">Detail</a>
                                <a href="order_edit.php?id=<?= $o['id'] ?>" class="btn btn-edit">Edit</a>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <div class="pagination">
                <a href="#" class="active">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">></a>
            </div>
            <?php } else { ?>
            <div class="empty-state">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 2v6"></path>
                    <path d="M15 2v6"></path>
                    <path d="M3 9h18"></path>
                    <rect x="3" y="11" width="18" height="10" rx="2" ry="2"></rect>
                </svg>
                <h3>Belum ada data pesanan</h3>
                <p>Belum ada pesanan yang masuk saat ini</p>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const searchBtn = document.getElementById('search-btn');
            const statusFilter = document.getElementById('status-filter');
            const dateFilter = document.getElementById('date-filter');
            const tableRows = document.querySelectorAll('.orders-table tbody tr');
            
            // Fungsi pencarian
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const orderId = row.querySelector('.order-id').textContent.toLowerCase();
                    const customerName = row.querySelector('.customer-name').textContent.toLowerCase();
                    const status = row.querySelector('.status-badge').textContent.toLowerCase();
                    
                    const matchesSearch = orderId.includes(searchTerm) || customerName.includes(searchTerm);
                    const matchesStatus = !statusValue || status.includes(statusValue);
                    
                    if (matchesSearch && matchesStatus) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            // Event listeners
            searchBtn.addEventListener('click', performSearch);
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
            
            statusFilter.addEventListener('change', performSearch);
            dateFilter.addEventListener('change', performSearch);
        });
    </script>
</body>
</html>