<?php
// ===========================
// Database Connection (VULNERABLE)
// Uses global variable and exposes errors
// ===========================

require_once __DIR__ . '/config.php';

// Global variable - bad practice!
global $conn;

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection and expose error details (VULNERABLE!)
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . " (Error code: " . mysqli_connect_errno() . ")");
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// No connection pooling, no proper error handling
// Just old-school mysqli with global variable
