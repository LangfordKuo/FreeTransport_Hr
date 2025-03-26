<?php
require_once '../config/db_config.php';
require_once '../config/auth.php';
require_role(['admin', 'superadmin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = intval($_POST['employee_id']);
    $reason = trim($_POST['reason']);
    $current_user_id = $_SESSION['user_id'];
    
    if ($employee_id <= 0) {
        echo json_encode(['success' => false, 'message' => '无效的员工ID']);
        exit;
    }
    
    // 开始事务
    $conn->begin_transaction();
    
    try {
        // 更新员工状态
        $stmt = $conn->prepare("UPDATE employees SET status = 'inactive', updated_by = ? WHERE id = ? AND status = 'active'");
        $stmt->bind_param("ii", $current_user_id, $employee_id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('员工不存在或已离职');
        }
        
        // 记录离职日志
        $stmt = $conn->prepare("INSERT INTO employee_status_logs (employee_id, status_change, reason, operated_by) VALUES (?, 'leave', ?, ?)");
        $stmt->bind_param("isi", $employee_id, $reason, $current_user_id);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => '员工离职处理成功']);
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