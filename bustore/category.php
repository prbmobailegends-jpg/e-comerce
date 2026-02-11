<?php
session_start();
include 'config/database.php';
include 'partials/navbar.php';

 $slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : '';
 $category = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM categories WHERE slug='$slug'"));

if (!$category) {
    header("Location: index.php");
    exit;
}

 $products = mysqli_query($conn, "SELECT * FROM products WHERE category_id = {$category['id']}");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori: <?= htmlspecialchars($category['name']) ?> - Bustore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .category-header {
            background: linear-gradient(135deg, #ff5722, #ff9800);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .category-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .category-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filter-options {
            display: flex;
            gap: 15px;
        }
        
        .filter-options select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
        }
        
        .product-count {
            color: #666;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .product-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover img {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-info h4 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            height: 40px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            font-weight: 600;
        }
        
        .product-category {
            font-size: 0.9rem;
            color: #757575;
            margin-bottom: 10px;
        }
        
        .product-rating {
            font-size: 0.9rem;
            margin-bottom: 15px;
            color: #ffc107;
        }
        
        .product-rating span {
            color: #9e9e9e;
            margin-left: 5px;
        }
        
        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ff5722;
            margin-top: auto;
            margin-bottom: 15px;
        }
        
        .add-to-cart-form {
            padding: 0 20px 20px;
            margin-top: auto;
        }
        
        .add-to-cart-form .btn {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .empty-category {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .empty-category img {
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-category h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .empty-category p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }
        
        .pagination a {
            display: inline-block;
            padding: 10px 15px;
            margin: 0 5px;
            border: 1px solid #ddd;
            border-radius: 8px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .pagination a:hover {
            background: #ff5722;
            color: white;
            border-color: #ff5722;
        }
        
        .pagination a.active {
            background: #ff5722;
            color: white;
            border-color: #ff5722;
        }
    </style>
</head>
<body>
    <div class="category-header">
        <div class="container">
            <h1><?= htmlspecialchars($category['name']) ?></h1>
            <p>Temukan berbagai pilihan <?= htmlspecialchars($category['name']) ?> terbaik</p>
        </div>
    </div>
    
    <div class="container">
        <div class="filter-bar">
            <div class="filter-options">
                <select>
                    <option>Urutkan: Terbaru</option>
                    <option>Urutkan: Harga Terendah</option>
                    <option>Urutkan: Harga Tertinggi</option>
                    <option>Urutkan: Rating Tertinggi</option>
                </select>
                <select>
                    <option>Semua Harga</option>
                    <option>Dibawah Rp 100.000</option>
                    <option>Rp 100.000 - Rp 500.000</option>
                    <option>Rp 500.000 - Rp 1.000.000</option>
                    <option>Diatas Rp 1.000.000</option>
                </select>
            </div>
            <div class="product-count">
                Menampilkan <?= mysqli_num_rows($products) ?> produk
            </div>
        </div>

        <?php if (mysqli_num_rows($products) > 0): ?>
            <div class="product-grid">
                <?php while($p = mysqli_fetch_assoc($products)) { ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $p['id'] ?>">
                            <img src="assets/img/products/<?= $p['image'] ?>" alt="<?= $p['name'] ?>">
                            <div class="product-info">
                                <h4><?= $p['name'] ?></h4>
                                <p class="product-category"><?= $category['name'] ?></p>
                                <div class="product-rating">
                                    <?php for($i=1; $i<=5; $i++) { echo $i <= floor($p['rating']) ? '⭐' : '☆'; } ?>
                                    <span>(<?= $p['sold'] ?> terjual)</span>
                                </div>
                                <p class="product-price">Rp <?= number_format($p['price']) ?></p>
                            </div>
                        </a>
                        <form method="post" action="cart_add.php" class="add-to-cart-form">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-secondary">+ Keranjang</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
            
            <div class="pagination">
                <a href="#" class="active">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">></a>
            </div>
        <?php else: ?>
            <div class="empty-category">
                <img src="assets/img/empty-category.png" alt="Tidak ada produk">
                <h3>Belum ada produk di kategori ini</h3>
                <p>Produk di kategori <?= htmlspecialchars($category['name']) ?> akan segera tersedia</p>
                <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'partials/footer.php'; ?>
</body>
</html>