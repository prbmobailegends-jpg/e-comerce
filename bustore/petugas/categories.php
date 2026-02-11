<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}
include '../config/database.php';

// Handle Add Category
if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $icon = mysqli_real_escape_string($conn, $_POST['icon']);

    if (!empty($name) && !empty($icon)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, icon) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $name, $icon);
        mysqli_stmt_execute($stmt);
        header("Location: categories.php?success=added");
        exit;
    } else {
        $error = "Nama dan Ikon kategori tidak boleh kosong.";
    }
}

// Handle Update Category
if (isset($_POST['update_category'])) {
    $id = $_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['edit_name']);
    $icon = mysqli_real_escape_string($conn, $_POST['edit_icon']);

    if (!empty($name) && !empty($icon)) {
        $stmt = mysqli_prepare($conn, "UPDATE categories SET name = ?, icon = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $name, $icon, $id);
        mysqli_stmt_execute($stmt);
        header("Location: categories.php?success=updated");
        exit;
    } else {
        $error = "Nama dan Ikon kategori tidak boleh kosong.";
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // A good practice is to check if there are products in this category before deleting
    // For simplicity, we will delete directly
    $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: categories.php?success=deleted");
    exit;
}

// Fetch all categories with product count
 $categories = mysqli_query($conn, "
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Bustore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .page-header { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .page-header h1 { color: #2c3e50; margin: 0; font-size: 1.8rem; }
        .card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .card-header { border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 25px; }
        .card-header h2 { color: #2c3e50; margin: 0; font-size: 1.4rem; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
        .form-group input:focus { outline: none; border-color: #2196F3; box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1); }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease; }
        .btn-primary { background: linear-gradient(135deg, #2196F3, #03A9F4); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #dc3545; color: white; padding: 8px 12px; font-size: 0.9rem; }
        .btn-danger:hover { background: #c82333; }
        .btn-edit { background: #28a745; color: white; padding: 8px 12px; font-size: 0.9rem; margin-right: 5px; }
        .btn-edit:hover { background: #218838; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { padding: 15px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .table th { background-color: #f8f9fa; font-weight: 600; color: #495057; }
        .table tr:hover { background-color: #f8f9fa; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid transparent; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 30px; border: none; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 5px 25px rgba(0,0,0,0.2); animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: #000; }
        .icon-preview { font-size: 2rem; }
        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; }
            .table { font-size: 0.9rem; }
            .table th, .table td { padding: 10px; }
            .btn-edit, .btn-danger { padding: 6px 10px; font-size: 0.8rem; }
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Kelola Kategori Produk</h1>
            <a href="dashboard.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php
                if ($_GET['success'] == 'added') echo 'Kategori berhasil ditambahkan!';
                if ($_GET['success'] == 'updated') echo 'Kategori berhasil diperbarui!';
                if ($_GET['success'] == 'deleted') echo 'Kategori berhasil dihapus!';
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Tambah Kategori Baru</h2>
            </div>
            <form action="categories.php" method="POST">
                <div class="form-group">
                    <label for="name">Nama Kategori</label>
                    <input type="text" id="name" name="name" placeholder="Contoh: Pakaian" required>
                </div>
                <div class="form-group">
                    <label for="icon">Ikon (Emoji)</label>
                    <input type="text" id="icon" name="icon" placeholder="Contoh: 👕" maxlength="2" required>
                </div>
                <button type="submit" name="add_category" class="btn btn-primary">Tambah Kategori</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Daftar Kategori</h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Ikon</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Produk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($category = mysqli_fetch_assoc($categories)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="icon-preview"><?= $category['icon'] ?></span></td>
                        <td><?= htmlspecialchars($category['name']) ?></td>
                        <td><?= $category['product_count'] ?></td>
                        <td>
                            <button class="btn btn-edit" onclick="openEditModal('<?= $category['id'] ?>', '<?= htmlspecialchars($category['name']) ?>', '<?= $category['icon'] ?>')">Edit</button>
                            <a href="categories.php?delete=<?= $category['id'] ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Kategori</h2>
            <form action="categories.php" method="POST" style="margin-top: 20px;">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_name">Nama Kategori</label>
                    <input type="text" id="edit_name" name="edit_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_icon">Ikon (Emoji)</label>
                    <input type="text" id="edit_icon" name="edit_icon" maxlength="2" required>
                </div>
                <button type="submit" name="update_category" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, icon) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_icon').value = icon;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>