<?php
session_start();
include '../config/database.php';

if ($_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk - Bustore Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .add-product-btn {
            margin-bottom: 20px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f9f9f9;
            font-weight: 600;
        }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
            margin-right: 5px;
        }
        .btn-edit {
            background: #ffc107;
            color: #212529;
        }
        .btn-edit:hover {
            background: #e0a800;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .btn-add {
            background: #28a745;
            color: white;
        }
        .btn-add:hover {
            background: #218838;
        }
        .btn-back {
            background: #f0f0f0;
            color: #555;
            margin-top: 20px;
            display: inline-block;
        }
        .btn-back:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container">
        <h2>Data Produk</h2>
        <a href="product_add.php" class="btn btn-add add-product-btn">+ Tambah Produk</a>
        
        <table>
            <tr>
                <th>No</th>
                <th>Gambar</th>
                <th>Nama</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
            <?php
            $no = 1;
            $q = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
            while ($p = mysqli_fetch_assoc($q)) {
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><img src="../assets/img/<?= $p['image'] ?>" alt="<?= $p['name'] ?>" class="product-img"></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td>Rp <?= number_format($p['price']) ?></td>
                <td><?= $p['stock'] ?></td>
                <td>
                    <a href="product_edit.php?id=<?= $p['id'] ?>" class="btn btn-edit">Edit</a> 
                    <a href="product_delete.php?id=<?= $p['id'] ?>" class="btn btn-delete" onclick="return confirm('Hapus produk?')">Hapus</a>
                </td>
            </tr>
            <?php } ?>
        </table>
        
        <a href="dashboard.php" class="btn btn-back">⬅ Kembali</a>
    </div>

</body>
</html>