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

$search = isset($_GET['search']) ? $_GET['search'] : '';
$orders = array();

if ($search) {
    // Search orders by order number or user name
    $orders = searchOrders($search);
} else {
    // Get all orders
    $query = "SELECT o.*, u.email, u.first_name, u.last_name, s.status_name 
              FROM orders o 
              JOIN users u ON o.user_id = u.user_id 
              JOIN product_status s ON o.status_id = s.status_id 
              ORDER BY o.created_at DESC";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Orders Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .search-box { margin-bottom: 20px; }
        .search-box input[type="text"] { padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 3px; }
        .btn { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; margin-left: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-confirm { background: #28a745; }
        .btn-confirm:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .expandable { cursor: pointer; color: #007bff; }
        .expandable:hover { text-decoration: underline; }
        .details { display: none; background: #f8f9fa; padding: 15px; margin: 10px 0; }
        .details table { margin: 10px 0; }
        .back { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back:hover { text-decoration: underline; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-confirmed { color: #28a745; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }
        .admin-badge { background: #dc3545; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px; }
    </style>
    <script>
        function toggleDetails(orderId) {
            var details = document.getElementById('details_' + orderId);
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'block';
                loadOrderDetails(orderId);
            } else {
                details.style.display = 'none';
            }
        }
        
        function loadOrderDetails(orderId) {
            // Already loaded via PHP, just showing/hiding
        }
        
        function selectAll(checkbox) {
            var checkboxes = document.getElementsByName('order_ids[]');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = checkbox.checked;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>
            <span class="admin-badge">ADMIN</span> Orders Management
        </h1>
        
        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by order number or user name..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn">Search</button>
                <a href="orders.php" class="btn">Show All</a>
            </form>
        </div>
        
        <form method="POST" action="confirm_orders.php">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="selectAll(this)"></th>
                        <th>Order Number</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Created At</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">No orders found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="order_ids[]" value="<?php echo $order['order_id']; ?>">
                                </td>
                                <td><strong><?php echo $order['order_number']; ?></strong></td>
                                <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?><br>
                                    <small><?php echo $order['email']; ?></small>
                                </td>
                                <td>
                                    <span class="status-<?php echo strtolower($order['status_name']); ?>">
                                        <?php echo $order['status_name']; ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo $order['created_at']; ?></td>
                                <td>
                                    <span class="expandable" onclick="toggleDetails(<?php echo $order['order_id']; ?>)">
                                        Show Details
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7">
                                    <div id="details_<?php echo $order['order_id']; ?>" class="details">
                                        <?php
                                            $order_details = getOrderDetails($order['order_number']);
                                            if ($order_details):
                                        ?>
                                            <strong>Shipping Address:</strong><br>
                                            <?php echo $order_details['order']['shipping_address'] ? nl2br($order_details['order']['shipping_address']) : '<em>Not provided</em>'; ?>
                                            
                                            <h4>Order Items:</h4>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Product Number</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($order_details['items'] as $item): ?>
                                                        <tr>
                                                            <td><?php echo $item['product_name']; ?></td>
                                                            <td><?php echo $item['product_number']; ?></td>
                                                            <td><?php echo $item['quantity']; ?></td>
                                                            <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                                            <td>$<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if (!empty($orders)): ?>
                <button type="submit" class="btn btn-confirm">Bulk Confirm Selected Orders</button>
            <?php endif; ?>
        </form>
        
        <a href="../index.php" class="back">&larr; Back to Home</a>
    </div>
</body>
</html>
