<?php
require_once 'config/db_config.php';

// 获取所有在职员工
$active_employees = $conn->query("SELECT * FROM employees WHERE status = 'active' ORDER BY join_date DESC");
if ($active_employees === false) {
    die("Error executing query: " . $conn->error);
}

// 获取所有离职员工（只显示最新的离职记录）
$inactive_employees = $conn->query("SELECT e.*, l.reason, l.change_date as leave_date 
FROM employees e 
JOIN (
    SELECT employee_id, MAX(change_date) as latest_leave_date
    FROM employee_status_logs
    WHERE status_change = 'leave'
    GROUP BY employee_id
) latest ON e.id = latest.employee_id
JOIN employee_status_logs l ON e.id = l.employee_id AND l.change_date = latest.latest_leave_date
WHERE e.status = 'inactive' AND l.status_change = 'leave' 
ORDER BY l.change_date DESC");
if ($inactive_employees === false) {
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>自由运输人力管理系统</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <nav class="nav-bar">
                <h1>自由运输人力管理系统</h1>
                <div class="nav-links">
                    <a href="add_employee.php" class="btn btn-primary">员工录入</a>
                    <a href="search.php" class="btn btn-primary">员工查询</a>
                </div>
            </nav>
            
            <!-- 在职员工列表 -->
            <div class="glass-card">
                <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">在职员工</h2>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>员工编号</th>
                                <th>姓名</th>
                                <th>QQ号码</th>
                                <th>TruckersMP ID</th>
                                <th>入职时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($employee = $active_employees->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($employee['employee_number']); ?></td>
                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                <td><?php echo htmlspecialchars($employee['qq_number']); ?></td>
                                <td><?php echo htmlspecialchars($employee['truckers_mp_id']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($employee['join_date'])); ?></td>
                                <td>
                                    <button class="btn btn-primary leave-button" data-employee-id="<?php echo $employee['id']; ?>">办理离职</button>
                                    <button class="btn btn-primary" onclick="window.location.href='edit.php?id=<?php echo $employee['id']; ?>'">编辑</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- 离职员工列表 -->
            <div class="glass-card">
                <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">离职员工</h2>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>员工编号</th>
                                <th>姓名</th>
                                <th>QQ号码</th>
                                <th>TruckersMP ID</th>
                                <th>离职时间</th>
                                <th>离职原因</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($employee = $inactive_employees->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($employee['employee_number']); ?></td>
                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                <td><?php echo htmlspecialchars($employee['qq_number']); ?></td>
                                <td><?php echo htmlspecialchars($employee['truckers_mp_id']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($employee['leave_date'])); ?></td>
                                <td><?php echo htmlspecialchars($employee['reason']); ?></td>
                                <td>
                                    <button class="btn btn-primary restore-button" data-employee-id="<?php echo $employee['id']; ?>">恢复</button>
                                    <button class="btn btn-primary delete-button" style="background: #ff6b6b;" data-employee-id="<?php echo $employee['id']; ?>">删除</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>