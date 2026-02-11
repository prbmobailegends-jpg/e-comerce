<?php
session_start();
include '../config/database.php';

if ($_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle blacklist action
if (isset($_GET['action']) && $_GET['action'] == 'blacklist' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Don't allow admin to blacklist themselves
    if ($user_id != $_SESSION['user']['id']) {
        mysqli_query($conn, "UPDATE users SET is_blacklisted = 1 WHERE id = $user_id");
        $_SESSION['success'] = "User berhasil di-blacklist";
    } else {
        $_SESSION['error'] = "Anda tidak bisa mem-blacklist diri sendiri";
    }
    
    header("Location: users.php");
    exit;
}

// Handle unblacklist action
if (isset($_GET['action']) && $_GET['action'] == 'unblacklist' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE users SET is_blacklisted = 0 WHERE id = $user_id");
    $_SESSION['success'] = "User berhasil di-unblacklist";
    
    header("Location: users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengguna - Bustore Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            font-size: 2rem;
            color: #333;
            position: relative;
            padding-bottom: 10px;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: #ff5722;
            border-radius: 2px;
        }
        
        .page-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #ff5722;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e64a19;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 87, 34, 0.2);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #555;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }
        
        .users-table-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th {
            background-color: #f9f9f9;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #f0f0f0;
            position: sticky;
            top: 0;
        }
        
        .users-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .users-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f0f0f0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-name {
            font-weight: 500;
            color: #333;
        }
        
        .user-email {
            font-size: 0.85rem;
            color: #666;
        }
        
        .role-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .role-admin {
            background: #ffebee;
            color: #d32f2f;
        }
        
        .role-petugas {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .role-customer {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .blacklisted-badge {
            background: #ffebee;
            color: #d32f2f;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-small {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-blacklist {
            background: #f44336;
            color: white;
        }
        
        .btn-blacklist:hover {
            background: #d32f2f;
        }
        
        .btn-unblacklist {
            background: #4caf50;
            color: white;
        }
        
        .btn-unblacklist:hover {
            background: #388e3c;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .users-table {
                font-size: 0.9rem;
            }
            
            .users-table th, .users-table td {
                padding: 10px 8px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Data Pengguna</h1>
            <div class="page-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="users-table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users_query = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
                    while ($user = mysqli_fetch_assoc($users_query)) {
                        $is_current_user = $user['id'] == $_SESSION['user']['id'];
                    ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <img src="<?= !empty($user['profile_pic']) ? '../assets/img/profiles/' . $user['profile_pic'] : '../assets/img/icons/icon-user.png' ?>" alt="Profile" class="user-avatar">
                                <div>
                                    <div class="user-name"><?= $user['name'] ?></div>
                                    <div class="user-email"><?= $user['email'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge role-<?= $user['role'] ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (isset($user['is_blacklisted']) && $user['is_blacklisted'] == 1): ?>
                                <span class="blacklisted-badge">Di-blacklist</span>
                            <?php else: ?>
                                <span style="color: #4caf50; font-weight: 500;">Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if (!$is_current_user): ?>
                                    <?php if (isset($user['is_blacklisted']) && $user['is_blacklisted'] == 1): ?>
                                        <a href="?action=unblacklist&id=<?= $user['id'] ?>" class="btn-small btn-unblacklist">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Unblacklist
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=blacklist&id=<?= $user['id'] ?>" class="btn-small btn-blacklist" onclick="return confirm('Apakah Anda yakin ingin mem-blacklist user ini?')">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                                <line x1="9" y1="9" x2="15" y2="15"></line>
                                            </svg>
                                            Blacklist
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>