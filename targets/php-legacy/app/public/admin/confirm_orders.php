<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/db_helper.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';
$confirmed_count = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['order_ids']) || empty($_POST['order_ids'])) {
        $error = 'No orders selected';
    } else {
        $order_ids = $_POST['order_ids'];
        
        if (bulkConfirmOrders($order_ids)) {
            $confirmed_count = count($order_ids);
            $success = 'Successfully confirmed ' . $confirmed_count . ' order(s)';
        } else {
            $error = 'Failed to confirm orders';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Bulk Confirm Orders</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .back { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back:hover { text-decoration: underline; }
        .admin-badge { background: #dc3545; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <span class="admin-badge">ADMIN</span> Bulk Confirm Orders
        </h1>
        
        <?php if ($success): ?>
            <div class="success">
                <strong>Success!</strong><br>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error">
                <strong>Error!</strong><br>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success && !$error): ?>
            <p>This page is used to bulk confirm orders. Please select orders from the <a href="orders.php">Orders Management</a> page.</p>
        <?php endif; ?>
        
        <a href="orders.php" class="back">&larr; Back to Orders</a>
    </div>
</body>
</html>
