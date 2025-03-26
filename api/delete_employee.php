<?php
require_once '../config/db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = intval($_POST['employee_id']);
    
    if ($employee_id <= 0) {
        echo json_encode(['success' => false, 'message' => '无效的员工ID']);
        exit;
    }
    
    // 开始事务
    $conn->begin_transaction();
    
    try {
        // 删除员工状态日志
        $stmt = $conn->prepare("DELETE FROM employee_status_logs WHERE employee_id = ?");
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        
        // 删除员工记录
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ? AND status = 'inactive'");
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('员工不存在或不是离职状态');
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => '员工记录已永久删除']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '不支持的请求方法']);
}

$conn->close();
?>