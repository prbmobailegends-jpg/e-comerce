<?php
session_start();
include '../config/database.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $name = $_POST['name']; // Ambil nama dari form

    $q = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    $user = mysqli_fetch_assoc($q);

    if ($user) {
        $_SESSION['user'] = $user;
        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] == 'petugas') {
            header("Location: ../petugas/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    } else {
        $error = "Email, nama, atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bustore</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .auth-header h2 {
            color: #ff5722;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            text-align: center;
        }
        .btn-orange {
            background-color: #ff5722;
            color: white;
        }
        .btn-orange:hover {
            background-color: #e64a19;
        }
        .auth-footer {
            text-align: center;
            margin-top: 20px;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2>Login Bustore</h2>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" id="name" name="name" placeholder="Nama" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" name="login" class="btn btn-orange">Login</button>
        </form>
        
        <div class="auth-footer">
            <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
        </div>
    </div>
</body>
</html>