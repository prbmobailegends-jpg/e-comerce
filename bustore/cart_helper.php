<?php
// bustore/helpers/cart_helper.php

function syncCartToDatabase($conn, $user_id) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return;
    }
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $check = mysqli_query($conn, "SELECT id FROM persistent_cart WHERE user_id=$user_id AND product_id=$product_id");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE persistent_cart SET quantity=$qty WHERE user_id=$user_id AND product_id=$product_id");
        } else {
            mysqli_query($conn, "INSERT INTO persistent_cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $qty)");
        }
    }
}

function loadCartFromDatabase($conn, $user_id) {
    $_SESSION['cart'] = [];
    $result = mysqli_query($conn, "SELECT product_id, quantity FROM persistent_cart WHERE user_id=$user_id");
    while ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['cart'][$row['product_id']] = $row['quantity'];
    }
}

function clearCartFromDatabase($conn, $user_id) {
    mysqli_query($conn, "DELETE FROM persistent_cart WHERE user_id=$user_id");
    unset($_SESSION['cart']);
}
?>