<?php
 $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
 $base_path = ''; 
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/petugas/') !== false) {
    $base_path = '../';
} elseif (strpos($_SERVER['REQUEST_URI'], '/customer/') !== false) {
    $base_path = '../';
}

// Get user profile picture if logged in
 $profile_pic = '';
if (isset($_SESSION['user'])) {
    $profile_pic = !empty($_SESSION['user']['profile_pic']) ? $base_path . 'assets/img/profiles/' . $_SESSION['user']['profile_pic'] : $base_path . 'assets/img/icons/icon-user.png';
}
?>
<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a href="<?= $base_path ?>index.php">
                <img src="<?= $base_path ?>assets/img/logo/logo1.png" alt="Bustore Logo" class="brand-logo">
                <span>Bustore</span>
            </a>
        </div>
        
        <div class="navbar-search">
            <form action="<?= $base_path ?>search.php" method="get">
                <div class="search-container">
                    <input type="text" name="q" placeholder="Cari produk..." class="search-input">
                    <button type="submit" class="search-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <div class="navbar-actions">
            <div class="action-buttons">
                <a href="#" class="action-btn" title="Wishlist" id="wishlist-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    <?php if (isset($_SESSION['wishlist']) && count($_SESSION['wishlist']) > 0): ?>
                        <span class="wishlist-badge"><?= count($_SESSION['wishlist']) ?></span>
                    <?php endif; ?>
                </a>
                
                <a href="<?= $base_path ?>cart.php" class="action-btn" title="Keranjang">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
                
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="navbar-dropdown">
                        <button class="action-btn profile-btn" title="Profil">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <div class="user-avatar">
                                    <img src="<?= $profile_pic ?>" alt="Profile" class="avatar-img">
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?= $_SESSION['user']['name'] ?></div>
                                    <div class="user-role"><?= ucfirst($_SESSION['user']['role']) ?></div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="<?= $base_path ?>profile.php" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Profil Saya
                            </a>
                            <?php if ($_SESSION['user']['role'] == 'customer'): ?>
                            <a href="<?= $base_path ?>customer/orders.php" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                Pesanan Saya
                            </a>
                            <a href="<?= $base_path ?>profile.php#chat" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                Chat dengan Admin
                            </a>
                            <?php endif; ?>
                            <?php if ($_SESSION['user']['role'] == 'admin' || $_SESSION['user']['role'] == 'petugas'): ?>
                            <a href="<?= $base_path . $_SESSION['user']['role'] ?>/dashboard.php" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="9" y1="9" x2="15" y2="9"></line>
                                    <line x1="9" y1="15" x2="15" y2="15"></line>
                                </svg>
                                Dashboard
                            </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?= $base_path ?>auth/logout.php" class="dropdown-item logout">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= $base_path ?>auth/login.php" class="btn-login">Masuk</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<style>
/* Navbar Styles */
.navbar {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    position: sticky;
    top: 0;
    z-index: 1000;
    border-bottom: 1px solid #f0f0f0;
}

.navbar-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 70px;
}

.navbar-brand {
    display: flex;
    align-items: center;
}

.navbar-brand a {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #ff5722;
    font-size: 1.5rem;
    font-weight: bold;
    transition: all 0.3s ease;
}

.navbar-brand a:hover {
    transform: translateY(-2px);
}

.brand-logo {
    height: 40px;
    width: auto;
    margin-right: 10px;
    transition: transform 0.3s ease;
}

.navbar-brand:hover .brand-logo {
    transform: scale(1.1);
}

.navbar-brand span {
    margin-left: 10px;
}

.navbar-search {
    flex: 1;
    max-width: 500px;
    margin: 0 40px;
}

