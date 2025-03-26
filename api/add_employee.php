<?php
require_once '../config/db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_number = trim($_POST['employee_number']);
    $name = trim($_POST['name']);
    $qq_number = trim($_POST['qq_number']);
    $truckers_mp_id = trim($_POST['truckers_mp_id']);
    
    // 验证数据
    if (empty($employee_number) || empty($name) || empty($qq_number) || empty($truckers_mp_id)) {
        echo json_encode(['success' => false, 'message' => '所有字段都必须填写']);
        exit;
    }
    
    // 检查员工编号是否已存在
    $stmt = $conn->prepare("SELECT id FROM employees WHERE employee_number = ?");
    $stmt->bind_param("s", $employee_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => '员工编号已存在']);
        exit;
    }
    
    // 添加新员工
    $stmt = $conn->prepare("INSERT INTO employees (employee_number, name, qq_number, truckers_mp_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $employee_number, $name, $qq_number, $truckers_mp_id);
    
    if ($stmt->execute()) {
        $employee_id = $stmt->insert_id;
        
        // 记录入职日志
        $stmt = $conn->prepare("INSERT INTO employee_status_logs (employee_id, status_change) VALUES (?, 'join')");
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => '员工添加成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '添加员工失败：' . $conn->error]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '不支持的请求方法']);
}

$conn->close();
?>