<?php
session_start();
include 'config/database.php';
include 'partials/navbar.php';

// Ambil 6 produk terbaru
 $latest_products = mysqli_query($conn, "SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 6");

// Ambil semua kategori
 $categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

// Ambil 2 testimoni
 $testimonials = [
    ['name' => 'Andi Pratama', 'comment' => 'Kualitas produk sangat bagus, pengiriman cepat! Recommended seller.', 'rating' => 5, 'image' => 'testimonial-1.jpg'],
    ['name' => 'Siti Nurhaliza', 'comment' => 'Harga terjangkau dan barang sesuai deskripsi. Puas belanja di sini.', 'rating' => 4, 'image' => 'testimonial-2.jpg']
];

// Get user profile picture if logged in
 $profile_pic = '';
if (isset($_SESSION['user'])) {
    $profile_pic = !empty($_SESSION['user']['profile_pic']) ? 'assets/img/profiles/' . $_SESSION['user']['profile_pic'] : '/assets/img/icons/icon-user.png';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bustore - Toko Online Terpercaya</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Additional styles for enhanced design */
        .hero {
            background: linear-gradient(135deg, rgba(255, 87, 34, 0.9), rgba(33, 150, 243, 0.8)), url('assets/img/hero-bg.jpg') no-repeat center center/cover;
            height: 80vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,197.3C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            animation: fadeInUp 1s ease-out;
        }
        
        .hero-content p {
            font-size: 1.4rem;
            margin-bottom: 30px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            animation: fadeInUp 1s ease-out 0.2s;
            animation-fill-mode: both;
        }
        
        .hero-content .btn {
            animation: fadeInUp 1s ease-out 0.4s;
            animation-fill-mode: both;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .hero-content .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .hero-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: #ff5722;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #e64a19;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 87, 34, 0.2);
        }
        
        .btn-secondary {
            background-color: #2196F3;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #1976D2;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.2);
        }
        
        .promo-banners {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .banner-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            cursor: pointer;
            transition: all 0.3s ease;
            height: 250px;
        }
        
        .banner-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .banner-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .banner-card:hover img {
            transform: scale(1.05);
        }
        
        .banner-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .banner-overlay h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .categories {
            margin-bottom: 50px;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        
        .category-card {
            background: white;
            padding: 25px 15px;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 160px;
        }
        
        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            color: #ff5722;
        }
        
        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .featured-products {
            margin-bottom: 50px;
        }
        
        .featured-products h2 {
            font-size: 2.2rem;
            margin-bottom: 40px;
            position: relative;
            display: inline-block;
        }
        
        .featured-products h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: #ff5722;
            border-radius: 2px;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .product-card a {
            text-decoration: none;
            color: inherit;
        }
        
        .product-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover img {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-info h4 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            height: 40px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            font-weight: 600;
        }
        
        .product-category {
            font-size: 0.9rem;
            color: #757575;
            margin-bottom: 10px;
        }
        
        .product-rating {
            font-size: 0.9rem;
            margin-bottom: 15px;
            color: #ffc107;
        }
        
        .product-rating span {
            color: #9e9e9e;
            margin-left: 5px;
        }
        
        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ff5722;
            margin-top: auto;
            margin-bottom: 15px;
        }
        
        .add-to-cart-form {
            padding: 0 20px 20px;
            margin-top: auto;
        }
        
        .add-to-cart-form .btn {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .out-of-stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 1;
        }
        
        .trust-elements {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .trust-item {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .trust-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .trust-item svg {
            width: 60px;
            height: 60px;
            margin-bottom: 20px;
        }
        
        .trust-item h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .trust-item p {
            color: #757575;
        }
        
        .testimonials {
            margin-bottom: 50px;
        }
        
        .testimonials h2 {
            font-size: 2.2rem;
            margin-bottom: 40px;
            position: relative;
            display: inline-block;
        }
        
        .testimonials h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: #ff5722;
            border-radius: 2px;
        }
        
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .testimonial-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 3px solid #f0f0f0;
        }
        
        .testimonial-content h4 {
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .testimonial-content .testimonial-rating {
            color: #ffc107;
            margin-bottom: 15px;
        }
        
        .testimonial-content p {
            font-style: italic;
            color: #555;
            line-height: 1.6;
        }
        
        .view-all-btn {
            display: block;
            width: 200px;
            margin: 40px auto 0;
            padding: 15px;
            text-align: center;
            background: #ff5722;
            color: white;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255, 87, 34, 0.3);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .view-all-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 87, 34, 0.4);
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1.2rem;
            }
            
            .hero-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .product-grid, .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .testimonial-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Selamat Datang di Bustore</h1>
            <p>Temukan produk terbaik dengan harga terjangkau</p>
            <div class="hero-actions">
                <a href="#latest-products" class="btn btn-primary">Belanja Sekarang</a>
                <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] == 'admin' || $_SESSION['user']['role'] == 'petugas')): ?>
                    <?php
                    $dashboard_path = $_SESSION['user']['role'] == 'admin' ? 'admin/dashboard.php' : 'petugas/dashboard.php';
                    ?>
                    <a href="<?= $dashboard_path ?>" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Banner Promo -->
        <section class="promo-banners">
            <div class="banner-card">
                <img src="assets/img/banner-promo-1.jpg" alt="Promo 1">
                <div class="banner-overlay">
                    <h3>Flash Sale!</h3>
                    <p>Diskon hingga 50%</p>
                </div>
            </div>
            <div class="banner-card">
                <img src="assets/img/banner-promo-2.jpg" alt="Promo 2">
                <div class="banner-overlay">
                    <h3>New Arrival</h3>
                    <p>Koleksi terbaru</p>
                </div>
            </div>
        </section>

        <!-- Kategori -->
        <section class="categories">
            <h2>Kategori Produk</h2>
            <div class="category-grid">
                <?php while($cat = mysqli_fetch_assoc($categories)) { ?>
                    <a href="category.php?slug=<?= $cat['slug'] ?>" class="category-card">
                        <div class="category-icon"><?= $cat['icon'] ?></div>
                        <span><?= $cat['name'] ?></span>
                    </a>
                <?php } ?>
            </div>
        </section>

        <!-- Produk Terbaru -->
        <section id="latest-products" class="featured-products">
            <h2>🛍️ Produk Terbaru</h2>
            <div class="product-grid">
                <?php while($p = mysqli_fetch_assoc($latest_products)) { ?>
                    <div class="product-card">
                        <?php if ($p['stock'] <= 0): ?>
                            <div class="out-of-stock-badge">Habis</div>
                        <?php endif; ?>
                        <a href="product_detail.php?id=<?= $p['id'] ?>">
                            <img src="assets/img/products/<?= $p['image'] ?: 'placeholder.jpg' ?>" alt="<?= $p['name'] ?>">
                            <div class="product-info">
                                <h4><?= $p['name'] ?></h4>
                                <p class="product-category"><?= $p['category_name'] ?></p>
                                <div class="product-rating">
                                    <?php 
                                    $rating = $p['rating'] ?? 5;
                                    for($i=1; $i<=5; $i++) { 
                                        echo $i <= floor($rating) ? '⭐' : '☆'; 
                                    } 
                                    ?>
                                    <span>(<?= $p['sold'] ?? 0 ?> terjual)</span>
                                </div>
                                <p class="product-price">Rp <?= number_format($p['price'], 0, ',', '.') ?></p>
                            </div>
                        </a>
                        <form method="post" action="cart_add.php" class="add-to-cart-form">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-secondary" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-shopping-cart"></i> <?= $p['stock'] <= 0 ? 'Stok Habis' : '+ Keranjang' ?>
                            </button>
                        </form>
                    </div>
                <?php } ?>
            </div>
            <a href="products.php" class="view-all-btn">Lihat Semua Produk</a>
        </section>

        <!-- Trust Elements -->
        <section class="trust-elements">
            <div class="trust-item">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ff5722" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16,8 20,8 23,11 23,16 16,16 16,8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                <h4>Pengiriman Cepat</h4>
                <p>Pesanan tiba 1-3 hari</p>
            </div>
            <div class="trust-item">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ff5722" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <h4>100% Original</h4>
                <p>Produk terjamin asli</p>
            </div>
            <div class="trust-item">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ff5722" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"></polygon>
                </svg>
                <h4>Rating Terpercaya</h4>
                <p>Ratusan review positif</p>
            </div>
        </section>

        <!-- Testimoni -->
        <section class="testimonials">
            <h2>Apa Kata Mereka?</h2>
            <div class="testimonial-grid">
                <?php foreach($testimonials as $t) { ?>
                    <div class="testimonial-card">
                        <img src="assets/img/<?= $t['image'] ?>" alt="<?= $t['name'] ?>">
                        <div class="testimonial-content">
                            <h4><?= $t['name'] ?></h4>
                            <div class="testimonial-rating"><?php for($i=1;$i<=$t['rating']; $i++) echo '⭐'; ?></div>
                            <p>"<?= $t['comment'] ?>"</p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>
    </div>

    <?php include 'partials/footer.php'; ?>
    
    <div class="overlay" id="overlay"></div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>