<?php
session_start();
include 'config/database.php';

// Cek login dan redirect SEBELUM ada output HTML
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// Cek jika keranjang kosong
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Inisialisasi variabel
 $cart_items = [];
 $total_harga = 0;
 $shipping_cost = 15000; // Ongkos flat, bisa dihitung dinamis nanti

// Siapkan data keranjang
foreach ($_SESSION['cart'] as $id => $qty) {
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
            'image' => $p['image']
        ];
    }
}

// Hitung total harga sebelum proses checkout
 $total_price = $total_harga + $shipping_cost;

// Proses checkout
if (isset($_POST['checkout'])) {
    $user_id = $_SESSION['user']['id'];
    $payment_method = $_POST['payment_method'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    
    // Validasi input
    if (empty($address) || empty($phone)) {
        $error = "Mohon lengkapi alamat dan nomor telepon";
    } else {
        // Simpan order ke database
        mysqli_query($conn, "INSERT INTO orders (user_id, total_price, shipping_cost, status, payment_method, shipping_address) VALUES ('$user_id', '$total_price', '$shipping_cost', 'Menunggu Pembayaran', '$payment_method', '$address')");
        $order_id = mysqli_insert_id($conn);
        
        // Simpan item-item order
        foreach ($cart_items as $item) {
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, qty, price) VALUES ('$order_id', '{$item['product_id']}', '{$item['qty']}', '{$item['price']}')");
            
            // Update stok produk
            mysqli_query($conn, "UPDATE products SET stock = stock - {$item['qty']}, sold = sold + {$item['qty']} WHERE id = {$item['product_id']}");
        }
        
        // Kosongkan keranjang
        unset($_SESSION['cart']);
        
        // Redirect ke halaman pembayaran berdasarkan metode
        if ($payment_method == 'Transfer Bank') {
            header("Location: customer/confirm_transfer.php?id=$order_id");
        } else {
            // Untuk E-Wallet atau VA, kita bisa langsung proses (simulasi)
            header("Location: customer/payment_success.php?id=$order_id");
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Bustore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 100px auto 30px;
            padding: 0 20px;
        }
        
        h2 {
            margin-bottom: 30px;
            color: #333;
            font-size: 2rem;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .checkout-form {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .checkout-form h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff5722;
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
        }
        
        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .payment-method {
            flex: 1;
            min-width: 200px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .payment-method:hover {
            border-color: #ff5722;
        }
        
        .payment-method.selected {
            border-color: #ff5722;
            background: #fff5f4;
        }
        
        .payment-method input {
            display: none;
        }
        
        .payment-method-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .order-summary h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .order-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .order-item-price {
            color: #ff5722;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .order-item-qty {
            color: #757575;
            font-size: 0.9rem;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .summary-item.total {
            font-weight: 600;
            font-size: 1.1rem;
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 10px;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.2s;
            text-align: center;
            font-size: 1rem;
        }
        
        .btn-orange {
            background-color: #ff5722;
            color: white;
        }
        
        .btn-orange:hover {
            background-color: #e64a19;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
            
            .payment-methods {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    
    <div class="checkout-container">
        <?php include 'partials/back.php'; ?>
        
        <h2>Checkout</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>
    
        <div class="checkout-grid">
            <div class="checkout-form">
                <h3>Informasi Pengiriman</h3>
                <form method="post">
                    <div class="form-group">
                        <label for="address">Alamat Pengiriman</label>
                        <textarea id="address" name="address" required><?= $_SESSION['user']['address'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" value="<?= $_SESSION['user']['phone'] ?? '' ?>" required>
                    </div>
                    
                    <h3>Metode Pembayaran</h3>
                    <div class="payment-methods">
                        <div class="payment-method selected">
                            <input type="radio" name="payment_method" id="transfer" value="Transfer Bank" checked>
                            <label for="transfer">
                                <div class="payment-method-icon">🏦</div>
                                <div>Transfer Bank</div>
                            </label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="ewallet" value="E-Wallet">
                            <label for="ewallet">
                                <div class="payment-method-icon">📱</div>
                                <div>E-Wallet</div>
                            </label>
                        </div>
                    </div>
                    <button type="submit" name="checkout" class="btn btn-orange">Buat Pesanan</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h3>Ringkasan Pesanan</h3>
                
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <?php 
                        // Menentukan path gambar tanpa image_helper
                        $image_path = 'assets/img/products/' . $item['image'];
                        if (!file_exists($image_path)) {
                            // Jika file tidak ada, gunakan gambar default
                            $image_path = 'assets/img/products/default.jpg';
                        }
                        ?>
                        <img src="<?= $image_path ?>" alt="<?= $item['name'] ?>">
                        <div class="order-item-details">
                            <div class="order-item-name"><?= $item['name'] ?></div>
                            <div class="order-item-price">Rp <?= number_format($item['price']) ?></div>
                            <div class="order-item-qty">Qty: <?= $item['qty'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="summary-item">
                    <span>Total Harga Produk:</span>
                    <span>Rp <?= number_format($total_harga) ?></span>
                </div>
                <div class="summary-item">
                    <span>Biaya Kirim:</span>
                    <span>Rp <?= number_format($shipping_cost) ?></span>
                </div>
                <div class="summary-item total">
                    <span>Total Pembayaran:</span>
                    <span>Rp <?= number_format($total_price) ?></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Payment method selection
            const paymentMethods = document.querySelectorAll('.payment-method');
            
            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    // Remove selected class from all methods
                    paymentMethods.forEach(m => m.classList.remove('selected'));
                    
                    // Add selected class to clicked method
                    this.classList.add('selected');
                    
                    // Check the radio button
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                });
            });
            
            // Form validation
            const checkoutForm = document.querySelector('form');
            
            checkoutForm.addEventListener('submit', function(e) {
                const address = document.getElementById('address').value.trim();
                const phone = document.getElementById('phone').value.trim();
                
                if (!address) {
                    e.preventDefault();
                    alert('Silakan isi alamat pengiriman');
                    return;
                }
                
                if (!phone) {
                    e.preventDefault();
                    alert('Silakan isi nomor telepon');
                    return;
                }
                
                // Show loading state
                const submitBtn = document.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Memproses...';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            });
        });
    </script>
</body>
</html>