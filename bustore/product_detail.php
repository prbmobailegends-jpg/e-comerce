<?php
session_start();
include 'config/database.php';

// 1. Cek Login
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// 2. Cek ID Produk
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

 $product_id = (int)$_GET['id'];

// 3. Ambil Data Produk
 $product_query = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = $product_id");
 $product = mysqli_fetch_assoc($product_query);

if (!$product) {
    header("Location: index.php");
    exit;
}

// 4. Ambil Data Rating
 $rating_query = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM product_reviews WHERE product_id = $product_id");
 $rating_data = mysqli_fetch_assoc($rating_query);

 $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
 $total_reviews = $rating_data['total_reviews'];

// ---------------------------------------------------------
// 5. LOGIKA PERBAIKAN: PROSES TAMBAH KE KERANJANG (SESSION)
// ---------------------------------------------------------
// Kami mengubah logika dari Database (persistent_cart) menjadi Session
// agar konsisten dengan file cart.php, cart_add.php, dan cart_remove.php Anda.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pastikan session cart ada
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Ambil jumlah dari form
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Validasi Stok
    if ($quantity > $product['stock']) {
        $error_message = "Jumlah pesanan melebihi stok yang tersedia.";
    } else {
        // Cek tombol mana yang ditekan (Keranjang atau Beli Sekarang)
        $is_buy_now = isset($_POST['buy_now']);

        // Proses Update Cart Session
        if (isset($_SESSION['cart'][$product_id])) {
            // Produk sudah ada, tambah jumlahnya
            $current_qty = $_SESSION['cart'][$product_id];
            
            // Cek apakah jumlah baru melebihi stok
            if (($current_qty + $quantity) <= $product['stock']) {
                $_SESSION['cart'][$product_id] += $quantity;
                $success_message = "Jumlah produk berhasil diperbarui di keranjang!";
            } else {
                // Jika melebihi, set ke maksimal stok
                $_SESSION['cart'][$product_id] = $product['stock'];
                $error_message = "Maksimal stok tercapai (" . $product['stock'] . ").";
            }
        } else {
            // Produk baru, masukkan ke cart
            $_SESSION['cart'][$product_id] = $quantity;
            $success_message = "Produk berhasil ditambahkan ke keranjang!";
        }

        // Redirect berdasarkan tombol yang ditekan
        if ($is_buy_now) {
            // Jika Beli Sekarang -> Langsung ke Checkout
            header("Location: checkout.php");
            exit;
        } else {
            // Jika Masuk Keranjang -> Refresh halaman ini untuk menampilkan pesan
            header("Location: product_detail.php?id=" . $product_id);
            exit;
        }
    }
}
// ---------------------------------------------------------

// 6. Ambil Data Produk Terkait
 $related_query = mysqli_query($conn, "SELECT * FROM products WHERE category_id = {$product['category_id']} AND id != $product_id LIMIT 4");

