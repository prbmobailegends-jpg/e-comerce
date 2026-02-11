<?php
session_start();
include '../config/database.php';

if ($_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle add category
if (isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $slug = strtolower(str_replace(' ', '-', $name));
    $icon = $_POST['icon'];
    
    // Check if category already exists
    $check = mysqli_query($conn, "SELECT * FROM categories WHERE slug='$slug'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Kategori sudah ada!";
    } else {
        mysqli_query($conn, "INSERT INTO categories (name, slug, icon) VALUES ('$name', '$slug', '$icon')");
        $_SESSION['success'] = "Kategori berhasil ditambahkan!";
    }
    
    header("Location: categories.php");
    exit;
}

// Handle delete category
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if category has products
    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE category_id=$id");
    $result = mysqli_fetch_assoc($check);
    
    if ($result['count'] > 0) {
        $_SESSION['error'] = "Tidak bisa menghapus kategori yang memiliki produk!";
    } else {
        mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
        $_SESSION['success'] = "Kategori berhasil dihapus!";
    }
    
    header("Location: categories.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - Bustore Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            font-size: 2rem;
            color: #333;
            position: relative;
            padding-bottom: 10px;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: #ff5722;
            border-radius: 2px;
        }
        
        .page-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #ff5722;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e64a19;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 87, 34, 0.2);
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        .btn-danger:hover {
            background: #d32f2f;
        }
        
        .categories-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .category-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .category-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .category-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .category-slug {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
        }
        
        .category-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: #ff5722;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            transition: color 0.2s;
        }
        
        .close-btn:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff5722;
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
        }
        
        .icon-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .icon-option {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1.5rem;
        }
        
        .icon-option:hover {
            border-color: #ff5722;
            background-color: #fff5f5;
        }
        
        .icon-option.selected {
            border-color: #ff5722;
            background-color: #fff5f5;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        @media (max-width: 768px) {
            .categories-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Kategori Produk</h1>
            <div class="page-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali ke Dashboard
                </a>
                <button class="btn btn-primary" onclick="openModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Tambah Kategori
                </button>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="categories-container">
            <?php
            $categories_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
            while ($category = mysqli_fetch_assoc($categories_query)) {
                // Get product count for this category
                $product_count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE category_id = {$category['id']}");
                $product_count = mysqli_fetch_assoc($product_count_query)['count'];
            ?>
            <div class="category-card">
                <div class="category-header">
                    <div class="category-icon"><?= $category['icon'] ?></div>
                    <button class="btn btn-danger" onclick="confirmDelete(<?= $category['id'] ?>)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
                <div class="category-name"><?= $category['name'] ?></div>
                <div class="category-slug"><?= $category['slug'] ?></div>
                <div class="category-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= $product_count ?></div>
                        <div class="stat-label">Produk</div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <!-- Modal for adding category -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Tambah Kategori Baru</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <form method="post" action="categories.php">
                <div class="form-group">
                    <label for="name">Nama Kategori</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Pilih Icon</label>
                    <div class="icon-options">
                        <div class="icon-option" data-icon="👕">👕</div>
                        <div class="icon-option" data-icon="👟">👟</div>
                        <div class="icon-option" data-icon="👜">👜</div>
                        <div class="icon-option" data-icon="⌚">⌚</div>
                        <div class="icon-option" data-icon="🍔">🍔</div>
                        <div class="icon-option" data-icon="📱">📱</div>
                        <div class="icon-option" data-icon="💻">💻</div>
                        <div class="icon-option" data-icon="🎧">🎧</div>
                        <div class="icon-option" data-icon="📷">📷</div>
                        <div class="icon-option" data-icon="🎮">🎮</div>
                        <div class="icon-option" data-icon="🏠">🏠</div>
                        <div class="icon-option" data-icon="⚽">⚽</div>
                    </div>
                    <input type="hidden" id="icon" name="icon" value="👕" required>
                </div>
                
                <button type="submit" name="add_category" class="btn btn-primary" style="width: 100%;">Tambah Kategori</button>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openModal() {
            document.getElementById('categoryModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }
        
        // Icon selection
        document.querySelectorAll('.icon-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.icon-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Update hidden input value
                document.getElementById('icon').value = this.getAttribute('data-icon');
            });
        });
        
        // Set first icon as selected by default
        document.querySelector('.icon-option').classList.add('selected');
        
        // Confirm delete function
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
                window.location.href = 'categories.php?delete=' + id;
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('categoryModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>