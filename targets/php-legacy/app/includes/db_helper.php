<?php
// ===========================
// Database Helper Functions (VULNERABLE)
// Uses string concatenation - SQL INJECTION RISK!
// ===========================

global $conn;

// Execute query with error exposure (VULNERABLE!)
function executeQuery($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        // Expose SQL error details (BAD PRACTICE!)
        die("SQL Error: " . mysqli_error($conn) . "\nQuery: " . $sql);
    }
    return $result;
}

// Get user by email (VULNERABLE to SQL injection!)
function getUserByEmail($email) {
    global $conn;
    // String concatenation - SQL INJECTION!
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = executeQuery($sql);
    return mysqli_fetch_assoc($result);
}

// Check if email exists
function emailExists($email) {
    $user = getUserByEmail($email);
    return $user !== null;
}

// Get product by product_number (VULNERABLE!)
function getProductByNumber($pn) {
    global $conn;
    $sql = "SELECT * FROM products WHERE product_number = '$pn'";
    $result = executeQuery($sql);
    return mysqli_fetch_assoc($result);
}

// Get order by order_number (VULNERABLE!)
function getOrderByNumber($on) {
    global $conn;
    $sql = "SELECT * FROM orders WHERE order_number = '$on'";
    $result = executeQuery($sql);
    return mysqli_fetch_assoc($result);
}

// Insert user (VULNERABLE!)
function insertUser($email, $fname, $lname, $phone, $pass, $isAdmin = 0) {
    global $conn;
    // Plaintext password + SQL injection vulnerability!
    $sql = "INSERT INTO users (email, first_name, last_name, phone, password, is_admin) 
            VALUES ('$email', '$fname', '$lname', '$phone', '$pass', $isAdmin)";
    return executeQuery($sql);
}

// Generic fetch all
function fetchAll($sql) {
    $result = executeQuery($sql);
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}
