<?php

global $conn;

function generateOrderNumber() {
    $t = time();
    $r = rand(1000, 9999);
    return "ORD-" . $t . "-" . $r;
}

function processOrder($data) {
    global $conn;
    
    $uid = $data['user_id'];
    $items = $data['items'];
    
    $sql = "SELECT * FROM users WHERE user_id = $uid";
    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) == 0) {
        return array('error' => 'User not found', 'code' => 400);
    }
    
    if (empty($items) || !is_array($items)) {
        return array('error' => 'Items required', 'code' => 400);
    }
    
    $on = generateOrderNumber();
    
    $sql = "INSERT INTO orders (order_number, user_id, status_id, total_amount) 
            VALUES ('$on', $uid, 1, 0)";
    $res = mysqli_query($conn, $sql);
    if (!$res) {
        return array('error' => 'Failed to create order: ' . mysqli_error($conn), 'code' => 500);
    }
    
    $oid = mysqli_insert_id($conn);
    
    $total = 0;
    foreach ($items as $item) {
        $pn = $item['product_number'];
        $qty = $item['quantity'];
        
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
        
        $sql = "INSERT INTO order_details (order_id, product_id, product_number, quantity, unit_price, subtotal) 
                VALUES ($oid, $pid, '$pn', $qty, $price, $sub)";
        mysqli_query($conn, $sql);
    }
    
    $sql = "UPDATE orders SET total_amount = $total WHERE order_id = $oid";
    mysqli_query($conn, $sql);
    
    return array('order_number' => $on, 'code' => 201);
}

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

function updateOrderItems($orderNumber, $items) {
    global $conn;
    
    $sql = "SELECT * FROM orders WHERE order_number = '$orderNumber'";
    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) == 0) {
        return array('error' => 'Order not found', 'code' => 404);
    }
    
    $order = mysqli_fetch_assoc($result);
    $oid = $order['order_id'];
    
    $sql = "DELETE FROM order_details WHERE order_id = $oid";
    mysqli_query($conn, $sql);
    
    $total = 0;
    foreach ($items as $item) {
        $pn = $item['product_number'];
        $qty = $item['quantity'];
        
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
        

        $sql = "INSERT INTO order_details (order_id, product_id, product_number, quantity, unit_price, subtotal) 
                VALUES ($oid, $pid, '$pn', $qty, $price, $sub)";
        mysqli_query($conn, $sql);
    }
    
    $sql = "UPDATE orders SET total_amount = $total WHERE order_id = $oid";
    mysqli_query($conn, $sql);
    
    return array('message' => 'Order updated', 'code' => 200);
}

function confirmOrderWithAddress($orderNumber, $address) {
    global $conn;
    
    $sql = "UPDATE orders SET shipping_address = '$address' 
            WHERE order_number = '$orderNumber'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        return array('error' => 'Failed to confirm order', 'code' => 500);
    }
    
    return array('message' => 'Order confirmed', 'code' => 200);
}

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

function searchOrders($search) {
    global $conn;
    
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

function bulkConfirmOrders($orderIds) {
    global $conn;
    
    $count = 0;
    foreach ($orderIds as $oid) {
        $sql = "UPDATE orders SET status_id = 2 WHERE order_id = $oid";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $count++;
        }
    }
    
    return $count;
}
