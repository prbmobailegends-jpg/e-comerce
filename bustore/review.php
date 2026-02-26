<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// Ambil data dari URL
 $order_id = (int)$_GET['order_id'];
 $product_id = (int)$_GET['product_id'];
 $user_id = $_SESSION['user']['id'];

// 1. Cek apakah user sudah pernah memberi review untuk produk DI ORDER INI
 $check_review = mysqli_query($conn, "SELECT * FROM product_reviews WHERE order_id=$order_id AND product_id=$product_id AND user_id=$user_id");
 $review_data = mysqli_fetch_assoc($check_review);

 $is_edit_mode = false;
 $can_edit = false;
 $error = "";
 $success_msg = "";

// 2. Logika Waktu Edit (5 Menit)
if ($review_data) {
    $created_time = strtotime($review_data['created_at']);
    $time_diff = time() - $created_time;
    
    if ($time_diff <= 300) { // 300 detik = 5 menit
        $can_edit = true;
        $is_edit_mode = true; // Masuk mode edit form
    } else {
        $can_edit = false;
    }
}

// 3. Handle Tombol Hapus (Jika User klik tombol hapus manual)
if (isset($_POST['delete_review'])) {
    if ($review_data) {
        mysqli_query($conn, "DELETE FROM product_reviews WHERE id = {$review_data['id']}");
        // Recalculate rating
        $avg_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg_r FROM product_reviews WHERE product_id = $product_id"));
        $new_avg = ($avg_data['avg_r']) ? round($avg_data['avg_r'], 1) : 0;
        mysqli_query($conn, "UPDATE products SET rating = $new_avg WHERE id = $product_id");
        
        // Redirect refresh agar data bersih (hilang ulasan lama)
        header("Location: review.php?product_id=$product_id&order_id=$order_id");
        exit;
    }
}

// Ambil detail produk
 $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$product_id"));
if (!$product) die("Produk tidak ditemukan.");