// 7. Ambil Data Ulasan
 $reviews_query = mysqli_query($conn, "
    SELECT pr.*, u.name as user_name, u.image as user_image 
    FROM product_reviews pr 
    JOIN users u ON pr.user_id = u.id 
    WHERE pr.product_id = $product_id 
    ORDER BY pr.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Bustore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        .product-detail-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .breadcrumb { margin-bottom: 20px; color: #666; font-size: 14px; }
        .breadcrumb a { color: #ff5722; text-decoration: none; }
        
        .product-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 40px; }
        .main-image { width: 100%; height: 400px; object-fit: cover; border-radius: 8px; border: 1px solid #e0e0e0; }
        
        .product-info h1 { font-size: 24px; margin-bottom: 10px; color: #333; }
        .product-price { font-size: 28px; color: #ff5722; font-weight: 600; margin: 20px 0; }
        .product-stock { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding: 10px; background: #f5f5f5; border-radius: 8px; font-size: 14px; }
        .in-stock { color: #4caf50; font-weight: bold; }
        .out-of-stock { color: #f44336; font-weight: bold; }
        
        .quantity-selector { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; }
        .quantity-controls { display: flex; align-items: center; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .quantity-btn { width: 40px; height: 40px; border: none; background: #f5f5f5; cursor: pointer; font-size: 18px; }
        .quantity-input { width: 60px; height: 40px; border: none; text-align: center; font-size: 16px; -moz-appearance: textfield; }
        
        .product-actions { display: flex; gap: 15px; }
        
        .btn { padding: 12px 24px; border-radius: 8px; font-weight: 500; cursor: pointer; transition: all 0.3s; border: none; font-size: 16px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: #ff5722; color: white; flex: 1; }
        .btn-primary:hover { background: #e64a19; }
        .btn-secondary { background: white; color: #333; border: 1px solid #ddd; flex: 1; }
        .btn-secondary:hover { background: #f5f5f5; }
        .btn-review { background: #ffc107; color: #333; flex: 1; }
        .btn-review:hover { background: #e0a800; }

        .related-products { margin-top: 50px; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .product-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: all 0.3s; cursor: pointer; }
        .product-card:hover { transform: translateY(-5px); }
        .product-image { width: 100%; height: 180px; object-fit: cover; }
        .product-info-card { padding: 15px; }
        .product-name { font-weight: 600; margin-bottom: 5px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .product-price-card { color: #ff5722; font-weight: bold; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Style Section Reviews */
        .reviews-section {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 20px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            color: #333;
        }
        
        .review-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .review-item {
            border: 1px solid #f0f0f0;
            border-radius: 8px;
            padding: 20px;
            background: #fafafa;
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
            background: #ddd;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        
        .review-date {
            font-size: 12px;
            color: #999;
        }
        
        .review-rating {
            color: #ffc107;
            font-size: 14px;
        }
        
        .review-comment {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 5px;
        }
        
        .review-images {
            margin-top: 10px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .review-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
            cursor: pointer;
        }

        .no-reviews {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 768px) {
            .product-detail { grid-template-columns: 1fr; }
            .product-actions { flex-direction: column; }
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    
    <div class="product-detail-container">
        <?php if (isset($success_message)): ?>
            <div class="alert"><?= $success_message ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?= $error_message ?></div>
        <?php endif; ?>
        
        <nav class="breadcrumb">
            <a href="index.php">Beranda</a> / 
            <span><?= htmlspecialchars($product['name']) ?></span>
        </nav>
        
        <div class="product-detail">
            <div class="product-images">
                <?php 
                $image_path = 'assets/img/products/' . $product['image'];
                if (!file_exists($image_path)) $image_path = 'https://via.placeholder.com/400x400?text=No+Image';
                ?>
                <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="main-image" id="mainImage">
            </div>
            
            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="product-rating" style="margin-bottom: 10px; color: #ffc107;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="<?= $i <= $avg_rating ? 'fas' : ($i - 0.5 <= $avg_rating ? 'fas fa-star-half-alt' : 'far') ?> fa-star"></i>
                    <?php endfor; ?>
                    <span style="color: #666; font-size: 14px; margin-left: 8px;">
                        (<?= $avg_rating ?> / <?= $total_reviews ?> Ulasan)
                    </span>
                </div>
                
                <div class="product-price">
                    Rp <?= number_format($product['price'], 0, ',', '.') ?>
                </div>
                
                <div class="product-description" style="color: #666; margin-bottom: 20px;">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
                
                <div class="product-stock">
                    Status: 
                    <span class="stock-status 
                        <?php 
                        if ($product['stock'] > 0) echo 'in-stock';
                        else echo 'out-of-stock';
                        ?>">
                        <?php 
                        if ($product['stock'] > 0) echo 'Tersedia (' . $product['stock'] . ')';
                        else echo 'Stok Habis';
                        ?>
                    </span>
                </div>
                
                <!-- PERBAIKAN FORM AGAR TOMBOL BELI SEKARANG BERFUNGSI -->
                <form method="POST" id="addToCartForm">
                    <div class="quantity-selector">
                        <label>Jumlah:</label>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" onclick="decreaseQuantity()">-</button>
                            <input type="number" name="quantity" class="quantity-input" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                            <button type="button" class="quantity-btn" onclick="increaseQuantity()">+</button>
                        </div>
                    </div>
                    
                    <div class="product-actions">
                        <!-- Tombol Tambah Keranjang -->
                        <button type="submit" name="add_to_cart" class="btn btn-secondary">
                            <i class="fas fa-shopping-cart"></i> Keranjang
                        </button>
                        
                        <!-- Tombol Beli Sekarang (Hanya punya name="buy_now") -->
                        <button type="submit" name="buy_now" value="1" class="btn btn-primary">
                            <i class="fas fa-bolt"></i> Beli Sekarang
                        </button>
                        
                        <?php if($product['stock'] > 0): ?>
                            <a href="#reviews-section" class="btn btn-review">
                                <i class="fas fa-star"></i> Lihat Ulasan
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Bagian Rating & Ulasan -->
        <div id="reviews-section" class="reviews-section">
            <h2 class="section-title">Ulasan Pembeli</h2>
            
            <?php if (mysqli_num_rows($reviews_query) > 0): ?>
                <div class="review-list">
                    <?php while ($review = mysqli_fetch_assoc($reviews_query)): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <?php 
                                $has_avatar = false;
                                $avatar_path = '';
                                if (!empty($review['user_image'])) {
                                    $check_path = 'assets/img/users/' . $review['user_image'];
                                    if (file_exists($check_path)) {
                                        $avatar_path = $check_path;
                                        $has_avatar = true;
                                    }
                                }
                                if (!$has_avatar) {
                                    $avatar_path = 'https://ui-avatars.com/api/?name=' . urlencode($review['user_name']) . '&background=random&color=fff&size=128';
                                }
                                ?>
                                <img src="<?= $avatar_path ?>" alt="<?= htmlspecialchars($review['user_name']) ?>" class="user-avatar">
                                
                                <div class="user-info">
                                    <div class="user-name"><?= htmlspecialchars($review['user_name']) ?></div>
                                    <div class="review-date">
                                        <?= date('d F Y, H:i', strtotime($review['created_at'])) ?>
                                        <?php if(!empty($review['updated_at'])): ?>
                                            <span style="color:#ff9800; font-size:11px;">(Diedit)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="review-rating">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="<?= $i <= $review['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="review-comment">
                                <?= htmlspecialchars($review['comment']) ?>
                            </div>

                            <!-- PATH GAMBAR ULASAN -->
                            <?php if (!empty($review['image'])): ?>
                                <div class="review-images">
                                    <?php 
                                    $review_imgs = explode(',', $review['image']);
                                    foreach ($review_imgs as $r_img):
                                        $r_img = trim($r_img);
                                        $r_path = 'assets/img/reviews/' . $r_img; 
                                    ?>
                                        <?php if(file_exists($r_path)): ?>
                                            <img src="<?= $r_path ?>" class="review-img" onclick="window.open(this.src)">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-reviews">
                    <i class="fas fa-comment-slash" style="font-size: 40px; margin-bottom: 15px; color: #ddd;"></i>
                    <h3>Belum ada ulasan</h3>
                    <p>Jadilah yang pertama memberikan ulasan untuk produk ini!</p>
                </div>
            <?php endif; ?>
        </div>
        <!-- End Bagian Rating & Ulasan -->

        
        <!-- Produk Terkait -->
        <div class="related-products">
            <h3 style="margin-bottom: 20px;">Produk Terkait</h3>
            <div class="products-grid">
                <?php 
                mysqli_data_seek($related_query, 0);
                while ($related = mysqli_fetch_assoc($related_query)): 
                ?>
                    <div class="product-card" onclick="window.location.href='product_detail.php?id=<?= $related['id'] ?>'">
                        <?php 
                        $rel_img = 'assets/img/products/' . $related['image'];
                        if (!file_exists($rel_img)) $rel_img = 'https://via.placeholder.com/200x200?text=No+Image';
                        ?>
                        <img src="<?= $rel_img ?>" alt="<?= htmlspecialchars($related['name']) ?>" class="product-image">
                        <div class="product-info-card">
                            <div class="product-name"><?= htmlspecialchars($related['name']) ?></div>
                            <div class="product-price-card">Rp <?= number_format($related['price'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <?php include 'partials/footer.php'; ?>
    
    <script>
        function increaseQuantity() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.max);
            const current = parseInt(input.value);
            if (current < max) input.value = current + 1;
        }
        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            const min = parseInt(input.min);
            const current = parseInt(input.value);
            if (current > min) input.value = current - 1;
        }
    </script>
</body>
</html>