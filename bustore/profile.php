<?php
session_start();
include 'config/database.php';
include 'partials/navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

 $user_id = $_SESSION['user']['id'];
 $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Bustore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Gaya CSS untuk halaman profil */
        .profile-container {
            max-width: 1000px;
            margin: 30px auto;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        .profile-sidebar {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .profile-pic-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }
        
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f0f0f0;
        }
        
        .profile-pic-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #ff5722;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .profile-email {
            color: #757575;
            margin-bottom: 20px;
        }
        
        .profile-content {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .profile-tabs {
            display: flex;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 25px;
        }
        
        .tab-btn {
            padding: 12px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #757575;
            position: relative;
            transition: all 0.2s;
        }
        
        .tab-btn.active {
            color: #ff5722;
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #ff5722;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #ff5722;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e64a19;
        }
        
        .chat-container {
            height: 400px;
            display: flex;
            flex-direction: column;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
        }
        
        .message.user {
            justify-content: flex-end;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
        }
        
        .message.user .message-bubble {
            background: #ff5722;
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message.admin .message-bubble {
            background: white;
            border: 1px solid #f0f0f0;
            border-bottom-left-radius: 4px;
        }
        
        .message-time {
            font-size: 0.7rem;
            color: #999;
            margin-top: 5px;
        }
        
        .chat-input {
            display: flex;
        }
        
        .chat-input input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 25px 0 0 25px;
            outline: none;
        }
        
        .chat-input button {
            padding: 12px 20px;
            background: #ff5722;
            color: white;
            border: none;
            border-radius: 0 25px 25px 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-pic-container">
                <img src="<?= !empty($user['profile_pic']) ? 'assets/img/profiles/' . $user['profile_pic'] : 'assets/img/icons/icon-user.png' ?>" alt="Profile" class="profile-pic">
                <label for="profile_pic" class="profile-pic-overlay">
                    <span>📷</span>
                </label>
                <input type="file" id="profile_pic" name="profile_pic" style="display: none;">
            </div>
            <h3 class="profile-name"><?= $user['name'] ?></h3>
            <p class="profile-email"><?= $user['email'] ?></p>
        </div>
        
        <div class="profile-content">
            <div class="profile-tabs">
                <button class="tab-btn active" onclick="openTab('info')">Informasi Akun</button>
                <button class="tab-btn" onclick="openTab('orders')">Pesanan Saya</button>
                <button class="tab-btn" onclick="openTab('chat')">Chat</button>
            </div>
            
            <!-- Informasi Akun Tab -->
            <div id="info" class="tab-content active">
                <h3>Informasi Akun</h3>
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" value="<?= $user['name'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= $user['email'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" value="<?= $user['phone'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Alamat</label>
                        <textarea id="address" name="address" rows="4"><?= $user['address'] ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
            
            <!-- Pesanan Tab -->
            <div id="orders" class="tab-content">
                <h3>Pesanan Saya</h3>
                <p>Daftar pesanan Anda akan ditampilkan di sini.</p>
            </div>
            
            <!-- Chat Tab -->
            <div id="chat" class="tab-content">
                <h3>Chat dengan Admin</h3>
                
                <div class="chat-container">
                    <div class="chat-messages" id="chat_messages">
                        <div class="message admin">
                            <div class="message-bubble">
                                Selamat datang di layanan chat Bustore! Ada yang bisa kami bantu?
                                <div class="message-time">10:00</div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-input">
                        <input type="text" id="message_input" placeholder="Ketik pesan Anda...">
                        <button onclick="sendMessage()">Kirim</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function openTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show the selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to the clicked button
            event.target.classList.add('active');
        }
        
        function sendMessage() {
            const messageInput = document.getElementById('message_input');
            const message = messageInput.value.trim();
            
            if (message === '') return;
            
            // Add user message to chat
            const chatMessages = document.getElementById('chat_messages');
            const now = new Date();
            const time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            
            const userMessage = document.createElement('div');
            userMessage.className = 'message user';
            userMessage.innerHTML = `
                <div class="message-bubble">
                    ${message}
                    <div class="message-time">${time}</div>
                </div>
            `;
            
            chatMessages.appendChild(userMessage);
            messageInput.value = '';
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Simulate admin response
            setTimeout(() => {
                const adminMessage = document.createElement('div');
                adminMessage.className = 'message admin';
                adminMessage.innerHTML = `
                    <div class="message-bubble">
                        Terima kasih atas pesan Anda. Tim kami akan segera merespons.
                        <div class="message-time">${time}</div>
                    </div>
                `;
                
                chatMessages.appendChild(adminMessage);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, 1000);
        }
        
        // Allow sending message with Enter key
        document.getElementById('message_input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>
</html>