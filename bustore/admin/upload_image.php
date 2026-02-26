<?php
session_start();
include '../config/database.php';

if ($_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

 $image_dir = '../assets/img/products/';
 $categories = [];
 $dirs = glob($image_dir . '*', GLOB_ONLYDIR);

foreach ($dirs as $dir) {
    $category_name = basename($dir);
    $categories[] = $category_name;
}

if (isset($_POST['upload'])) {
    $selected_category = $_POST['category'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    
    $img = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    
    if ($img && $tmp_name) {
        $new_filename = time() . "_" . str_replace(' ', '-', $img);
        $upload_path = $image_dir . $selected_category . "/" . $new_filename;
        
        if (move_uploaded_file($tmp_name, $upload_path)) {
            // Cari ID kategori
            $category = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM categories WHERE name='$selected_category'"));
            $category_id = $category['id'];
            
            // Simpan ke database
            $db_image_path = $selected_category . "/" . $new_filename;
            mysqli_query($conn, "INSERT INTO products (name, price, category_id, image, description) VALUES ('$product_name', $price, $category_id, '$db_image_path', '$description')");
            
            $success = "Produk berhasil ditambahkan!";
        } else {
            $error = "Gagal mengupload gambar.";
        }
    } else {
        $error = "Pilih gambar terlebih dahulu.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Gambar Produk - Bustore Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .upload-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* --- STYLE TOMBOL KEMBALI --- */
        .btn-dashboard-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #555;
            border: 1px solid #e0e0e0;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 20px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .btn-dashboard-back:hover {
            background: #f8f9fa;
            color: #ff5722;
            border-color: #ff5722;
            transform: translateX(-3px);
        }
        /* ----------------------------- */
        
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
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
        }
        .btn-primary {
            background: #ff5722;
            color: white;
        }
        .btn-primary:hover {
            background: #e64a19;
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
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="upload-container">
        <!-- Tombol Kembali ke Dashboard -->
        <a href="dashboard.php" class="btn-dashboard-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Dashboard
        </a>

        <h2>Upload Gambar Produk</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category">Pilih Kategori Folder</label>
                <select name="category" id="category" required>
                    <?php
                    // Pastikan $categories sudah ada
                    if (!empty($categories)) {
                        foreach ($categories as $cat) {
                            echo "<option value='" . htmlspecialchars($cat) . "'>" . htmlspecialchars($cat) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Tidak ada kategori</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product_name">Nama Produk</label>
                <input type="text" id="product_name" name="product_name" required>
            </div>

            <div class="form-group">
                <label for="price">Harga</label>
                <input type="number" id="price" name="price" required>
            </div>

            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="image">Pilih Gambar</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>

            <button type="submit" name="upload" class="btn btn-primary">Upload Produk</button>
        </form>
    </div>
</body>
</html>