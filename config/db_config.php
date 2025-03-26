<?php
$db_config = [
    'host' => 'localhost',
    'username' => 'frt_hr',
    'password' => 'tEJz6RJachsfH9ep',
    'database' => 'frt_hr'
];

$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>