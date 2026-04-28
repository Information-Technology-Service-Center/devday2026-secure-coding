<?php

global $conn;

function executeQuery($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("SQL Error: " . mysqli_error($conn) . "\nQuery: " . $sql);
    }
    return $result;
}

function getUserByEmail($email) {
    global $conn;
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = executeQuery($sql);
    return mysqli_fetch_assoc($result);
}

function emailExists($email) {
    $user = getUserByEmail($email);
    return $user !== null;
}

function getProductByNumber($pn) {
    global $conn;
    $sql = "SELECT * FROM products WHERE product_number = '$pn'";
    $result = executeQuery($sql);
    return mysqli_fetch_assoc($result);
}

function getOrderByNumber($on) {
    global $conn;
    $sql = "SELECT * FROM orders WHERE order_number = '$on'";
    $result = executeQuery($sql);
    return mysqli_fetch_assoc($result);
}

function insertUser($email, $fname, $lname, $phone, $pass, $isAdmin = 0) {
    global $conn;
    $sql = "INSERT INTO users (email, first_name, last_name, phone, password, is_admin) 
            VALUES ('$email', '$fname', '$lname', '$phone', '$pass', $isAdmin)";
    return executeQuery($sql);
}

function fetchAll($sql) {
    $result = executeQuery($sql);
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}
