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
$order_number = '';

// Fetch all products
$query = "SELECT * FROM products ORDER BY product_name";
$result = mysqli_query($conn, $query);
$products = array();
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = array();
    
    // Get selected products and quantities
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
        $order_data = array(
            'user_id' => $user_id,
            'items' => $items
        );
        
        $result = processOrder($order_data);
        
        if (isset($result['order_number'])) {
            $order_number = $result['order_number'];
            $success = 'Order created successfully! Order Number: ' . $order_number;
        } else {
            $error = isset($result['error']) ? $result['error'] : 'Failed to create order';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Order</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        input[type="number"] { width: 80px; padding: 5px; }
        .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #218838; }
        .btn-add { padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; margin: 10px 0; }
        .btn-add:hover { background: #0056b3; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .back { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back:hover { text-decoration: underline; }
        .item-row { margin-bottom: 10px; }
    </style>
    <script>
        var productCount = 1;
        function addProductRow() {
            var table = document.getElementById('productTable');
            var row = table.insertRow(-1);
            
            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);
            var cell4 = row.insertCell(3);
            
            cell1.innerHTML = productCount++;
            cell2.innerHTML = document.getElementById('productSelect').innerHTML;
            cell3.innerHTML = '<input type="number" name="quantity[]" min="1" value="1">';
            cell4.innerHTML = '<button type="button" onclick="removeRow(this)">Remove</button>';
        }
        
        function removeRow(btn) {
            var row = btn.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Create New Order</h1>
        
        <?php if ($error): ?>
            <div class="error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <?php echo $success; ?><br>
                <a href="update_order.php?order_number=<?php echo $order_number; ?>">Edit this order</a> | 
                <a href="confirm_order.php?order_number=<?php echo $order_number; ?>">Confirm with shipping address</a>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <h3>Select Products:</h3>
            
            <table id="productTable">
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
                        <tr>
                            <td><?php echo ($index + 1); ?></td>
                            <td>
                                <input type="checkbox" name="selected_<?php echo $index; ?>" value="1" id="prod_<?php echo $index; ?>">
                                <label for="prod_<?php echo $index; ?>">
                                    <strong><?php echo $product['product_name']; ?></strong><br>
                                    <small><?php echo $product['product_description']; ?></small>
                                </label>
                                <input type="hidden" name="product_number[]" value="<?php echo $product['product_number']; ?>">
                            </td>
                            <td>
                                <input type="number" name="quantity[]" min="0" value="0">
                            </td>
                            <td>
                                $<?php echo number_format($product['price'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <button type="submit" class="btn">Create Order</button>
        </form>
        
        <a href="index.php" class="back">&larr; Back to Home</a>
    </div>
    
    <div id="productSelect" style="display: none;">
        <select name="product_number[]">
            <option value="">-- Select Product --</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php echo $product['product_number']; ?>">
                    <?php echo $product['product_name']; ?> - $<?php echo number_format($product['price'], 2); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</body>
</html>
