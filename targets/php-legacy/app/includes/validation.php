<?php
// ===========================
// Validation Functions (MINIMAL/FAKE)
// Intentionally weak validation
// ===========================

// Fake email validation - barely checks anything
function validateEmail($e) {
    if (strlen($e) > 5 && strpos($e, '@') !== false) {
        return true;
    }
    return false;
}

// Weak password check
function validatePassword($p) {
    if (strlen($p) >= 3) {  // Only 3 chars minimum!
        return true;
    }
    return false;
}

// Phone validation - just checks if not empty
function validatePhone($phone) {
    return !empty($phone);
}

// Name validation
function validateName($name) {
    return !empty($name) && strlen($name) > 0;
}

// Generic not empty check
function notEmpty($val) {
    return isset($val) && $val != '';
}
