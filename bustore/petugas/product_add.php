<?php
session_start();
include '../config/database.php';

if ($_SESSION['user']['role'] != 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $desc = $_POST['description'];
    $rating = $_POST['rating'];
    $sold = $_POST['sold'];

    // Dapatkan nama kategori untuk membuat path folder
    $category = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM categories WHERE id = $category_id"));
    $category_folder = mysqli_real_escape_string($conn, $category['name']);

    $img = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    
    // Validasi file
    $allowed_types = ['jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($img, PATHINFO_EXTENSION));
    
    if (in_array($file_extension, $allowed_types)) {
        // Buat nama file unik
        $new_filename = time() . "_" . str_replace(' ', '-', $img);
        // Path lengkap ke folder kategori
        $upload_path = "../assets/img/products/" . $category_folder . "/" . $new_filename;
        
        move_uploaded_file($tmp_name, $upload_path);

        // Simpan path relatif ke database
        $db_image_path = $category_folder . "/" . $new_filename;

        mysqli_query($conn, "INSERT INTO products 
            (name,price,stock,category_id,image,description,rating,sold)
            VALUES ('$name','$price','$stock','$category_id','$db_image_path','$desc','$rating','$sold')");

        header("Location: products.php");
        exit;
    } else {
        $error = "Hanya file JPG, JPEG, atau PNG yang diperbolehkan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Bustore Petugas</title>
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
            background: #2196F3;
            color: white;
        }
        .btn-primary:hover {
            background: #1976D2;
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
        <h2>Tambah Produk</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">Kategori</label>
                <select name="category_id" id="category_id" required>
                    <?php
                    $cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
                    while($cat = mysqli_fetch_assoc($cats)) {
                        echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Harga</label>
                <input type="number" id="price" name="price" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stok</label>
                <input type="number" id="stock" name="stock" required>
            </div>
            
            <div class="form-group">
                <label for="image">Gambar</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <small>Gambar akan disimpan sesuai kategori yang dipilih.</small>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="rating">Rating (contoh: 4.5)</label>
                <input type="number" id="rating" name="rating" step="0.1" value="4.5" required>
            </div>

            <div class="form-group">
                <label for="sold">Terjual (contoh: 10)</label>
                <input type="number" id="sold" name="sold" value="0" required>
            </div>

            <button type="submit" name="save" class="btn btn-primary">Simpan</button>
            <a href="products.php" class="btn btn-gray">Batal</a>
        </form>
    </div>

</body>
</html>