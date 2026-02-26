<?php
session_start();
include '../config/database.php';

// Check permissions
if ($_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}

// Get product ID
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID produk tidak valid";
    header("Location: products.php");
    exit;
}

// Get product data
 $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$id"));

if (!$product) {
    $_SESSION['error'] = "Produk tidak ditemukan";
    header("Location: products.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $rating = $_POST['rating'];
    $sold = $_POST['sold'];
    
    // Handle image upload if provided
    $image = $product['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $img = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        
        // Validasi file
        $allowed_types = ['jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($img, PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_types)) {
            // Dapatkan nama kategori untuk membuat path folder
            $category = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM categories WHERE id = $category_id"));
            $category_folder = mysqli_real_escape_string($conn, $category['name']);
            
            // Hapus gambar lama jika ada
            if (!empty($product['image'])) {
                $old_image_path = "../assets/img/" . $product['image'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            
            // Buat nama file unik
            $new_filename = time() . "_" . str_replace(' ', '-', $img);
            // Path lengkap ke folder kategori
            $upload_path = "../assets/img/products/" . $category_folder . "/" . $new_filename;
            
            move_uploaded_file($tmp_name, $upload_path);
            
            // Simpan path relatif ke database
            $image = $category_folder . "/" . $new_filename;
        }
    }
    
    // Update product
    $update_query = "UPDATE products SET 
                    name='$name', 
                    price='$price', 
                    stock='$stock', 
                    category_id='$category_id', 
                    image='$image', 
                    description='$description',
                    rating='$rating',
                    sold='$sold'
                    WHERE id=$id";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Produk berhasil diperbarui";
        header("Location: products.php");
        exit;
    } else {
        $error = "Gagal memperbarui produk: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Bustore Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-container {
            max-width: 700px;
            margin: 100px auto;
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
        .current-image {
            margin-bottom: 10px;
        }
        .current-image img {
            max-width: 200px;
            height: auto;
            border-radius: 4px;
            border: 1px solid #ddd;
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
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-gray {
            background: #f0f0f0;
            color: #555;
        }
        .btn-gray:hover {
            background: #e0e0e0;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="form-container">
        <!-- Tombol Kembali ke Dashboard -->
        <a href="dashboard.php" class="btn-dashboard-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Dashboard
        </a>

        <h2>Edit Produk</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input type="text" id="name" name="name" value="<?= $product['name'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">Kategori</label>
                <select name="category_id" id="category_id" required>
                    <?php
                    $cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
                    while($cat = mysqli_fetch_assoc($cats)) {
                        $selected = $cat['id'] == $product['category_id'] ? 'selected' : '';
                        echo "<option value='{$cat['id']}' $selected>{$cat['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Harga</label>
                <input type="number" id="price" name="price" value="<?= $product['price'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stok</label>
                <input type="number" id="stock" name="stock" value="<?= $product['stock'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="image">Gambar</label>
                <div class="current-image">
                    <img src="../assets/img/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                </div>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Biarkan kosong jika tidak ingin mengubah gambar.</small>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4"><?= $product['description'] ?></textarea>
            </div>

            <div class="form-group">
                <label for="rating">Rating (contoh: 4.5)</label>
                <input type="number" id="rating" name="rating" step="0.1" value="<?= $product['rating'] ?>" required>
            </div>

            <div class="form-group">
                <label for="sold">Terjual (contoh: 10)</label>
                <input type="number" id="sold" name="sold" value="<?= $product['sold'] ?>" required>
            </div>

            <button type="submit" name="update" class="btn btn-success">Update</button>
            <a href="products.php" class="btn btn-gray">Batal</a>
        </form>
    </div>

</body>
</html>