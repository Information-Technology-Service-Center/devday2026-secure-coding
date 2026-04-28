<?php
// ===========================
// Business Functions (SPAGHETTI STYLE)
// Long functions, mixed concerns, poor naming
// ===========================

global $conn;

// Generate order number - simple timestamp + random
function generateOrderNumber() {
    $t = time();
    $r = rand(1000, 9999);
    return "ORD-" . $t . "-" . $r;
}

// Process Order - LONG FUNCTION (150+ lines)
// Mixed validation, calculation, DB operations
function processOrder($data) {
    global $conn;
    
    // Extract data
    $uid = $data['user_id'];
    $items = $data['items'];
    
    // Validate user exists
    $sql = "SELECT * FROM users WHERE user_id = $uid";
    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) == 0) {
        return array('error' => 'User not found', 'code' => 400);
    }
    
    // Check items array
    if (empty($items) || !is_array($items)) {
        return array('error' => 'Items required', 'code' => 400);
    }
    
    // Generate order number
    $on = generateOrderNumber();
    
    // Create order record
    $sql = "INSERT INTO orders (order_number, user_id, status_id, total_amount) 
            VALUES ('$on', $uid, 1, 0)";
    $res = mysqli_query($conn, $sql);
    if (!$res) {
        return array('error' => 'Failed to create order: ' . mysqli_error($conn), 'code' => 500);
    }
    
    $oid = mysqli_insert_id($conn);
    
    // Process each item (NO TRANSACTION!)
    $total = 0;
    foreach ($items as $item) {
        $pn = $item['product_number'];
        $qty = $item['quantity'];
        
        // Get product
        $sql = "SELECT * FROM products WHERE product_number = '$pn'";
        $pres = mysqli_query($conn, $sql);
        if (!$pres || mysqli_num_rows($pres) == 0) {
            // Continue anyway - bad practice!
            continue;
        }
        
        $prod = mysqli_fetch_assoc($pres);
        $pid = $prod['product_id'];
        $price = $prod['price'];
        
        // Calculate subtotal
        $sub = $price * $qty;
        $total += $sub;
        
        // Insert order detail
        $sql = "INSERT INTO order_details (order_id, product_id, product_number, quantity, unit_price, subtotal) 
                VALUES ($oid, $pid, '$pn', $qty, $price, $sub)";
        mysqli_query($conn, $sql);
        // No error checking!
    }
    
    // Update order total
    $sql = "UPDATE orders SET total_amount = $total WHERE order_id = $oid";
    mysqli_query($conn, $sql);
    
    return array('order_number' => $on, 'code' => 201);
}

// Calculate total - redundant function but exists anyway
function calculateTotal($items) {
    global $conn;
    $t = 0;
    foreach ($items as $i) {
        $pn = $i['product_number'];
        $q = $i['quantity'];
        $sql = "SELECT price FROM products WHERE product_number = '$pn'";
        $r = mysqli_query($conn, $sql);
        if ($r && mysqli_num_rows($r) > 0) {
            $row = mysqli_fetch_assoc($r);
            $p = $row['price'];
            $t += $p * $q;
        }
    }
    return $t;
}

// Update order items - delete all then re-insert (INEFFICIENT!)
function updateOrderItems($orderNumber, $items) {
    global $conn;
    
    // Get order
    $sql = "SELECT * FROM orders WHERE order_number = '$orderNumber'";
    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) == 0) {
        return array('error' => 'Order not found', 'code' => 404);
    }
    
    $order = mysqli_fetch_assoc($result);
    $oid = $order['order_id'];
    
    // Delete all existing order details (NO TRANSACTION!)
    $sql = "DELETE FROM order_details WHERE order_id = $oid";
    mysqli_query($conn, $sql);
    
    // Re-insert items
    $total = 0;
    foreach ($items as $item) {
        $pn = $item['product_number'];
        $qty = $item['quantity'];
        
        // Get product info
        $sql = "SELECT * FROM products WHERE product_number = '$pn'";
        $pres = mysqli_query($conn, $sql);
        if (!$pres || mysqli_num_rows($pres) == 0) {
            continue;
        }
        
        $prod = mysqli_fetch_assoc($pres);
        $pid = $prod['product_id'];
        $price = $prod['price'];
        $sub = $price * $qty;
        $total += $sub;
        
        // Insert new detail
        $sql = "INSERT INTO order_details (order_id, product_id, product_number, quantity, unit_price, subtotal) 
                VALUES ($oid, $pid, '$pn', $qty, $price, $sub)";
        mysqli_query($conn, $sql);
    }
    
    // Update total
    $sql = "UPDATE orders SET total_amount = $total WHERE order_id = $oid";
    mysqli_query($conn, $sql);
    
    return array('message' => 'Order updated', 'code' => 200);
}

// Confirm order with shipping address (VULNERABLE!)
function confirmOrderWithAddress($orderNumber, $address) {
    global $conn;
    
    // Update with string concatenation (SQL INJECTION!)
    // Keep status as PENDING (status_id = 1)
    $sql = "UPDATE orders SET shipping_address = '$address' 
            WHERE order_number = '$orderNumber'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        return array('error' => 'Failed to confirm order', 'code' => 500);
    }
    
    return array('message' => 'Order confirmed', 'code' => 200);
}

// Get order details with items
function getOrderDetails($orderNumber) {
    global $conn;
    
    $sql = "SELECT o.*, ps.status_name FROM orders o 
            JOIN product_status ps ON o.status_id = ps.status_id 
            WHERE o.order_number = '$orderNumber'";
    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) == 0) {
        return null;
    }
    
    $order = mysqli_fetch_assoc($result);
    $oid = $order['order_id'];
    
    // Get order items with product details
    $sql = "SELECT od.*, p.product_name FROM order_details od
            JOIN products p ON od.product_id = p.product_id
            WHERE od.order_id = $oid";
    $items_result = mysqli_query($conn, $sql);
    $items = array();
    while ($item = mysqli_fetch_assoc($items_result)) {
        $items[] = $item;
    }
    
    return array('order' => $order, 'items' => $items);
}

// Search orders - VULNERABLE to SQL injection
function searchOrders($search) {
    global $conn;
    
    // SQL INJECTION vulnerability!
    $sql = "SELECT o.*, u.first_name, u.last_name, u.email, ps.status_name
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            JOIN product_status ps ON o.status_id = ps.status_id
            WHERE o.order_number LIKE '%$search%' 
               OR u.first_name LIKE '%$search%'
               OR u.last_name LIKE '%$search%'
            ORDER BY o.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    $orders = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $oid = $row['order_id'];
        
        // Get order details for each order
        $sql2 = "SELECT * FROM order_details WHERE order_id = $oid";
        $items_result = mysqli_query($conn, $sql2);
        $items = array();
        while ($item = mysqli_fetch_assoc($items_result)) {
            $items[] = $item;
        }
        
        $row['order_details'] = $items;
        $orders[] = $row;
    }
    
    return $orders;
}

// Bulk update order status (NO TRANSACTION!)
function bulkConfirmOrders($orderIds) {
    global $conn;
    
    $count = 0;
    foreach ($orderIds as $oid) {
        // No validation, no transaction
        $sql = "UPDATE orders SET status_id = 2 WHERE order_id = $oid";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $count++;
        }
    }
    
    return $count;
}