// 4. Handle Submit Form (Edit atau Baru)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete_review'])) {
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    if ($rating < 1 || $rating > 5) {
        $error = "Rating harus antara 1 sampai 5.";
    } elseif (empty($comment)) {
        $error = "Komentar tidak boleh kosong.";
    } else {
        // Handle Upload Gambar
        $review_image = "";
        if(isset($_FILES['review_image']) && $_FILES['review_image']['error'] == 0){
            $target_dir = "assets/img/reviews/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $file_name = time() . '_' . basename($_FILES["review_image"]["name"]);
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES["review_image"]["tmp_name"], $target_file)) {
                $review_image = $file_name;
            }
        }

        if ($is_edit_mode) {
            // --- UPDATE Ulasan Lama ---
            // Jika tidak upload gambar baru, pakai gambar lama
            $final_image = (!empty($review_image)) ? $review_image : $review_data['image'];
            
            $query = "UPDATE product_reviews 
                      SET rating = '$rating', comment = '$comment', updated_at = NOW(), image = '$final_image'
                      WHERE id = {$review_data['id']}";
            $msg = "Ulasan berhasil diperbarui!";
        } else {
            // --- INSERT Ulasan Baru ---
            $image_val = (!empty($review_image)) ? "'$review_image'" : "NULL";
            
            $query = "INSERT INTO product_reviews (order_id, product_id, user_id, rating, comment, created_at, image) 
                      VALUES ('$order_id', '$product_id', '$user_id', '$rating', '$comment', NOW(), $image_val)";
            $msg = "Terima kasih! Ulasan baru berhasil disimpan.";
        }
        
        if (mysqli_query($conn, $query)) {
            // Recalculate Rating Average
            $avg_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg_r FROM product_reviews WHERE product_id = $product_id"));
            $new_avg = ($avg_data['avg_r']) ? round($avg_data['avg_r'], 1) : 0;
            mysqli_query($conn, "UPDATE products SET rating = $new_avg WHERE id = $product_id");

            $success_msg = $msg;
            // Redirect ke detail pesanan atau detail produk
            header("refresh:2;url=customer/order_detail.php?id=$order_id&msg=review_success");
            exit;
        } else {
            $error = "Gagal menyimpan ulasan: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Ulasan - Bustore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .review-container {
            max-width: 600px;
            margin: 100px auto 40px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        /* Container Ulasan Lama */
        .existing-review-container {
            background: #fff8e1;
            border: 1px solid #ffe0b2;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .review-date {
            font-size: 12px;
            color: #e65100;
            font-weight: bold;
        }
        
        .review-content-text {
            font-size: 15px;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .review-img-preview {
            margin-top: 10px;
            max-width: 100px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .product-preview {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom:1px solid #eee;
        }
        .product-preview img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius:8px;
        }
        
        .star-rating {
            display: flex;
            flex-direction: row-reverse; 
            justify-content: flex-end;
            gap: 5px;
            margin-bottom: 10px;
        }
        .star-rating input { display: none; }
        .star-rating label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107;
        }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-control {
            width: 100%; 
            padding: 10px; 
            border:1px solid #ddd; 
            border-radius: 5px; 
            font-family: inherit;
            resize: vertical;
        }
        .form-control:focus { outline: none; border-color: #ff5722; }
        
        .btn {
            padding: 12px 24px; 
            background: #ff5722; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600; 
            width: 100%;
            transition: background 0.3s;
        }
        .btn:hover { background: #e64a19; }
        
        .btn-danger { background: #d32f2f; margin-top: 10px; width: auto; font-size: 14px; padding: 8px 16px;}
        .btn-danger:hover { background: #c62828; }
        
        .text-error { color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ffcdd2; }
        .text-success { color: #388e3c; background: #e8f5e9; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #c8e6c9; }
        
        .warning-box {
            background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 13px;
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar.php'; ?>
    
    <div class="review-container">
        <h2>Kelola Ulasan Produk</h2>
        
        <?php if($error): ?>
            <div class="text-error"><?= $error ?></div>
        <?php endif; ?>

        <?php if($success_msg): ?>
            <div class="text-success"><?= $success_msg ?></div>
        <?php endif; ?>

        <!-- CONTAINER ULASAN LAMA (Muncul jika data ada) -->
        <?php if ($review_data): ?>
            <div class="existing-review-container">
                <div class="review-header">
                    <strong><i class="fas fa-history"></i> Ulasan Anda Sebelumnya:</strong>
                    <span class="review-date"><?= date('d M Y, H:i', strtotime($review_data['created_at'])) ?></span>
                </div>
                
                <div class="review-content-text">
                    <?= htmlspecialchars($review_data['comment']) ?>
                </div>

                <?php if(!empty($review_data['image'])): ?>
                    <div>
                        <small>Foto Lama:</small><br>
                        <img src="assets/img/reviews/<?= $review_data['image'] ?>" class="review-img-preview">
                    </div>
                <?php endif; ?>

                <!-- OPSI: HAPUS -->
                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');">
                    <input type="hidden" name="delete_review" value="1">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Hapus Ulasan Ini
                    </button>
                </form>

                <?php if (!$can_edit): ?>
                    <div class="warning-box" style="margin-top: 10px;">
                        <i class="fas fa-info-circle"></i> Masa edit telah berakhir (Lebih dari 5 menit). Anda tidak bisa mengedit teks, tapi bisa <b>Hapus</b> ulasan ini untuk membuat yang baru.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- FORM INPUT (Muncul jika Belum ada review, atau Masih bisa edit) -->
        <?php if (!$review_data || $can_edit): ?>
            
            <?php if($review_data): ?>
                <h3 style="margin: 20px 0 10px; color: #555; border-bottom: 1px dashed #ccc; padding-bottom: 5px;">
                    <?= $can_edit ? 'Edit Ulasan Di Atas' : 'Form Dinonaktifkan' ?>
                </h3>
            <?php endif; ?>

            <div class="product-preview">
                <img src="assets/img/products/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                <div>
                    <h4><?= $product['name'] ?></h4>
                    <p>Rp <?= number_format($product['price']) ?></p>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Rating Produk</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required /><label for="star5" title="5 bintang">★</label>
                        <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 bintang">★</label>
                        <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 bintang">★</label>
                        <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 bintang">★</label>
                        <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 bintang">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Unggah Foto (Opsional)</label>
                    <input type="file" name="review_image" class="form-control" accept="image/*">
                    <?php if($is_edit_mode): ?>
                        <small style="color: #666;">Biarkan kosong jika tidak ingin mengubah foto lama.</small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Komentar Anda</label>
                    <textarea name="comment" rows="4" class="form-control" placeholder="Ceritakan pengalaman Anda berbelanja..." required><?= $is_edit_mode ? htmlspecialchars($review_data['comment']) : '' ?></textarea>
                </div>

                <button type="submit" class="btn"><?= $is_edit_mode ? 'Simpan Perubahan' : 'Kirim Ulasan Baru' ?></button>
            </form>

            <!-- Script Auto Check Radio Star jika Edit -->
            <?php if ($is_edit_mode): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var rating = "<?= $review_data['rating'] ?>";
                        if(document.getElementById("star" + rating)) {
                            document.getElementById("star" + rating).checked = true;
                        }
                    });
                </script>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Jika ada review tapi tidak bisa edit -->
            <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                <i class="fas fa-lock" style="font-size: 24px; color: #999; margin-bottom: 10px;"></i>
                <p style="color: #666;">Form ulasan baru dikunci. Silakan hapus ulasan lama di atas untuk membuat yang baru.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>