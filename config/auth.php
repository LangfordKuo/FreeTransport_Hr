<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['username']) && isset($_SESSION['role']);
}

function require_role($required_role) {
    if (!isset($_SESSION['role']) || 
        ($required_role == 'superadmin' && $_SESSION['role'] != 'superadmin') ||
        ($required_role == 'admin' && !in_array($_SESSION['role'], ['superadmin', 'admin']))
    ) {
        header('Location: /login.php');
        exit;
    }
}

function checkAuth() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// 检查是否为登录页面
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'login.php') {
    checkAuth();
}