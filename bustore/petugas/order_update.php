<?php
session_start();
include '../config/database.php';

// Check permissions
if ($_SESSION['user']['role'] != 'petugas') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';
    
    if ($id > 0 && !empty($status)) {
        // Validate status
        $valid_statuses = ['Menunggu Pembayaran', 'Dibayar', 'Diproses', 'Dikirim', 'Selesai'];
        
        if (in_array($status, $valid_statuses)) {
            // Update order status
            $update_query = "UPDATE orders SET status='$status' WHERE id=$id";
            
            if (mysqli_query($conn, $update_query)) {
                $_SESSION['success'] = "Status pesanan berhasil diperbarui";
            } else {
                $_SESSION['error'] = "Gagal memperbarui status: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['error'] = "Status tidak valid";
        }
    } else {
        $_SESSION['error'] = "Data tidak lengkap";
    }
    
    header("Location: order_detail.php?id=$id");
    exit;
}

// If not POST request, redirect to orders
header("Location: orders.php");
exit;
?>