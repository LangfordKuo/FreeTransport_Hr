<?php
require_once '../config/db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => '无效的员工ID']);
        exit;
    }
    
    try {
        $sql = "SELECT * FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $row['id'],
                    'employee_number' => $row['employee_number'],
                    'name' => $row['name'],
                    'qq_number' => $row['qq_number'],
                    'truckers_mp_id' => $row['truckers_mp_id'],
                    'status' => $row['status'],
                    'join_date' => $row['join_date']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => '未找到该员工']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '查询过程中发生错误']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '不支持的请求方法']);
}

$conn->close();
?>