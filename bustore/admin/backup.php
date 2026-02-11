<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

 $host = "localhost";
 $user = "root";
 $pass = "";
 $db   = "bustore";

 $backup_file = "backup_bustore_" . date("Y-m-d_H-i-s") . ".sql";

 $command = "mysqldump --user=$user --password=$pass --host=$host $db > $backup_file";

system($command);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Database - Bustore Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .backup-container {
            max-width: 600px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .backup-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .backup-container p {
            margin-bottom: 25px;
            font-size: 1.1rem;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            margin: 5px;
            transition: all 0.2s;
        }
        .btn-green {
            background: #ee4d2d;
            color: white;
        }
        .btn-green:hover {
            background: #d6371f;
        }
        .btn-gray {
            background: #f0f0f0;
            color: #555;
        }
        .btn-gray:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="backup-container">
        <h2>Backup Database</h2>
        <p>Backup database berhasil dibuat.</p>
        <a href="<?= $backup_file ?>" download class="btn btn-green">
            ⬇ Download Backup Database
        </a>
        <br><br>
        <a href="dashboard.php" class="btn btn-gray">⬅ Kembali ke Dashboard</a>
    </div>
</body>
</html>