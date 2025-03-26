<?php
require_once 'config/db_config.php';

header('Content-Type: text/html; charset=utf-8');

function executeSQL($conn, $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ SQL执行成功: " . htmlspecialchars(substr($sql, 0, 50)) . "...</p>";
        return true;
    } else {
        $errorMsg = $conn->errno === 1050 
            ? "表已存在，无需重复创建" 
            : "错误原因: " . $conn->error;
        echo "<p style='color: orange;'>⚠ " . $errorMsg . "</p>";
        return false;
    }
}

// 检查数据库连接
if ($conn->connect_error) {
    die("<p style='color: red;'>数据库连接失败: " . $conn->connect_error . "</p>");
}

echo "<h2>开始安装数据库表...</h2>";

// 创建employees表
$sql_create_employees = "CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_number VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    qq_number VARCHAR(20) NOT NULL,
    truckers_mp_id VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// 创建employee_status_logs表
$sql_create_logs = "CREATE TABLE IF NOT EXISTS employee_status_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    status_change ENUM('join', 'leave') NOT NULL,
    reason TEXT,
    change_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// 创建users用户表
$sql_create_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nickname VARCHAR(50) DEFAULT NULL,
    role ENUM('superadmin','admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// 执行创建表操作
$success = true;
$success &= executeSQL($conn, $sql_create_employees);
$success &= executeSQL($conn, $sql_create_logs);
$success &= executeSQL($conn, $sql_create_users);

// 初始化管理员账户
$username = 'admin';
$password = 'admin';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // 检查用户是否存在
    $check_sql = "SELECT id FROM users WHERE username = '$username' LIMIT 1";
    $result = $conn->query($check_sql);

    if ($result->num_rows === 0) {
        $insert_sql = "INSERT INTO users (username, password_hash, role) 
                      VALUES ('$username', '$hashed_password', 'superadmin')";
        
        if ($conn->query($insert_sql)) {
            echo "<p style='color: green;'>✓ 管理员账户创建成功</p>";
            echo "<p>用户名: $username</p>";
            echo "<p>初始密码: $password</p>";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "<p style='color: orange;'>⚠ 管理员账户已存在，跳过创建</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 管理员账户创建失败: " . htmlspecialchars($e->getMessage()) . "</p>";
    $success = false;
}

if ($success) {
    echo "<h3 style='color: green;'>✓ 数据库表安装完成！</h3>";
} else {
    echo "<h3 style='color: red;'>✗ 数据库表安装过程中出现错误</h3>";
}

$conn->close();
?>