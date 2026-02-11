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
    <title>Data Produk - Bustore Petugas</title>
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
        
        .products-table-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .products-table th {
            background-color: #f9f9f9;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #f0f0f0;
            position: sticky;
            top: 0;
        }
        
        .products-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .products-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
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
        
        .btn-view {
            background: #007bff;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        .btn-view:hover {
            background: #0056b3;
        }
        
        .stock-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .stock-high {
            background: #d4edda;
            color: #155724;
        }
        
        .stock-medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-low {
            background: #f8d7da;
            color: #721c24;
        }
        
        .search-bar {
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
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            min-width: 150px;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                width: 100%;
            }
            
            .products-table {
                font-size: 0.9rem;
            }
            
            .products-table th, .products-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Data Produk</h1>
            <div class="page-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali ke Dashboard
                </a>
                <a href="upload_image.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Upload Produk
                </a>
            </div>
        </div>
        
        <div class="search-bar">
            <div class="filter-options">
                <div class="filter-group">
                    <label for="category-filter">Kategori:</label>
                    <select id="category-filter">
                        <option value="">Semua Kategori</option>
                        <?php
                        $categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
                        while($cat = mysqli_fetch_assoc($categories)) {
                            echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="stock-filter">Stok:</label>
                    <select id="stock-filter">
                        <option value="">Semua Stok</option>
                        <option value="high">Stok Tinggi (>10)</option>
                        <option value="medium">Stok Sedang (5-10)</option>
                        <option value="low">Stok Rendah (<5)</option>
                    </select>
                </div>
            </div>
            <div class="search-box">
                <input type="text" placeholder="Cari produk..." id="search-input">
                <button type="button" id="search-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="products-table-container">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $q = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
                    while ($p = mysqli_fetch_assoc($q)) {
                        $stock_status = '';
                        $stock_class = '';
                        if ($p['stock'] > 10) {
                            $stock_status = 'Tersedia';
                            $stock_class = 'stock-high';
                        } elseif ($p['stock'] >= 5) {
                            $stock_status = 'Terbatas';
                            $stock_class = 'stock-medium';
                        } else {
                            $stock_status = 'Hampir Habis';
                            $stock_class = 'stock-low';
                        }
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><img src="../assets/img/<?= $p['image'] ?>" alt="<?= $p['name'] ?>" class="product-img"></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= $p['category_name'] ?></td>
                        <td>Rp <?= number_format($p['price']) ?></td>
                        <td><?= $p['stock'] ?></td>
                        <td><span class="stock-status <?= $stock_class ?>"><?= $stock_status ?></span></td>
                        <td>
                            <a href="../admin/product_edit.php?id=<?= $p['id'] ?>" class="btn btn-view">View</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const searchBtn = document.getElementById('search-btn');
            const categoryFilter = document.getElementById('category-filter');
            const stockFilter = document.getElementById('stock-filter');
            const tableRows = document.querySelectorAll('.products-table tbody tr');
            
            // Fungsi pencarian
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase();
                const categoryValue = categoryFilter.value;
                const stockValue = stockFilter.value;
                
                tableRows.forEach(row => {
                    const productName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const productCategory = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                    const productStock = parseInt(row.querySelector('td:nth-child(6)').textContent);
                    
                    let matchesSearch = productName.includes(searchTerm) || productCategory.includes(searchTerm);
                    let matchesCategory = !categoryValue || row.querySelector('td:nth-child(4)').textContent.includes(categoryValue);
                    let matchesStock = true;
                    
                    if (stockValue === 'high' && productStock <= 10) matchesStock = false;
                    if (stockValue === 'medium' && (productStock < 5 || productStock > 10)) matchesStock = false;
                    if (stockValue === 'low' && productStock >= 5) matchesStock = false;
                    
                    if (matchesSearch && matchesCategory && matchesStock) {
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
            
            categoryFilter.addEventListener('change', performSearch);
            stockFilter.addEventListener('change', performSearch);
        });
    </script>
</body>
</html>