.search-container {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border-radius: 25px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.search-container:focus-within {
    border-color: #ff5722;
    background: white;
    box-shadow: 0 0 0 4px rgba(255, 87, 34, 0.1);
}

.search-input {
    flex: 1;
    padding: 12px 20px;
    border: none;
    background: transparent;
    outline: none;
    font-size: 0.95rem;
    color: #333;
}

.search-input::placeholder {
    color: #999;
}

.search-btn {
    background: none;
    border: none;
    padding: 12px 15px;
    cursor: pointer;
    color: #666;
    transition: all 0.3s ease;
}

.search-btn:hover {
    color: #ff5722;
}

.navbar-actions {
    display: flex;
    align-items: center;
}

.action-buttons {
    display: flex;
    align-items: center;
    gap: 5px;
}

.action-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 2px solid transparent;
    color: #555;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    overflow: hidden;
}

.action-btn:hover {
    background: white;
    border-color: #ff5722;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 87, 34, 0.15);
}

.cart-badge, .wishlist-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff5722;
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 3px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    border: 2px solid white;
}

.navbar-dropdown {
    position: relative;
}

.profile-btn {
    overflow: hidden;
}

.btn-login {
    padding: 10px 20px;
    background: #ff5722;
    color: white;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-login:hover {
    background: #e64a19;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 10px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    min-width: 250px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

.navbar-dropdown:hover .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    padding: 20px;
    background: linear-gradient(135deg, #ff5722, #ff9800);
    color: white;
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 2px;
}

.user-role {
    font-size: 0.85rem;
    opacity: 0.9;
}

.dropdown-divider {
    height: 1px;
    background: #f0f0f0;
    margin: 5px 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: #ff5722;
}

.dropdown-item svg {
    width: 16px;
    height: 16px;
    transition: transform 0.2s ease;
}

.dropdown-item:hover svg {
    transform: scale(1.1);
}

.dropdown-item.logout {
    color: #dc3545;
}

.dropdown-item.logout:hover {
    background: #fff5f5;
    color: #dc3545;
}

/* Responsive Design */
@media (max-width: 768px) {
    .navbar-container {
        padding: 0 15px;
        height: 60px;
    }
    
    .navbar-search {
        display: none;
    }
    
    .navbar-brand a {
        font-size: 1.3rem;
    }
    
    .brand-logo {
        height: 35px;
    }
    
    .action-btn {
        width: 40px;
        height: 40px;
    }
}
</style>

<script>
// Wishlist functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize wishlist if not exists
    if (!localStorage.getItem('wishlist')) {
        localStorage.setItem('wishlist', JSON.stringify([]));
    }
    
    // Update wishlist badge
    function updateWishlistBadge() {
        const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
        const badge = document.querySelector('.wishlist-badge');
        
        if (wishlist.length > 0 && badge) {
            badge.textContent = wishlist.length;
            badge.style.display = 'block';
        } else if (badge) {
            badge.style.display = 'none';
        }
    }
    
    // Add to wishlist function
    window.addToWishlist = function(productId) {
        let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
        
        if (!wishlist.includes(productId)) {
            wishlist.push(productId);
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
            updateWishlistBadge();
            
            // Show notification
            showNotification('Produk ditambahkan ke wishlist!');
        } else {
            // Remove from wishlist
            wishlist = wishlist.filter(id => id !== productId);
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
            updateWishlistBadge();
            
            // Show notification
            showNotification('Produk dihapus dari wishlist!');
        }
    };
    
    // Check if product is in wishlist
    window.isInWishlist = function(productId) {
        const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
        return wishlist.includes(productId);
    };
    
    // Show notification
    function showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        
        // Style the notification
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.right = '20px';
        notification.style.backgroundColor = '#ff5722';
        notification.style.color = 'white';
        notification.style.padding = '12px 20px';
        notification.style.borderRadius = '8px';
        notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        notification.style.zIndex = '9999';
        notification.style.transform = 'translateY(100px)';
        notification.style.opacity = '0';
        notification.style.transition = 'all 0.3s ease';
        
        // Add to body
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.style.transform = 'translateY(0)';
            notification.style.opacity = '1';
        }, 100);
        
        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateY(100px)';
            notification.style.opacity = '0';
            
            // Remove from DOM after animation
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Update badge on page load
    updateWishlistBadge();
    
    // Handle wishlist button click
    const wishlistBtn = document.getElementById('wishlist-btn');
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Redirect to wishlist page
            window.location.href = 'wishlist.php';
        });
    }
});
</script>