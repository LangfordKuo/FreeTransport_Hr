<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['username']) && isset($_SESSION['role']);
}

function require_role($required_roles) {
    if (!isset($_SESSION['role'])) {
        header('Location: /login.php');
        exit;
    }

    // 如果传入的是字符串，转换为数组
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }

    // 检查用户角色是否在允许的角色列表中
    if (!in_array($_SESSION['role'], $required_roles)) {
        // 如果是AJAX请求，返回JSON错误
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            die(json_encode(['error' => '权限不足']));
        }
        // 否则重定向到首页
        header('Location: /index.php');
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