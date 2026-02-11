<?php
 $conn = mysqli_connect("localhost", "root", "", "bustore");

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>