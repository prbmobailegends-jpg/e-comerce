<?php
session_start();
include 'config/database.php';
include 'partials/navbar.php';

 $id = $_GET['id'];
 $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id=$id"));

if (!$product) {
    header("Location: index.php");
    exit;
}

 $related_products = mysqli_query($conn, "SELECT * FROM products WHERE category_id = {$product['category_id']} AND id != $id ORDER BY RAND() LIMIT 4");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product['name'] ?> - Bustore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ... (Style CSS tetap sama) ... */
        .product-detail-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }
        
        .product-detail-page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .product-image-gallery {
            position: relative;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
        }
        
        .image-thumbnails {
            display: flex;
            gap: 10px;
            overflow-x: auto;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }
        
        .thumbnail:hover, .thumbnail.active {
            border-color: #ff5722;
        }
        
        .product-details {
            display: flex;
            flex-direction: column;
        }
        
        .product-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        
        .product-category {
            color: #757575;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .rating-stars {
            color: #ffc107;
        }
        
        .rating-count {
            color: #757575;
            font-size: 0.9rem;
        }
        
        .product-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ff5722;
            margin-bottom: 20px;
        }
        
        .product-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .product-stock {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 25px;
            color: #555;
        }
        
        .stock-status {
            font-weight: 500;
        }
        
        .in-stock {
            color: #4caf50;
        }
        
        .low-stock {
            color: #ff9800;
        }
        
        .out-of-stock {
            color: #f44336;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px 0 0 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #333;
        }
        
        .quantity-btn:last-child {
            border-radius: 0 8px 8px 0;
        }
        
        .quantity-btn:hover {
            background: #f5f5f5;
        }
        
        .quantity-input {
            width: 60px;
            height: 40px;
            text-align: center;
            border: 1px solid #ddd;
            border-left: none;
            border-right: none;
            font-size: 1rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            border: none;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: #ff5722;
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            background: #e64a19;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .btn-heart {
            background: #f5f5f5;
            color: #333;
            width: 50px;
            padding: 0;
        }
        
        .btn-heart.active {
            background: #ffebee;
            color: #f44336;
        }
        
        .btn-heart:hover {
            background: #ffebee;
            transform: translateY(-2px);
        }
        
        .product-meta {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #757575;
            font-size: 0.9rem;
        }
        
        .meta-item img {
            width: 20px;
            height: 20px;
        }
        
        .product-description-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: #ff5722;
            border-radius: 2px;
        }
        
        .related-products-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            background: white;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-card-info {
            padding: 15px;
        }
        
        .product-card-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .product-card-price {
            font-weight: 700;
            color: #ff5722;
            font-size: 1.1rem;
        }
        
        .floating-buttons {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 100;
        }
        
        .floating-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .floating-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .floating-cart {
            background: #ff5722;
            color: white;
        }
        
        .floating-checkout {
            background: #4caf50;
            color: white;
        }
        
        .floating-btn img {
            width: 24px;
            height: 24px;
        }
        
        @media (max-width: 768px) {
            .product-detail-page {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .floating-buttons {
                bottom: 20px;
                right: 20px;
            }
            
            .floating-btn {
                width: 50px;
                height: 50px;
            }
            
            .floating-btn img {
                width: 20px;
                height: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="product-detail-container">
        <div class="product-detail-page">
            <div class="product-image-gallery">
                <img src="assets/img/products/<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="main-image" id="main-image">
                <div class="image-thumbnails">
                    <!-- Thumbnails will be added dynamically if multiple images exist -->
                </div>
            </div>
            
            <div class="product-details">
                <h1 class="product-title"><?= $product['name'] ?></h1>
                <p class="product-category">Kategori: <?= $product['category_name'] ?></p>
                
                <div class="product-rating">
                    <div class="rating-stars">
                        <?php for($i=1; $i<=5; $i++) { echo $i <= floor($product['rating']) ? '⭐' : '☆'; } ?>
                    </div>
                    <span class="rating-count">(<?= $product['sold'] ?> terjual)</span>
                </div>
                
                <div class="product-price">Rp <?= number_format($product['price']) ?></div>
                
                <div class="product-stock">
                    <img src="assets/img/icons/icon-truck.png" alt="Stock" width="20">
                    <span class="stock-status <?= $product['stock'] > 10 ? 'in-stock' : ($product['stock'] > 0 ? 'low-stock' : 'out-of-stock') ?>">
                        <?= $product['stock'] > 0 ? 'Stok tersedia: ' . $product['stock'] : 'Stok habis' ?>
                    </span>
                </div>
                
                <div class="product-description">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
                
                <div class="quantity-selector">
                    <!-- Perubahan: Tombol minus link ke cart_remove.php -->
                    <a href="cart_remove.php?id=<?= $product['id'] ?>" class="quantity-btn">-</a>
                    <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?= $product['stock'] ?>" readonly>
                    <!-- Perubahan: Tombol plus link ke cart_add.php -->
                    <a href="cart_add.php?id=<?= $product['id'] ?>" class="quantity-btn">+</a>
                </div>
                
                <div class="action-buttons">
                    <!-- Perubahan: Tombol Tambah ke Keranjang langsung link ke cart.php -->
                    <a href="cart_add.php?id=<?= $product['id'] ?>" class="btn btn-primary">
                        <img src="assets/img/icons/empty-cart.png" alt="Cart" width="20">
                        Tambah ke Keranjang
                    </a>
                    <!-- Perubahan: Tombol Beli Sekarang langsung link ke checkout.php -->
                    <a href="checkout.php" class="btn btn-secondary">
                        <img src="assets/img/icons/icon-star.png" alt="Buy" width="20">
                        Beli Sekarang
                    </a>
                    <button class="btn btn-heart <?= isInWishlist($product['id']) ? 'active' : '' ?>" id="wishlist-btn" onclick="toggleWishlist()">
                        <img src="assets/img/icons/icon-heart.png" alt="Wishlist" width="20">
                    </button>
                </div>
                
                <div class="product-meta">
                    <div class="meta-item">
                        <img src="assets/img/icons/icon-truck.png" alt="Shipping">
                        <span>Pengiriman 1-3 hari</span>
                    </div>
                    <div class="meta-item">
                        <img src="assets/img/icons/icon-shield.png" alt="Guarantee">
                        <span>Garansi 100% Original</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="product-description-section">
            <h2 class="section-title">Deskripsi Produk</h2>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>
        
        <div class="related-products-section">
            <h2 class="section-title">Produk Terkait</h2>
            <div class="products-grid">
                <?php while($p = mysqli_fetch_assoc($related_products)) { ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $p['id'] ?>">
                            <img src="assets/img/products/<?= $p['image'] ?>" alt="<?= $p['name'] ?>">
                        </a>
                        <div class="product-card-info">
                            <h3 class="product-card-title"><?= $p['name'] ?></h3>
                            <p class="product-card-price">Rp <?= number_format($p['price']) ?></p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <div class="floating-buttons">
        <a href="cart.php" class="floating-btn floating-cart">
            <img src="assets/img/icons/empty-cart.png" alt="Cart">
        </a>
        <a href="checkout.php" class="floating-btn floating-checkout">
            <img src="assets/img/icons/icon-star.png" alt="Checkout">
        </a>
    </div>
    
    <script>
        // Toggle wishlist function
        function toggleWishlist() {
            const productId = <?= $product['id'] ?>;
            const btn = document.getElementById('wishlist-btn');
            
            // Use the global function from navbar
            addToWishlist(productId);
            
            // Toggle active class
            btn.classList.toggle('active');
        }
        
        // Check if product is in wishlist on page load
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('wishlist-btn');
            if (isInWishlist(<?= $product['id'] ?>)) {
                btn.classList.add('active');
            }
        });
        
        // Image gallery functionality (if multiple images)
        document.addEventListener('DOMContentLoaded', function() {
            const mainImage = document.getElementById('main-image');
            const thumbnailsContainer = document.querySelector('.image-thumbnails');
            
            // If you have multiple images for a product, you can add them here
            // For now, we'll just show the main image
        });
    </script>
</body>
</html>