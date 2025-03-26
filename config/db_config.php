<?php
$db_config = [
    'host' => 'localhost',
    'username' => '数据库用户名',
    'password' => '数据库密码',
    'database' => '数据库名'
];

$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>