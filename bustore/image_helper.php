<?php
// bustore/image_helper.php

// Fungsi untuk menghasilkan stiker/ikon SVG
function generateStickerImage($productName, $category = 'default', $size = 60) {
    // Stiker/Emoji untuk berbagai kategori
    $stickers = [
        'Pakaian' => '👕',
        'Sepatu' => '👟',
        'Tas' => '👜',
        'Aksesoris' => '⌚',
        'default' => '📦'
    ];
    
    $sticker = $stickers[$category] ?? $stickers['default'];
    $color = getRandomColor();
    
    return "data:image/svg+xml;base64," . base64_encode(
        '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">' .
        '<rect width="' . $size . '" height="' . $size . '" fill="' . $color . '" rx="8"/>' .
        '<text x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="' . ($size * 0.5) . '" fill="white">' . $sticker . '</text>' .
        '<title>' . htmlspecialchars($productName) . '</title>' .
        '</svg>'
    );
}

function getRandomColor() {
    $colors = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
        '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2'
    ];
    return $colors[array_rand($colors)];
}

function getProductImage($image, $productName = '', $categoryId = null, $base_path = '') {
    // Jika ada produk nyata, coba dulu
    if (!empty($image)) {
        $categories = ['Pakaian', 'Sepatu', 'Tas', 'Aksesoris'];
        
        // Cek di folder kategori
        foreach ($categories as $category) {
            $path = $base_path . 'assets/img/products/' . $category . '/' . $image;
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Cek di folder products langsung
        $directPath = $base_path . 'assets/img/products/' . $image;
        if (file_exists($directPath)) {
            return $directPath;
        }
    }
    
    // Jika tidak ada gambar, gunakan stiker
    $categoryMap = [
        1 => 'Pakaian',
        2 => 'Sepatu', 
        3 => 'Tas',
        4 => 'Aksesoris'
    ];
    
    $category = $categoryMap[$categoryId] ?? 'default';
    return generateStickerImage($productName, $category);
}
?>