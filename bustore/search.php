<?php
session_start();
include 'config/database.php';
include 'partials/navbar.php';

 $query = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
 $results = [];

if (!empty($query)) {
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.name LIKE '%$query%' OR p.description LIKE '%$query%'";
    $results = mysqli_query($conn, $sql);
}
?>

<link rel="stylesheet" href="css/style.css">

<div class="container">
    <h2>Hasil Pencarian untuk "<?= htmlspecialchars($query) ?>"</h2>

    <?php if (mysqli_num_rows($results) > 0): ?>
        <div class="product-grid">
            <?php while($p = mysqli_fetch_assoc($results)) { ?>
                <div class="product-card">
                    <a href="product.php?id=<?= $p['id'] ?>">
                        <img src="assets/img/<?= $p['image'] ?>" alt="<?= $p['name'] ?>">
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
    <?php else: ?>
        <p>Tidak ada produk ditemukan untuk pencarian "<strong><?= htmlspecialchars($query) ?></strong>".</p>
    <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>x