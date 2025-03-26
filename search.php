<?php
require_once 'config/auth.php';
require_role(['user', 'admin', 'superadmin']);
require_once 'config/db_config.php';

// 处理搜索请求
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$result = null;

if (!empty($search)) {
    // 构建搜索查询
    $search_term = '%' . $conn->real_escape_string($search) . '%';
    $sql = "SELECT * FROM employees 
            WHERE employee_number LIKE ? 
            OR name LIKE ? 
            OR qq_number LIKE ? 
            OR truckers_mp_id LIKE ?
            ORDER BY status = 'active' DESC, join_date DESC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("准备语句失败: " . $conn->error);
    }
    
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    
    if (!$stmt->execute()) {
        die("执行查询失败: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>员工查询 - 自由运输人力管理系统</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/main.js"></script>
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <nav class="nav-bar">
                <h1><i class="fas fa-search"></i> 员工查询</h1>
                <div class="nav-links">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> 返回主页
                    </a>
                </div>
            </nav>

            <!-- 搜索表单 -->
            <div class="search-container">
                <form method="GET" class="search-box">
                    <div class="search-input-wrapper">
                        <input type="text" 
                               class="search-input" 
                               id="search" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search ?? ''); ?>"
                               placeholder="搜索员工编号、姓名、QQ号或TruckersMP ID..."
                               autocomplete="off">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i> 搜索
                    </button>
                </form>
                <div class="search-tips">
                    <i class="fas fa-info-circle"></i> 支持模糊搜索，输入任意关键词即可查询相关员工信息
                </div>
            </div>

            <?php if (isset($search) && !empty($search)): ?>
            <!-- 搜索结果 -->
            <div class="results-container">
                <div class="results-header">
                    <h2><i class="fas fa-list"></i> 搜索结果</h2>
                    <?php if ($result): ?>
                    <div class="results-count">
                        <i class="fas fa-users"></i> 共找到 <?php echo $result->num_rows; ?> 条记录
                    </div>
                    <?php endif; ?>
                </div>
                <div class="search-results" style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>员工编号</th>
                                <th>姓名</th>
                                <th>QQ号码</th>
                                <th>TruckersMP ID</th>
                                <th>状态</th>
                                <th>入职时间</th>
                                <?php if ($_SESSION['role'] !== 'user'): ?>
                                <th>操作</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($employee = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($employee['employee_number']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['qq_number']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['truckers_mp_id']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $employee['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                            <i class="fas <?php echo $employee['status'] === 'active' ? 'fa-user-check' : 'fa-user-times'; ?>"></i>
                                            <?php echo $employee['status'] === 'active' ? '在职' : '离职'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($employee['join_date'])); ?></td>
                                    <?php if ($_SESSION['role'] !== 'user'): ?>
                                    <td>
                                        <?php if ($employee['status'] === 'active'): ?>
                                        <button class="btn btn-primary" onclick="window.location.href='edit.php?id=<?php echo $employee['id']; ?>'">
                                            <i class="fas fa-edit"></i> 编辑
                                        </button>
                                        <button class="btn btn-primary leave-button" data-employee-id="<?php echo $employee['id']; ?>">
                                            <i class="fas fa-user-minus"></i> 离职
                                        </button>
                                        <?php else: ?>
                                        <button class="btn btn-primary restore-button" data-employee-id="<?php echo $employee['id']; ?>">
                                            <i class="fas fa-user-plus"></i> 恢复
                                        </button>
                                        <button class="btn btn-primary delete-button" style="background: #ff6b6b;" data-employee-id="<?php echo $employee['id']; ?>">
                                            <i class="fas fa-trash-alt"></i> 删除
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo $_SESSION['role'] !== 'user' ? '7' : '6'; ?>" style="text-align: center;">
                                        <div style="padding: 2rem;">
                                            <i class="fas fa-search" style="font-size: 2rem; color: rgba(255,255,255,0.3); margin-bottom: 1rem;"></i>
                                            <p style="color: rgba(255,255,255,0.6);">未找到匹配的员工记录</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 1000;
    }
    
    .modal-content {
        position: relative;
        margin: 15% auto;
        padding: 20px;
        width: 80%;
        max-width: 500px;
    }

    .modal-content textarea {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        border: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.1);
        color: white;
        min-height: 100px;
    }

    .modal-content textarea:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .btn {
        margin: 5px;
    }
    </style>

    <script>
    // 离职功能
    document.querySelectorAll('.leave-button').forEach(button => {
        button.addEventListener('click', () => {
            const employeeId = button.dataset.employeeId;
            document.getElementById('leaveEmployeeId').value = employeeId;
            document.getElementById('leaveReasonModal').style.display = 'block';
        });
    });

    function closeLeaveModal() {
        document.getElementById('leaveReasonModal').style.display = 'none';
    }

    // 点击模态框外部关闭
    window.onclick = function(event) {
        const modal = document.getElementById('leaveReasonModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // 处理离职表单提交
    document.getElementById('leaveForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/employee_status.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('离职处理成功');
                location.reload();
            } else {
                alert('操作失败: ' + result.message);
            }
        } catch (error) {
            alert('操作失败，请重试');
        }
    });

    // 恢复功能
    document.querySelectorAll('.restore-button').forEach(button => {
        button.addEventListener('click', async () => {
            if (!confirm('确定要恢复该员工吗？')) return;
            
            const employeeId = button.dataset.employeeId;
            const formData = new FormData();
            formData.append('action', 'restore');
            formData.append('employee_id', employeeId);
            
            try {
                const response = await fetch('api/employee_status.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('员工已恢复');
                    location.reload();
                } else {
                    alert('操作失败: ' + result.message);
                }
            } catch (error) {
                alert('操作失败，请重试');
            }
        });
    });

    // 删除功能
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', async () => {
            if (!confirm('确定要删除该员工吗？此操作不可恢复！')) return;
            
            const employeeId = button.dataset.employeeId;
            const formData = new FormData();
            formData.append('employee_id', employeeId);
            
            try {
                const response = await fetch('api/delete_employee.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('员工已删除');
                    location.reload();
                } else {
                    alert('操作失败: ' + result.message);
                }
            } catch (error) {
                alert('操作失败，请重试');
            }
        });
    });
    </script>
</body>
</html>