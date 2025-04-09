<?php
require_once '../config/db_config.php';
require_once '../config/auth.php';

header('Content-Type: application/json');

try {
    // 检查用户权限
    require_role(['admin', 'superadmin']);

    // 检查数据库连接
    if (!$conn) {
        throw new Exception('数据库连接失败');
    }

    // 获取所有已使用的编号
    $stmt = $conn->prepare("SELECT employee_number FROM employees ORDER BY employee_number");
    if (!$stmt) {
        throw new Exception('准备SQL语句失败: ' . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('执行SQL语句失败: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $used_numbers = [];
    while ($row = $result->fetch_assoc()) {
        $used_numbers[] = intval($row['employee_number']);
    }

    // 找到最小的未使用编号，从20开始
    $new_number = 20;
    while (in_array($new_number, $used_numbers)) {
        $new_number++;
    }

    // 补零到3位
    $new_number = sprintf("%03d", $new_number);

    echo json_encode(['success' => true, 'number' => $new_number]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 