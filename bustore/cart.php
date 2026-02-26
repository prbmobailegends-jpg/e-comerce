<?php
session_start();
include 'config/database.php';

// Cek login dan redirect SEBELUM ada output HTML
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// Inisialisasi variabel
 $cart_items = [];
 $total_harga = 0;
 $shipping_cost = 15000; // Ongkos flat

// Siapkan data keranjang HANYA jika ada item di session
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        // Pastikan ID adalah angka untuk mencegah SQL Injection
        $id = (int)$id;
        $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$id"));
        if ($p && $qty > 0) {
            $subtotal = $p['price'] * $qty;
            $total_harga += $subtotal;
            $cart_items[] = [
                'product_id' => $p['id'],
                'name' => $p['name'],
                'price' => $p['price'],
                'qty' => $qty,
                'subtotal' => $subtotal,
                'image' => $p['image'],
                'category_id' => $p['category_id']
            ];
        }
    }
}

// Hitung total harga
 $total_price = $total_harga + $shipping_cost;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Bustore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* CSS Utama */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        .cart-container {
            max-width: 1200px;
            margin: 100px auto 30px; /* Margin top disesuaikan dengan navbar */
            padding: 0 20px;
        }
        
        /* --- STYLE BARU: TOMBOL KEMBALI --- */
        .back-nav {
            margin-bottom: 20px;
        }
        
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: white;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }
        
        .btn-back-home:hover {
            background-color: #f5f5f5;
            color: #ff5722;
            border-color: #ff5722;
            transform: translateX(-3px);
        }
        
        .btn-back-home svg {
            width: 18px;
            height: 18px;
        }
        /* ----------------------------------- */
        
        h2 {
            margin-bottom: 30px;
            color: #333;
            font-size: 2rem;
        }
        
        .cart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .cart-items {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .cart-item {
            display: flex;
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.2s;
            border: 1px solid #f0f0f0;
        }
        
        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
            border: 1px solid #eee;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
            font-size: 16px;
        }
        
        .cart-item-price {
            color: #ff5722;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .quantity-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: #f9f9f9;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            text-decoration: none;
            color: #333;
            font-size: 16px;
        }
        
        .quantity-btn:hover {
            background: #eee;
            color: #ff5722;
        }
        
        .quantity-btn.minus {
            border-right: 1px solid #ddd;
        }
        
        .quantity-btn.plus {
            border-left: 1px solid #ddd;
        }
        
        .quantity-value {
            width: 40px;
            text-align: center;
            font-weight: 500;
            font-size: 15px;
        }
        
        .cart-item-subtotal {
            text-align: right;
            margin-left: 15px;
            min-width: 100px;
        }
        
        .cart-item-subtotal p {
            color: #ff5722;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        
        .remove-item {
            color: #999;
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.2s;
            display: inline-block;
        }
        
        .remove-item:hover {
            color: #f44336;
            text-decoration: underline;
        }
        
        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .cart-summary h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.3rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f9f9f9;
            color: #555;
        }
        
        .summary-item:last-of-type {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .summary-item.total {
            font-weight: 700;
            font-size: 1.2rem;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #ff5722;
            color: #ff5722;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 15px;
            font-size: 1rem;
            margin-top: 25px;
            background: #ff5722;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .checkout-btn:hover {
            background: #e64a19;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 87, 34, 0.3);
        }
        
        /* Empty Cart Style */
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .empty-cart-icon {
            font-size: 60px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .empty-cart h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .empty-cart p {
            color: #666;
            margin-bottom: 25px;
        }
        
        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }
            
            .cart-summary {
                position: static;
            }
            
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .cart-item-image {
                margin: 0 auto 10px;
            }
            
            .cart-item-quantity {
                justify-content: center;
                margin-top: 10px;
            }
            
            .cart-item-subtotal {
                margin-left: 0;
                margin-top: 15px;
                text-align: center;
            }
            
            .remove-item {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    
    <div class="cart-container">
        <!-- NAVIGASI KEMBALI (Rapih & Mengarah ke Index) -->
        <div class="back-nav">
            <a href="index.php" class="btn-back-home">
                <!-- Icon Panah Kiri -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Kembali ke Beranda
            </a>
        </div>
        <!-- -------------------------------------------- -->

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert">
                <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <h2>Keranjang Belanja</h2>
        
        <?php if (!empty($cart_items)): ?>
            <div class="cart-grid">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <?php 
                            // Menentukan path gambar
                            $image_path = 'assets/img/products/' . $item['image'];
                            if (!file_exists($image_path)) {
                                $image_path = 'assets/img/products/default.jpg';
                            }
                            ?>
                            <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-image">
                            <div class="cart-item-details">
                                <h4 class="cart-item-name"><?= htmlspecialchars($item['name']) ?></h4>
                                <p class="cart-item-price">Rp <?= number_format($item['price']) ?></p>
                                <div class="cart-item-quantity">
                                    <div class="quantity-control">
                                        <!-- Tombol minus mengurangi quantity -->
                                        <a href="cart_remove.php?id=<?= $item['product_id'] ?>" class="quantity-btn minus">−</a>
                                        <span class="quantity-value"><?= $item['qty'] ?></span>
                                        <!-- Tombol plus menambah quantity -->
                                        <a href="cart_add.php?id=<?= $item['product_id'] ?>" class="quantity-btn plus">+</a>
                                    </div>
                                </div>
                            </div>
                            <div class="cart-item-subtotal">
                                <p>Rp <?= number_format($item['subtotal']) ?></p>
                                <a href="cart_remove.php?id=<?= $item['product_id'] ?>" class="remove-item" onclick="return confirm('Hapus produk ini?')">Hapus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Ringkasan Belanja</h3>
                    
                    <div class="summary-item">
                        <span>Total Harga:</span>
                        <span>Rp <?= number_format($total_harga) ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Total Ongkos Kirim:</span>
                        <span>Rp <?= number_format($shipping_cost) ?></span>
                    </div>
                    
                    <div class="summary-item total">
                        <span>Total:</span>
                        <span>Rp <?= number_format($total_price) ?></span>
                    </div>
                    
                    <a href="checkout.php" class="checkout-btn">Checkout</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Tampilkan halaman keranjang kosong, JANGAN REDIRECT -->
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h3>Keranjang Belanja Kosong</h3>
                <p>Belanja sekarang dan dapatkan produk terbaik dari kami</p>
                <a href="index.php" class="checkout-btn">Belanja Sekarang</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>