<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

 $order_id = $_GET['id'];

// pastikan order milik customer dan statusnya masih 'Dibayar'
 $order = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM orders 
     WHERE id=$order_id AND user_id=".$_SESSION['user']['id']." AND status='Dibayar'"
));

if (!$order) {
    die("Pesanan tidak ditemukan atau tidak bisa diupload bukti.");
}

if (isset($_POST['upload'])) {
    $img = $_FILES['proof']['name'];
    $tmp_name = $_FILES['proof']['tmp_name'];
    
    // Validasi file
    $allowed_types = ['jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($img, PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        $error = "Hanya file JPG, JPEG, atau PNG yang diperbolehkan.";
    } elseif ($_FILES['proof']['size'] > 2000000) { // 2MB max
        $error = "Ukuran file maksimal 2MB.";
    } else {
        // Buat nama file unik
        $new_filename = "payment_" . $order_id . "_" . time() . "." . $file_extension;
        
        if (move_uploaded_file($tmp_name, "../assets/img/" . $new_filename)) {
            // Update database
            mysqli_query($conn,
            "UPDATE orders SET payment_proof='$new_filename', status='Diproses' WHERE id=$order_id");
            
            $success = "Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.";
        } else {
            $error = "Gagal mengupload file. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran - Bustore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .payment-info {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .payment-info p {
            margin: 5px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .file-input {
            display: none;
        }
        .file-label {
            display: block;
            padding: 12px;
            background: #f5f5f5;
            border: 2px dashed #ddd;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .file-label:hover {
            background: #f0f0f0;
            border-color: #ff5722;
        }
        .btn {
            padding: 12px 20px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.2s;
            width: 100%;
        }
        .btn-orange {
            background: #ff5722;
            color: white;
        }
        .btn-orange:hover {
            background: #d6371f;
        }
        .btn-gray {
            background: #f0f0f0;
            color: #555;
            width: 100%;
            text-align: center;
            margin-top: 10px;
        }
        .btn-gray:hover {
            background: #e0e0e0;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #e7f9e7;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .preview-image {
            max-width: 100%;
            margin-top: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>
    
    <div class="payment-container">
        <div class="payment-header">
            <h2>Upload Bukti Pembayaran</h2>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?= $success ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="orders.php" class="btn btn-orange">Lihat Pesanan Saya</a>
            </div>
        <?php else: ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <div class="payment-info">
                <p><strong>ID Pesanan:</strong> #<?= $order['id'] ?></p>
                <p><strong>Total:</strong> Rp <?= number_format($order['total_price']) ?></p>
                <p><strong>Status:</strong> <?= $order['status'] ?></p>
            </div>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="proof">Upload Bukti Pembayaran</label>
                    <input type="file" id="proof" name="proof" class="file-input" required onchange="previewFile(this)">
                    <label for="proof" class="file-label">
                        <span id="file-label-text">Pilih file bukti pembayaran</span>
                    </label>
                    <img id="preview" class="preview-image" style="display: none;">
                </div>
                
                <button type="submit" name="upload" class="btn btn-orange">Upload Bukti</button>
            </form>
            
            <a href="orders.php" class="btn btn-gray">Kembali ke Pesanan Saya</a>
        <?php endif; ?>
    </div>
    
    <script>
        function previewFile(input) {
            const file = input.files[0];
            const preview = document.getElementById('preview');
            const fileLabelText = document.getElementById('file-label-text');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    fileLabelText.textContent = file.name;
                }
                
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>