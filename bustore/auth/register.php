<?php
include '../config/database.php';

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    
    // Cek apakah email sudah terdaftar
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        mysqli_query($conn, "INSERT INTO users (name,email,password,role) VALUES ('$name','$email','$password','customer')");
        $success = "Registrasi berhasil. <a href='login.php'>Login</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bustore</title>
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
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header h2 {
            color: #ee4d2d;
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
            padding: 12px 20px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.2s;
            width: 100%;
            text-align: center;
        }
        
        .btn-green {
            background: #ee4d2d;
            color: white;
        }
        
        .btn-green:hover {
            background: #d6371f;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
        }
        
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #e7f9e7;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2>Register Customer</h2>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <?= $success ?>
            </div>
        <?php else: ?>
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
                
                <button type="submit" name="register" class="btn btn-green">Register</button>
            </form>
            
            <div class="auth-footer">
                <p>Sudah punya akun? <a href="login.php">Login</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>