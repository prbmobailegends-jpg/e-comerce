<?php
session_start();
include 'config/database.php';
include 'partials/navbar.php';

// Ambil semua produk
 $all_products = mysqli_query($conn, "SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
?>

<link rel="stylesheet" href="assets/css/style.css">

<div class="container">
    <h2>Semua Produk</h2>
    <div class="product-grid">
        <?php while($p = mysqli_fetch_assoc($all_products)) { ?>
            <div class="product-card">
                <a href="product.php?id=<?= $p['id'] ?>">
                    <img src="assets/img/products/<?= $p['image'] ?>" alt="<?= $p['name'] ?>">
                    <div class="product-info">
                        <h4><?= $p['name'] ?></h4>
                        <p class="product-category"><?= $p['category_name'] ?></p>
                        <div class="product-rating">
                            <?php for($i=1; $i<=5; $i++) { echo $i <= floor($p['rating']) ? '⭐' : '☆'; } ?>
                            <span>(<?= $p['sold'] ?> terjual)</span>
                        </div>
                        <p class="product-price">Rp <?= number_format($p['price']) ?></p>
                    </div>
                </a>
                <form method="post" action="cart_add.php" class="add-to-cart-form">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-secondary">+ Keranjang</button>
                </form>
            </div>
        <?php } ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>