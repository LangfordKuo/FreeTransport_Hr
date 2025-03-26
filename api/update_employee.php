<?php
require_once '../config/db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['employee_number']) || !isset($data['name']) || 
    !isset($data['qq_number']) || !isset($data['truckers_mp_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// 检查员工编号是否已被其他员工使用
$stmt = $conn->prepare("SELECT id FROM employees WHERE employee_number = ? AND id != ? AND status = 'active'");
$stmt->bind_param('si', $data['employee_number'], $data['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => '员工编号已被使用']);
    exit;
}

// 检查QQ号码是否已被其他员工使用
$stmt = $conn->prepare("SELECT id FROM employees WHERE qq_number = ? AND id != ? AND status = 'active'");
$stmt->bind_param('si', $data['qq_number'], $data['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'QQ号码已被使用']);
    exit;
}

// 检查TruckersMP ID是否已被其他员工使用
$stmt = $conn->prepare("SELECT id FROM employees WHERE truckers_mp_id = ? AND id != ? AND status = 'active'");
$stmt->bind_param('si', $data['truckers_mp_id'], $data['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'TruckersMP ID已被使用']);
    exit;
}

// 更新员工信息
$stmt = $conn->prepare("UPDATE employees SET employee_number = ?, name = ?, qq_number = ?, truckers_mp_id = ? WHERE id = ?");
$stmt->bind_param('ssssi', $data['employee_number'], $data['name'], $data['qq_number'], $data['truckers_mp_id'], $data['id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => '更新失败']);
}