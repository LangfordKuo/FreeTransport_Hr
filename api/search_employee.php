<?php
require_once '../config/db_config.php';
require_once '../config/auth.php';
require_role(['user', 'admin', 'superadmin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search_term = trim($_GET['search_term'] ?? '');
    
    if (empty($search_term)) {
        echo json_encode(['success' => false, 'message' => '请输入搜索关键词']);
        exit;
    }
    
    try {
        $search_type = $_GET['search_type'] ?? 'employee_number';
        $search_pattern = "%{$search_term}%";
        
        // 根据搜索类型构建SQL查询语句
        $sql = "SELECT * FROM employees WHERE ";
        switch ($search_type) {
            case 'name':
                $sql .= "name LIKE ?";
                break;
            case 'qq_number':
                $sql .= "qq_number LIKE ?";
                break;
            case 'truckers_mp_id':
                $sql .= "truckers_mp_id LIKE ?";
                break;
            default:
                $sql .= "employee_number LIKE ?";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $search_pattern);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $employees = [];
        
        while ($row = $result->fetch_assoc()) {
            $employees[] = [
                'id' => $row['id'],
                'employee_number' => $row['employee_number'],
                'name' => $row['name'],
                'qq_number' => $row['qq_number'],
                'truckers_mp_id' => $row['truckers_mp_id'],
                'status' => $row['status'],
                'join_date' => $row['join_date']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $employees,
            'message' => count($employees) > 0 ? '查询成功' : '未找到匹配的员工'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '查询过程中发生错误']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '不支持的请求方法']);
}

$conn->close();
?>