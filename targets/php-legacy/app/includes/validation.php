<?php

function validateEmail($e) {
    if (strlen($e) > 5 && strpos($e, '@') !== false) {
        return true;
    }
    return false;
}

function validatePassword($p) {
    if (strlen($p) >= 3) {
        return true;
    }
    return false;
}

function validatePhone($phone) {
    return !empty($phone);
}

function validateName($name) {
    return !empty($name) && strlen($name) > 0;
}

function notEmpty($val) {
    return isset($val) && $val != '';
}
