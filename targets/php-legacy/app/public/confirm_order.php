<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_helper.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : (isset($_POST['order_number']) ? $_POST['order_number'] : '');
$order_details = null;

if ($order_number && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $order_details = getOrderDetails($order_number);
    
    if (!$order_details) {
        $error = 'Order not found';
        $order_number = '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_number = $_POST['order_number'];
    $shipping_address = $_POST['shipping_address'];
    
    if (empty($shipping_address)) {
        $error = 'Shipping address is required';
    } else {
        // This function has SQL injection vulnerability by design
        $result = confirmOrderWithAddress($order_number, $shipping_address);
        if (isset($result['message'])) {
            $success = 'Order confirmed with shipping address! Your order will be processed soon.';
            $order_details = getOrderDetails($order_number);
        } else {
            $error = isset($result['error']) ? $result['error'] : 'Failed to confirm order';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirm Order</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 700px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; min-height: 100px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #218838; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .back { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back:hover { text-decoration: underline; }
        .order-info { background: #f8f9fa; padding: 15px; border-radius: 3px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Confirm Order with Shipping Address</h1>
        
        <?php if ($error): ?>
            <div class="error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$order_number || !$order_details): ?>
            <form method="GET">
                <div class="form-group">
                    <label for="order_number">Enter Order Number:</label>
                    <input type="text" id="order_number" name="order_number" required value="<?php echo $order_number; ?>">
                    <button type="submit" class="btn">Load Order</button>
                </div>
            </form>
        <?php else: ?>
            <div class="order-info">
                <strong>Order Number:</strong> <?php echo $order_number; ?><br>
                <strong>Status:</strong> <?php echo $order_details['order']['status_name']; ?><br>
                <strong>Total Amount:</strong> $<?php echo number_format($order_details['order']['total_amount'], 2); ?><br>
                <?php if ($order_details['order']['shipping_address']): ?>
                    <strong>Current Shipping Address:</strong> <?php echo nl2br($order_details['order']['shipping_address']); ?>
                <?php endif; ?>
            </div>
            
            <h3>Order Items:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_details['items'] as $item): ?>
                        <tr>
                            <td><?php echo $item['product_name']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td>$<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <form method="POST">
                <input type="hidden" name="order_number" value="<?php echo $order_number; ?>">
                
                <div class="form-group">
                    <label for="shipping_address">Shipping Address:</label>
                    <textarea id="shipping_address" name="shipping_address" required placeholder="Enter your full shipping address..."><?php echo $order_details['order']['shipping_address']; ?></textarea>
                </div>
                
                <button type="submit" class="btn">Confirm Order</button>
            </form>
        <?php endif; ?>
        
        <a href="index.php" class="back">&larr; Back to Home</a>
    </div>
</body>
</html>
