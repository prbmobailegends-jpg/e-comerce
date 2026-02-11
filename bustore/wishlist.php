<?php
session_start();
include 'config/database.php';
include 'partials/navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// Get wishlist from localStorage (converted to session)
if (isset($_POST['wishlist'])) {
    $_SESSION['wishlist'] = json_decode($_POST['wishlist'], true);
}

// Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Get products in wishlist
 $wishlist_products = [];
if (!empty($_SESSION['wishlist'])) {
    $wishlist_ids = implode(',', $_SESSION['wishlist']);
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($wishlist_ids)");
    while ($row = mysqli_fetch_assoc($result)) {
        $wishlist_products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Saya - Bustore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .wishlist-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
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
        
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .wishlist-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .wishlist-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        
        .wishlist-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .wishlist-info {
            padding: 20px;
        }
        
        .wishlist-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .wishlist-price {
            font-weight: 700;
            color: #ff5722;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .wishlist-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-size: 0.9rem;
            border: none;
            flex: 1;
        }
        
        .btn-primary {
            background: #ff5722;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e64a19;
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-danger:hover {
            background: #d32f2f;
        }
        
        .empty-wishlist {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .empty-wishlist-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ccc;
        }
        
        .empty-wishlist h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .empty-wishlist p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .remove-wishlist {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .remove-wishlist:hover {
            background: white;
            transform: scale(1.1);
        }
        
        .remove-wishlist img {
            width: 20px;
            height: 20px;
        }
    </style>
</head>
<body>
    <div class="wishlist-container">
        <div class="page-header">
            <h1 class="page-title">Wishlist Saya</h1>
            <span><?= count($wishlist_products) ?> produk</span>
        </div>
        
        <?php if (!empty($wishlist_products)): ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlist_products as $product): ?>
                    <div class="wishlist-card">
                        <div class="remove-wishlist" onclick="removeFromWishlist(<?= $product['id'] ?>)">
                            <img src="assets/img/icons/icon-heart.png" alt="Remove">
                        </div>
                        <img src="assets/img/products/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                        <div class="wishlist-info">
                            <h3 class="wishlist-title"><?= $product['name'] ?></h3>
                            <div class="wishlist-price">Rp <?= number_format($product['price']) ?></div>
                            <div class="wishlist-actions">
                                <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-secondary">Lihat Detail</a>
                                <button class="btn btn-primary" onclick="addToCart(<?= $product['id'] ?>)">Tambah ke Keranjang</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist">
                <div class="empty-wishlist-icon">💔</div>
                <h3>Wishlist Anda Kosong</h3>
                <p>Anda belum menambahkan produk ke wishlist</p>
                <a href="index.php" class="btn btn-primary">Belanja Sekarang</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Remove from wishlist function
        function removeFromWishlist(productId) {
            let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            
            // Remove product from wishlist
            wishlist = wishlist.filter(id => id !== productId);
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
            
            // Update session
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'wishlist';
            input.value = JSON.stringify(wishlist);
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
        
        // Add to cart function
        function addToCart(productId) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'cart_add.php';
            
            const productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'id';
            productIdInput.value = productId;
            
            form.appendChild(productIdInput);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>