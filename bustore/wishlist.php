<?php
session_start();
include 'config/database.php';
include 'partials/navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

 $user_id = $_SESSION['user']['id'];

// Handle hapus item dari wishlist
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    // Hapus dari database (pastikan nama tabel di DB adalah 'wishlist' atau 'wishlists')
    // Sesuaikan nama tabel di bawah ini dengan yang ada di database Anda. 
    // Berdasarkan screenshot, tabel Anda bernama 'wishlist'.
    mysqli_query($conn, "DELETE FROM wishlist WHERE user_id = $user_id AND product_id = $remove_id");
    header("Location: wishlist.php");
    exit;
}

// Handle Tambah ke Cart langsung dari Wishlist
if (isset($_GET['add_to_cart'])) {
    $product_id = (int)$_GET['add_to_cart'];
    
    // Cek produk
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id"));
    if ($product) {
        // Cek di cart database (persistent_cart)
        $check_cart = mysqli_query($conn, "SELECT * FROM persistent_cart WHERE user_id = $user_id AND product_id = $product_id");
        if (mysqli_num_rows($check_cart) > 0) {
            // Update quantity
            $row = mysqli_fetch_assoc($check_cart);
            $new_qty = $row['quantity'] + 1;
            mysqli_query($conn, "UPDATE persistent_cart SET quantity = $new_qty WHERE id = " . $row['id']);
        } else {
            // Insert baru ke cart
            mysqli_query($conn, "INSERT INTO persistent_cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)");
        }
        
        // Update session cart juga (opsional)
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }
        
        $_SESSION['success'] = "Produk ditambahkan ke keranjang!";
    }
    header("Location: cart.php");
    exit;
}

// Ambil data wishlist dari DATABASE
// Perbaikan: Hapus p.slug karena kolom tersebut tidak ada di tabel products
 $wishlist_query = mysqli_query($conn, "
    SELECT w.*, p.name, p.price, p.image 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = $user_id 
    ORDER BY w.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Saya - Bustore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .wishlist-container { max-width: 1200px; margin: 100px auto 40px; padding: 0 20px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }
        .page-title { font-size: 2rem; color: #333; margin: 0; }
        .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; }
        
        .wishlist-card {
            background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease; position: relative;
        }
        .wishlist-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .wishlist-card img { width: 100%; height: 200px; object-fit: cover; }
        
        .remove-btn {
            position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.9);
            color: #d32f2f; width: 35px; height: 35px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; text-decoration: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: all 0.2s; z-index: 10;
        }
        .remove-btn:hover { background: #d32f2f; color: white; transform: scale(1.1); }
        
        .wishlist-info { padding: 15px; }
        .wishlist-title { font-weight: 600; margin-bottom: 5px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .wishlist-price { font-weight: 700; color: #ff5722; font-size: 1.2rem; margin-bottom: 15px; display: block; }
        .wishlist-actions { display: flex; gap: 10px; }
        
        .btn { padding: 8px 12px; border-radius: 6px; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; text-align: center; font-size: 0.9rem; border: none; display: inline-block; }
        .btn-cart { background: #ff5722; color: white; flex: 1; }
        .btn-cart:hover { background: #e64a19; }
        .btn-view { background: #f5f5f5; color: #333; }
        .btn-view:hover { background: #e0e0e0; }

        .empty-wishlist { text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .empty-icon { font-size: 4rem; margin-bottom: 20px; color: #ccc; }
    </style>
</head>
<body>
    <div class="wishlist-container">
        <div class="page-header">
            <h1 class="page-title">Wishlist Saya</h1>
            <span style="color: #666;"><?= mysqli_num_rows($wishlist_query) ?> Produk</span>
        </div>
        
        <?php if (mysqli_num_rows($wishlist_query) > 0): ?>
            <div class="wishlist-grid">
                <?php while ($product = mysqli_fetch_assoc($wishlist_query)): ?>
                    <div class="wishlist-card">
                        <a href="?remove=<?= $product['product_id'] ?>" class="remove-btn" title="Hapus dari Wishlist">
                            <i class="fas fa-trash"></i>
                        </a>
                        
                        <a href="product_detail.php?id=<?= $product['product_id'] ?>">
                            <!-- Path gambar diperbaiki -->
                            <?php 
                            $image_path = 'assets/img/products/' . $product['image'];
                            if (!file_exists($image_path)) { $image_path = 'assets/img/products/default.jpg'; }
                            ?>
                            <img src="<?= $image_path ?>" alt="<?= $product['name'] ?>">
                        </a>
                        
                        <div class="wishlist-info">
                            <div class="wishlist-title" title="<?= $product['name'] ?>"><?= $product['name'] ?></div>
                            <span class="wishlist-price">Rp <?= number_format($product['price']) ?></span>
                            
                            <div class="wishlist-actions">
                                <a href="?add_to_cart=<?= $product['product_id'] ?>" class="btn btn-cart">
                                    <i class="fas fa-shopping-cart"></i> Keranjang
                                </a>
                                <a href="checkout.php" class="btn btn-view">Beli</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist">
                <div class="empty-icon">💔</div>
                <h3>Wishlist Anda Kosong</h3>
                <p>Anda belum menambahkan produk ke wishlist</p>
                <a href="index.php" class="btn btn-cart" style="display:inline-block; width:auto; padding: 12px 30px; font-size:1rem;">Belanja Sekarang</a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'partials/footer.php'; ?>
</body>
</html>