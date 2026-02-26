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
    exit;
}

// --- SISTEM KODE BILLING (Random) ---
// Generate kode unik: 2 huruf acak + 4 angka acak + timestamp uniqid
 $prefix = chr(rand(65, 90)) . chr(rand(65, 90)); // Misal: AB, XY, KZ
 $random_num = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT); // Misal: 0042, 8512
 $unique_id = strtoupper(substr(uniqid(), -6)); // Ambil 6 karakter terakhir uniqid
 $billing_code = "BILL-" . $prefix . $random_num . "-" . $unique_id; // Format: BILL-AB8512-X9Z2A1
// -----------------------------------

// Inisialisasi variabel
 $cart_items = [];
 $total_harga = 0;
 $shipping_cost = 15000; // Ongkos flat
 $error = "";

// Siapkan data keranjang
foreach ($_SESSION['cart'] as $id => $qty) {
    // Pastikan ID adalah angka
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
            'image' => $p['image']
        ];
    }
}

// Hitung total harga
 $total_price = $total_harga + $shipping_cost;

// --- PROSES CHECKOUT ---
if (isset($_POST['checkout'])) {
    $user_id = $_SESSION['user']['id'];
    
    // 1. Ambil Data Alamat (Label, Nama Penerima, Telepon, Alamat Lengkap)
    $address_label = $_POST['address_label'] ?? 'Rumah';
    $receiver_name = $_POST['receiver_name'] ?? $_SESSION['user']['name'];
    $phone = $_POST['phone'] ?? $_SESSION['user']['phone'];
    $address_text = $_POST['address_text'] ?? $_SESSION['user']['address'];
    
    // Gabungkan alamat menjadi string lengkap untuk disimpan di DB
    $full_address = "$address_label - $receiver_name | $phone | $address_text";

    // 2. Data Pembayaran
    $payment_method = $_POST['payment_method'];
    $sender_account = NULL; 
    $amount_paid = $total_price; // Default: COD atau Lunas

    // Jika Transfer Bank atau E-Wallet, ambil input manual
    if ($payment_method == 'Transfer Bank' || $payment_method == 'E-Wallet') {
        $sender_account = $_POST['sender_account'] ?? NULL;
        $amount_paid = $_POST['amount_paid'] ?? 0;
    }
    
    // Validasi input
    if (empty($address_text) || empty($phone)) {
        $error = "Mohon lengkapi alamat dan nomor telepon";
    } else {
        // PERBAIKAN: Menggunakan 'created_at' bukan 'order_date'
        // Kita juga menyimpan billing_code ini ke database agar tercatat di riwayat pesanan
        $query = "INSERT INTO orders (
                    user_id, total_price, shipping_cost, 
                    status, payment_method, shipping_address, 
                    sender_account, amount_paid, created_at, billing_code
                  ) VALUES (
                    '$user_id', '$total_price', '$shipping_cost', 
                    'Menunggu Pembayaran', '$payment_method', '$full_address', 
                    '$sender_account', '$amount_paid', NOW(), '$billing_code'
                  )";
        
        $exec = mysqli_query($conn, $query);
        
        if ($exec) {
            $order_id = mysqli_insert_id($conn);
            
            // Simpan item-item order
            foreach ($cart_items as $item) {
                mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, qty, price) VALUES ('$order_id', '{$item['product_id']}', '{$item['qty']}', '{$item['price']}')");
            }
            
            // Kosongkan keranjang
            unset($_SESSION['cart']);
            
            // Redirect ke halaman pesanan
            header("Location: customer/orders.php?id=$order_id");
            exit;
        } else {
            $error = "Terjadi kesalahan sistem: " . mysqli_error($conn);
        }
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
        /* CSS TAMBAHAN KHUSUS HALAMAN INI (Agar tidak bentrok dengan style.css) */
        
        /* Override margin container agar tidak tertutup navbar lama */
        .checkout-wrapper {
            max-width: 1200px;
            margin: 120px auto 50px; /* Margin top lebih besar karena navbar lama */
            padding: 0 20px;
        }

        h2 { margin-bottom: 25px; font-size: 2rem; color: #333; }
        
        /* Grid Layout 2 Kolom */
        .checkout-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        
        /* Form Cards */
        .card { 
            background: var(--white); padding: 25px; border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; 
            border: 1px solid #f0f0f0;
        }
        .card h3 { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; font-size: 1.2rem; }

        /* Address Styling */
        .address-display {
            background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 15px; 
            border: 1px solid #eee;
        }
        .new-address-form { 
            background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 15px; 
            border: 1px dashed #ccc; 
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem; }
        .form-control {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;
        }
        .form-control:focus { outline: none; border-color: #ff5722; box-shadow: 0 0 0 2px rgba(255, 87, 34, 0.1); }

        /* Payment Methods */
        .payment-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .payment-option {
            border: 1px solid #ddd; border-radius: 8px; padding: 15px; text-align: center; cursor: pointer;
            transition: 0.2s;
        }
        .payment-option:hover { border-color: #ff5722; background: #fff5f2; }
        .payment-option.selected { border-color: #ff5722; background: #fff5f2; border-width: 2px; }
        .payment-icon { font-size: 1.5rem; display: block; margin-bottom: 8px; }

        .payment-details { 
            background: #fff8e1; padding: 15px; border-radius: 8px; border: 1px solid #ffe082; 
            animation: fadeIn 0.3s; 
        }
        .payment-details .hint { font-size: 0.85rem; color: #d32f2f; margin-bottom: 10px; display: block; font-weight: 600; }
        
        /* --- STYLES UNTUK RINGKASAN PESANAN --- */
        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 100px;
            border: 1px solid #f0f0f0;
        }
        
        .order-summary h3 {
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
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

        /* --- STYLE KODE BILLING BARU --- */
        .billing-code-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* Hidden by default via JS logic, but styling ready */
        }
        
        .billing-info {
            flex: 1;
        }
        
        .billing-label {
            font-size: 0.75rem;
            color: #546e7a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        
        .billing-number {
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0277bd;
            letter-spacing: 1px;
        }
        
        .btn-copy-code {
            background: white;
            border: 1px solid #90caf9;
            color: #0277bd;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-copy-code:hover {
            background: #0277bd;
            color: white;
        }

        /* Tombol Back Kecil (Floating) */
        .btn-back-float {
            position: fixed;
            top: 90px;
            left: 20px;
            width: 45px;
            height: 45px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            z-index: 90;
            transition: all 0.2s;
            color: #333;
            text-decoration: none;
        }
        .btn-back-float:hover {
            background: #f9f9f9;
            transform: translateX(-3px);
            color: #ff5722;
        }

        /* Helpers */
        .hidden { display: none !important; }
        .alert {
            padding: 15px; border-radius: 8px; margin-bottom: 20px;
            background: #ffebee; color: #c62828; border: 1px solid #ffcdd2;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 768px) {
            .checkout-grid { grid-template-columns: 1fr; }
            .order-summary { position: static; }
            .btn-back-float { top: 70px; left: 15px; width: 40px; height: 40px; }
        }
    </style>
</head>
<body>
    <!-- 1. NAVBAR LAMA (Dari Partials) -->
    <?php include 'partials/navbar.php'; ?>
    
    <!-- 2. TOMBOL BACK FLOATING -->
    <a href="cart.php" class="btn-back-float" title="Kembali ke Keranjang">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
    </a>

    <div class="checkout-wrapper">
        <h2>Checkout</h2>

        <?php if (isset($error)): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>

        <form id="checkoutForm" method="post">
            <div class="checkout-grid">
                <!-- KOLOM KIRI: FORM -->
                <div class="left-col">
                    
                    <!-- 1. Alamat Pengiriman -->
                    <div class="card">
                        <h3>1. Alamat Pengiriman</h3>
                        
                        <!-- Tampilkan alamat default user jika ada -->
                        <div class="address-display">
                            <strong>Alamat Saat Ini:</strong><br>
                            <?= htmlspecialchars($_SESSION['user']['name']) ?><br>
                            <?= htmlspecialchars($_SESSION['user']['phone'] ?? '-') ?><br>
                            <?= htmlspecialchars($_SESSION['user']['address'] ?? '-') ?>
                        </div>

                        <!-- Toggle Edit/Tambah Alamat -->
                        <button type="button" class="btn btn-secondary" id="btnToggleAddress" style="width: 100%; border-style: dashed;">
                            ✎ Gunakan Alamat Baru / Edit Alamat
                        </button>

                        <!-- Form Alamat Baru (Hidden by default) -->
                        <div id="newAddressForm" class="new-address-form hidden">
                            <div class="form-group">
                                <label>Label Alamat (Contoh: Rumah, Kantor)</label>
                                <input type="text" class="form-control" id="new_label" name="address_label" placeholder="Rumah" value="Rumah">
                            </div>
                            <div class="form-group">
                                <label>Nama Penerima</label>
                                <input type="text" class="form-control" id="new_name" name="receiver_name" value="<?= htmlspecialchars($_SESSION['user']['name']) ?>" placeholder="Nama Anda">
                            </div>
                            <div class="form-group">
                                <label>Nomor Telepon</label>
                                <input type="tel" class="form-control" id="new_phone" name="phone" value="<?= htmlspecialchars($_SESSION['user']['phone'] ?? '') ?>" placeholder="0812...">
                            </div>
                            <div class="form-group">
                                <label>Alamat Lengkap</label>
                                <textarea class="form-control" id="new_address_text" name="address_text" rows="3" placeholder="Jalan, No Rumah, Kecamatan, Kota..."><?= htmlspecialchars($_SESSION['user']['address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Metode Pembayaran -->
                    <div class="card">
                        <h3>2. Metode Pembayaran</h3>
                        <div class="payment-options">
                            <div class="payment-option selected" data-method="COD" onclick="selectPayment('COD', this)">
                                <span class="payment-icon">📦</span>
                                <div>COD (Bayar di Tempat)</div>
                            </div>
                            <div class="payment-option" data-method="Transfer" onclick="selectPayment('Transfer', this)">
                                <span class="payment-icon">🏦</span>
                                <div>Transfer Bank</div>
                            </div>
                            <div class="payment-option" data-method="E-Wallet" onclick="selectPayment('E-Wallet', this)">
                                <span class="payment-icon">📱</span>
                                <div>E-Wallet</div>
                            </div>
                        </div>

                        <!-- Hidden Input untuk menyimpan metode yang dipilih -->
                        <input type="hidden" name="payment_method" id="selected_payment_method" value="COD">

                        <!-- Dynamic Payment Fields -->
                        <div id="paymentFields" class="payment-details hidden">
                            <span class="hint">Silakan transfer sesuai total tagihan.</span>
                            
                            <div class="form-group">
                                <label>Nomor Rekening / E-Wallet Pengirim</label>
                                <input type="text" class="form-control" name="sender_account" placeholder="Contoh: 08123456789">
                            </div>
                            
                            <div class="form-group">
                                <label>Jumlah yang Ditransfer (Rp)</label>
                                <input type="number" class="form-control" id="amount_paid" name="amount_paid" placeholder="0">
                                <small style="color: #666; display: block; margin-top: 5px;">
                                    Total Tagihan: <strong id="displayTotal">Rp <?= number_format($total_price) ?></strong>
                                </small>
                            </div>

                            <div id="amountWarning" class="hidden" style="color: #d32f2f; font-size: 0.85rem; margin-top: 5px; font-weight: bold;">
                                ⚠️ Jumlah tidak sesuai! Admin mungkin akan menolak pesanan ini.
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL BUAT PESANAN (Diberi ID untuk JavaScript) -->
                    <button type="submit" name="checkout" id="btnSubmitOrder" class="btn btn-primary" style="width: 100%;">Buat Pesanan</button>
                </div>

                <!-- KOLOM KANAN: SUMMARY -->
                <div class="right-col">
                    <div class="order-summary">
                        <h3>Ringkasan Pesanan</h3>
                        
                        <!-- TAMBAHAN: KODE BILLING (Class hidden ditambahkan agar default tersembunyi) -->
                        <div id="billingCodeContainer" class="billing-code-box hidden">
                            <div class="billing-info">
                                <div class="billing-label">Kode Billing</div>
                                <div class="billing-number" id="billingCodeDisplay"><?= $billing_code ?></div>
                            </div>
                            <button type="button" class="btn-copy-code" onclick="copyBillingCode()">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                                Salin
                            </button>
                        </div>
                        <!-- --------------------------- -->
                        
                        <!-- PHP Loop untuk Item Keranjang -->
                        <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <?php 
                                // Menentukan path gambar
                                $image_path = 'assets/img/products/' . $item['image'];
                                if (!file_exists($image_path)) {
                                    $image_path = 'assets/img/products/default.jpg';
                                }
                                ?>
                                <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                <div class="order-item-details">
                                    <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
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
        </form>
    </div>

    <script>
        // Variabel PHP ke JS untuk validasi jumlah
        const grandTotalPHP = <?= $total_price ?>;
        const formattedTotal = "Rp <?= number_format($total_price) ?>";

        // Init
        document.addEventListener('DOMContentLoaded', () => {
            const amountInput = document.getElementById('amount_paid');
            const warningMsg = document.getElementById('amountWarning');
            const submitBtn = document.getElementById('btnSubmitOrder');

            // Fungsi Validasi
            function validateAmount() {
                // Jika input kosong, anggap 0
                const val = parseFloat(amountInput.value) || 0;
                
                // Cek apakah metode pembayaran membutuhkan input jumlah (Transfer/E-Wallet)
                const paymentMethod = document.getElementById('selected_payment_method').value;
                const isTransfer = (paymentMethod === 'Transfer Bank' || paymentMethod === 'E-Wallet');

                if (isTransfer) {
                    if (val < grandTotalPHP) {
                        // Jika kurang dari total: Tampilkan warning, Disable tombol
                        warningMsg.classList.remove('hidden');
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = "0.5";
                        submitBtn.style.cursor = "not-allowed";
                        submitBtn.innerText = "Jumlah Kurang";
                    } else {
                        // Jika sesuai atau lebih: Sembunyikan warning, Enable tombol
                        warningMsg.classList.add('hidden');
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = "1";
                        submitBtn.style.cursor = "pointer";
                        submitBtn.innerText = "Buat Pesanan";
                    }
                } else {
                    // Jika COD, pastikan tombol selalu aktif
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = "1";
                    submitBtn.innerText = "Buat Pesanan";
                }
            }

            // Event Listener saat user mengetik di input jumlah
            if(amountInput){
                amountInput.addEventListener('input', validateAmount);
            }
        });

        // Fungsi Toggle Alamat Baru
        document.getElementById('btnToggleAddress').addEventListener('click', () => {
            const form = document.getElementById('newAddressForm');
            const btn = document.getElementById('btnToggleAddress');
            
            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                btn.classList.add('hidden');
            } else {
                form.classList.add('hidden');
                btn.classList.remove('hidden');
            }
        });

        // Fungsi Pilih Metode Pembayaran
        function selectPayment(method, element) {
            // Set UI Selected
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            
            // Set Hidden Input Value
            document.getElementById('selected_payment_method').value = method;
            
            // Show/Hide Form Transfer
            const detailsDiv = document.getElementById('paymentFields');
            const amountInput = document.getElementById('amount_paid');
            const submitBtn = document.getElementById('btnSubmitOrder');
            
            // --- LOGIKA BARU: Show/Hide Kode Billing ---
            const billingContainer = document.getElementById('billingCodeContainer');
            // ---------------------------------------------

            if (method === 'COD') {
                detailsDiv.classList.add('hidden');
                amountInput.required = false;
                
                // Sembunyikan Kode Billing jika COD
                if(billingContainer) billingContainer.classList.add('hidden');
                
                // Reset tombol COD
                submitBtn.disabled = false;
                submitBtn.style.opacity = "1";
                submitBtn.innerText = "Buat Pesanan";
                submitBtn.style.cursor = "pointer";
            } else {
                detailsDiv.classList.remove('hidden');
                amountInput.required = true;

                // TAMPILKAN Kode Billing jika Transfer
                if(billingContainer) billingContainer.classList.remove('hidden');

                // OTOMATIS ISI INPUT SESUAI TOTAL TAGIHAN (UX Enhancement)
                amountInput.value = grandTotalPHP;
                
                // Trigger event input agar validasi langsung berjalan (men-enable tombol)
                // Karena nilai sudah sesuai, tombol akan otomatis aktif
                amountInput.dispatchEvent(new Event('input'));
            }
        }

        // Fungsi Copy Kode Billing
        function copyBillingCode() {
            const codeText = document.getElementById('billingCodeDisplay').innerText;
            navigator.clipboard.writeText(codeText).then(() => {
                const btn = document.querySelector('.btn-copy-code');
                const originalText = btn.innerHTML;
                
                // Ubah teks tombol sementara
                btn.innerHTML = `
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg> Tersalin!
                `;
                btn.style.background = "#4caf50";
                btn.style.color = "white";
                btn.style.borderColor = "#4caf50";
                
                // Kembalikan ke asli setelah 2 detik
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = "white";
                    btn.style.color = "#0277bd";
                    btn.style.borderColor = "#90caf9";
                }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin: ', err);
                alert('Gagal menyalin kode, silakan salin manual.');
            });
        }
    </script>
</body>
</html>