<?php

require_once __DIR__ . '/config.php';

global $conn;

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . " (Error code: " . mysqli_connect_errno() . ")");
}

mysqli_set_charset($conn, "utf8mb4");
