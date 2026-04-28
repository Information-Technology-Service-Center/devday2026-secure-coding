<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_helper.php';
require_once __DIR__ . '/../includes/functions.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$is_admin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 0;
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Management System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .menu { margin: 20px 0; }
        .menu a { display: block; padding: 10px 15px; margin: 5px 0; background: #007bff; color: white; text-decoration: none; border-radius: 3px; }
        .menu a:hover { background: #0056b3; }
        .user-info { background: #e9ecef; padding: 10px; border-radius: 3px; margin-bottom: 20px; }
        .admin-badge { background: #dc3545; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Order Management System</h1>
        
        <?php if ($user_id): ?>
            <div class="user-info">
                <strong>Logged in as:</strong> <?php echo $email; ?>
                <?php if ($is_admin): ?>
                    <span class="admin-badge">ADMIN</span>
                <?php endif; ?>
                | <a href="logout.php">Logout</a>
            </div>
            
            <h2>User Menu</h2>
            <div class="menu">
                <a href="order.php">Create New Order</a>
                <a href="update_order.php">Edit Order</a>
                <a href="confirm_order.php">Confirm Order with Shipping Address</a>
            </div>
            
            <?php if ($is_admin): ?>
                <h2>Admin Menu</h2>
                <div class="menu">
                    <a href="admin/orders.php">Search/List All Orders</a>
                    <a href="admin/orders.php">Bulk Confirm Orders</a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <p>Please login or register to access the order management system.</p>
            
            <div class="menu">
                <a href="login.php">Login</a>
                <a href="register.php">Register New Account</a>
            </div>
        <?php endif; ?>
        
        <hr>
        <div class="menu">
            <a href="seed.php">Seed Database (Admin)</a>
        </div>
    </div>
</body>
</html>
