<?php
require_once '../config/db_config.php';
require_once '../config/auth.php';
require_role(['admin', 'superadmin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_number = trim($_POST['employee_number']);
    $name = trim($_POST['name']);
    $qq_number = trim($_POST['qq_number']);
    $truckers_mp_id = trim($_POST['truckers_mp_id']);
    $current_user_id = $_SESSION['user_id'];
    
    // 验证数据
    if (empty($name) || empty($qq_number) || empty($truckers_mp_id)) {
        echo json_encode(['success' => false, 'message' => '姓名、QQ号码和TruckersMP ID是必填项']);
        exit;
    }
    
    // 如果提供了员工编号，验证格式
    if (!empty($employee_number)) {
        if (!is_numeric($employee_number) || intval($employee_number) < 0) {
            echo json_encode([
                'success' => false,
                'message' => '员工编号必须为0或正整数'
            ]);
            exit;
        }
    }
    
    // 如果没有提供员工编号，自动分配一个最小的空闲编号
    if (empty($employee_number)) {
        // 获取所有已使用的编号
        $result = $conn->query("SELECT employee_number FROM employees ORDER BY employee_number");
        $used_numbers = [];
        while ($row = $result->fetch_assoc()) {
            $used_numbers[] = intval($row['employee_number']);
        }
        
        // 找到最小的空闲编号
        $number = 1;
        while (in_array($number, $used_numbers)) {
            $number++;
        }
        
        // 格式化编号：个位数和十位数补零，三位数及以上保持原样
        if ($number < 100) {
            $employee_number = sprintf("%03d", $number);
        } else {
            $employee_number = sprintf("%d", $number);
        }
    } else {
        // 如果手动输入了编号，也需要格式化
        $number = intval($employee_number);
        if ($number < 100) {
            $employee_number = sprintf("%03d", $number);
        }
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
    $stmt = $conn->prepare("INSERT INTO employees (employee_number, name, qq_number, truckers_mp_id, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $employee_number, $name, $qq_number, $truckers_mp_id, $current_user_id, $current_user_id);
    
    if ($stmt->execute()) {
        $employee_id = $stmt->insert_id;
        
        // 记录入职日志
        $stmt = $conn->prepare("INSERT INTO employee_status_logs (employee_id, status_change, operated_by) VALUES (?, 'join', ?)");
        $stmt->bind_param("ii", $employee_id, $current_user_id);
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