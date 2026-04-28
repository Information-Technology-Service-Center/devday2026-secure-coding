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

// Fetch all products
$query = "SELECT * FROM products ORDER BY product_name";
$result = mysqli_query($conn, $query);
$products = array();
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

if ($order_number) {
    $order_details = getOrderDetails($order_number);
    
    if (!$order_details) {
        $error = 'Order not found';
        $order_number = '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_number = $_POST['order_number'];
    $items = array();
    
    // Get updated products and quantities
    if (isset($_POST['product_number']) && isset($_POST['quantity'])) {
        $product_numbers = $_POST['product_number'];
        $quantities = $_POST['quantity'];
        
        for ($i = 0; $i < count($product_numbers); $i++) {
            if ($product_numbers[$i] && $quantities[$i] > 0) {
                $items[] = array(
                    'product_number' => $product_numbers[$i],
                    'quantity' => $quantities[$i]
                );
            }
        }
    }
    
    if (empty($items)) {
        $error = 'Please select at least one product with quantity';
    } else {
        $result = updateOrderItems($order_number, $items);
        if (isset($result['message'])) {
            $success = 'Order updated successfully!';
            $order_details = getOrderDetails($order_number);
        } else {
            $error = isset($result['error']) ? $result['error'] : 'Failed to update order';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Order</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="number"] { padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #0056b3; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .back { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back:hover { text-decoration: underline; }
        .order-info { background: #f8f9fa; padding: 15px; border-radius: 3px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Order</h1>
        
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
        
        <?php if (!$order_number): ?>
            <form method="GET">
                <div class="form-group">
                    <label for="order_number">Enter Order Number:</label>
                    <input type="text" id="order_number" name="order_number" required>
                    <button type="submit" class="btn">Load Order</button>
                </div>
            </form>
        <?php else: ?>
            <div class="order-info">
                <strong>Order Number:</strong> <?php echo $order_number; ?><br>
                <strong>Status:</strong> <?php echo $order_details['order']['status_name']; ?><br>
                <strong>Current Total:</strong> $<?php echo number_format($order_details['order']['total_amount'], 2); ?>
            </div>
            
            <form method="POST">
                <input type="hidden" name="order_number" value="<?php echo $order_number; ?>">
                
                <h3>Current Order Items:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Current Qty</th>
                            <th>Price</th>
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
                
                <h3>Update Order Items:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $index => $product): ?>
                            <?php
                                // Find current quantity for this product
                                $current_qty = 0;
                                foreach ($order_details['items'] as $item) {
                                    if ($item['product_number'] === $product['product_number']) {
                                        $current_qty = $item['quantity'];
                                        break;
                                    }
                                }
                            ?>
                            <tr>
                                <td><?php echo ($index + 1); ?></td>
                                <td>
                                    <strong><?php echo $product['product_name']; ?></strong><br>
                                    <small><?php echo $product['product_description']; ?></small>
                                    <input type="hidden" name="product_number[]" value="<?php echo $product['product_number']; ?>">
                                </td>
                                <td>
                                    <input type="number" name="quantity[]" min="0" value="<?php echo $current_qty; ?>">
                                </td>
                                <td>
                                    $<?php echo number_format($product['price'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <button type="submit" name="update_order" class="btn">Update Order</button>
            </form>
        <?php endif; ?>
        
        <a href="index.php" class="back">&larr; Back to Home</a>
    </div>
</body>
</html>
