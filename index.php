<?php
require_once 'config/auth.php';
require_role(['user', 'admin', 'superadmin']);
require_once 'config/db_config.php';

// 获取所有在职员工
$active_employees = $conn->query("SELECT e.*, u.nickname as created_by_nickname 
FROM employees e 
LEFT JOIN users u ON e.created_by = u.id 
WHERE e.status = 'active' 
ORDER BY e.employee_number DESC");
if ($active_employees === false) {
    die("Error executing query: " . $conn->error);
}

// 获取所有离职员工（只显示最新的离职记录）
$inactive_employees = $conn->query("SELECT e.*, l.reason, l.change_date as leave_date, u.nickname as operated_by_nickname 
FROM employees e 
JOIN (
    SELECT employee_id, MAX(change_date) as latest_leave_date
    FROM employee_status_logs
    WHERE status_change = 'leave'
    GROUP BY employee_id
) latest ON e.id = latest.employee_id
JOIN employee_status_logs l ON e.id = l.employee_id AND l.change_date = latest.latest_leave_date
LEFT JOIN users u ON l.operated_by = u.id
WHERE e.status = 'inactive' AND l.status_change = 'leave' 
ORDER BY e.employee_number DESC");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <nav class="nav-bar">
                <h1><i class="fas fa-truck"></i> 自由运输人力管理系统</h1>
                <div class="nav-links">
                    <?php if ($_SESSION['role'] !== 'user'): ?>
                    <a href="add_employee.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> 员工录入
                    </a>
                    <?php endif; ?>
                    <a href="search.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> 员工查询
                    </a>
                    <?php if ($_SESSION['role'] === 'superadmin'): ?>
                    <a href="admin/manage_users.php" class="btn btn-primary">
                        <i class="fas fa-users-cog"></i> 用户管理
                    </a>
                    <?php endif; ?>
                    <a href="api/logout.php" class="btn btn-primary" style="background: #ff4757;">
                        <i class="fas fa-sign-out-alt"></i> 退出登录
                    </a>
                </div>
            </nav>
            
            <!-- 在职员工列表 -->
            <div class="glass-card">
                <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
                    <i class="fas fa-user-tie"></i> 在职员工
                </h2>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>员工编号</th>
                                <th>姓名</th>
                                <th>QQ号码</th>
                                <th>TruckersMP ID</th>
                                <th>入职时间</th>
                                <th>录入管理员</th>
                                <?php if ($_SESSION['role'] !== 'user'): ?>
                                <th>操作</th>
                                <?php endif; ?>
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
                                <td><?php echo htmlspecialchars($employee['created_by_nickname'] ?? '未知'); ?></td>
                                <?php if ($_SESSION['role'] !== 'user'): ?>
                                <td>
                                    <button class="btn btn-primary leave-button" data-employee-id="<?php echo $employee['id']; ?>">
                                        <i class="fas fa-user-minus"></i> 离职
                                    </button>
                                    <button class="btn btn-primary" onclick="window.location.href='edit.php?id=<?php echo $employee['id']; ?>'">
                                        <i class="fas fa-edit"></i> 编辑
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- 离职员工列表 -->
            <div class="glass-card">
                <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
                    <i class="fas fa-user-times"></i> 离职员工
                </h2>
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
                                <th>办理管理员</th>
                                <?php if ($_SESSION['role'] !== 'user'): ?>
                                <th>操作</th>
                                <?php endif; ?>
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
                                <td><?php echo htmlspecialchars($employee['operated_by_nickname'] ?? '未知'); ?></td>
                                <?php if ($_SESSION['role'] !== 'user'): ?>
                                <td>
                                    <button class="btn btn-primary restore-button" data-employee-id="<?php echo $employee['id']; ?>">
                                        <i class="fas fa-user-plus"></i> 恢复
                                    </button>
                                    <button class="btn btn-primary delete-button" style="background: #ff6b6b;" data-employee-id="<?php echo $employee['id']; ?>">
                                        <i class="fas fa-trash-alt"></i> 删除
                                    </button>
                                </td>
                                <?php endif; ?>
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