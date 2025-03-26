<?php
require_once '../config/auth.php';
require_role('superadmin');
require_once '../config/db_config.php';

// 获取所有用户
$users = $conn->query("SELECT * FROM users ORDER BY role, username");
if ($users === false) {
    die("Error executing query: " . $conn->error);
}

// 处理添加用户请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $username = $conn->real_escape_string($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $conn->real_escape_string($_POST['role']);
        $nickname = $conn->real_escape_string($_POST['nickname']);
        
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role, nickname) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die("准备语句失败: " . $conn->error);
        }
        
        $stmt->bind_param("ssss", $username, $password, $role, $nickname);
        if (!$stmt->execute()) {
            die("执行失败: " . $stmt->error);
        }
        $stmt->close();
        
        header("Location: manage_users.php");
        exit;
    }
    
    if ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        
        // 检查要删除的用户是否是超级管理员
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && $user['role'] === 'superadmin') {
            die("错误：不允许删除超级管理员账户");
        }
        
        if (!$conn->query("DELETE FROM users WHERE id = $user_id")) {
            die("删除失败: " . $conn->error);
        }
        header("Location: manage_users.php");
        exit;
    }
    
    if ($_POST['action'] === 'update' && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        $role = $conn->real_escape_string($_POST['role']);
        $nickname = $conn->real_escape_string($_POST['nickname']);
        
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET role = ?, password_hash = ?, nickname = ? WHERE id = ?");
            if ($stmt === false) {
                die("准备语句失败: " . $conn->error);
            }
            $stmt->bind_param("sssi", $role, $password, $nickname, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET role = ?, nickname = ? WHERE id = ?");
            if ($stmt === false) {
                die("准备语句失败: " . $conn->error);
            }
            $stmt->bind_param("ssi", $role, $nickname, $user_id);
        }
        
        if (!$stmt->execute()) {
            die("执行失败: " . $stmt->error);
        }
        $stmt->close();
        
        header("Location: manage_users.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - 自由运输人力管理系统</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .form-col {
            flex: 1;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        .form-group .input-icon {
            position: relative;
        }
        
        .form-group .input-icon i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
        }
        
        .form-group .input-icon input {
            padding-left: 35px;
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            width: 90%;
            max-width: 500px;
            padding: 25px;
            border-radius: 12px;
            position: relative;
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .role-select {
            padding-left: 35px !important;
        }

        .role-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
        }

        .role-option i {
            width: 16px;
            text-align: center;
        }

        /* 修改角色标签样式 */
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .role-badge i {
            width: 16px;
            text-align: center;
        }
        
        .role-superadmin { background-color: #e74c3c; color: white; }
        .role-admin { background-color: #3498db; color: white; }
        .role-user { background-color: #2ecc71; color: white; }
        
        .table td {
            vertical-align: middle;
        }
        
        .validation-message {
            display: none;
            color: #e74c3c;
            font-size: 0.85em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <nav class="nav-bar">
                <h1><i class="fas fa-users-cog"></i> 用户管理</h1>
                <div class="nav-links">
                    <a href="../index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> 返回主页
                    </a>
                    <a href="../api/logout.php" class="btn btn-primary" style="background: #ff4757;">
                        <i class="fas fa-sign-out-alt"></i> 退出登录
                    </a>
                </div>
            </nav>
            
            <!-- 添加新用户表单 -->
            <div class="glass-card">
                <h2><i class="fas fa-user-plus"></i> 添加新用户</h2>
                <form method="POST" class="form" id="addUserForm">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="username">用户名</label>
                                <div class="input-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="username" name="username" required minlength="3" maxlength="50">
                                </div>
                                <div class="validation-message" id="username-validation"></div>
                            </div>
                            <div class="form-group">
                                <label for="nickname">昵称</label>
                                <div class="input-icon">
                                    <i class="fas fa-id-card"></i>
                                    <input type="text" id="nickname" name="nickname" placeholder="请输入昵称">
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="password">密码</label>
                                <div class="input-icon">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" required minlength="6">
                                </div>
                                <div class="validation-message" id="password-validation"></div>
                            </div>
                            <div class="form-group">
                                <label for="role">角色</label>
                                <div class="input-icon">
                                    <i class="fas fa-user-shield"></i>
                                    <select id="role" name="role" required class="role-select">
                                        <option value="user" class="role-option">
                                            <i class="fas fa-user"></i> 普通用户
                                        </option>
                                        <option value="admin" class="role-option">
                                            <i class="fas fa-user-cog"></i> 管理员
                                        </option>
                                        <option value="superadmin" class="role-option">
                                            <i class="fas fa-user-shield"></i> 超级管理员
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> 添加用户
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- 用户列表 -->
            <div class="glass-card">
                <h2><i class="fas fa-users"></i> 用户列表</h2>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>用户名</th>
                                <th>昵称</th>
                                <th>角色</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td>
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['nickname'] ?: '-'); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php 
                                        $roleIcons = [
                                            'superadmin' => '<i class="fas fa-user-shield"></i>',
                                            'admin' => '<i class="fas fa-user-cog"></i>',
                                            'user' => '<i class="fas fa-user"></i>'
                                        ];
                                        echo $roleIcons[$user['role']] . ' ' . htmlspecialchars($user['role']); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-primary edit-user-btn" 
                                            data-user-id="<?php echo $user['id']; ?>"
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            data-role="<?php echo htmlspecialchars($user['role']); ?>"
                                            data-nickname="<?php echo htmlspecialchars($user['nickname']); ?>">
                                        <i class="fas fa-edit"></i> 编辑
                                    </button>
                                    <?php if ($user['username'] !== $_SESSION['username'] && $user['role'] !== 'superadmin'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-primary" style="background: #ff6b6b;"
                                                onclick="return confirm('确定要删除此用户吗？')">
                                            <i class="fas fa-trash-alt"></i> 删除
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 编辑用户模态框 -->
    <div id="editUserModal" class="modal">
        <div class="modal-content glass-card">
            <button type="button" class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
            <h2><i class="fas fa-user-edit"></i> 编辑用户</h2>
            <form method="POST" class="form" id="editUserForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <label>用户名</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="edit_username" disabled>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_nickname">昵称</label>
                    <div class="input-icon">
                        <i class="fas fa-id-card"></i>
                        <input type="text" id="edit_nickname" name="nickname">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_password">新密码（留空保持不变）</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="edit_password" name="password" minlength="6">
                    </div>
                    <div class="validation-message" id="edit-password-validation"></div>
                </div>
                <div class="form-group">
                    <label for="edit_role">角色</label>
                    <div class="input-icon">
                        <i class="fas fa-user-shield"></i>
                        <select id="edit_role" name="role" required class="role-select">
                            <option value="user" class="role-option">
                                <i class="fas fa-user"></i> 普通用户
                            </option>
                            <option value="admin" class="role-option">
                                <i class="fas fa-user-cog"></i> 管理员
                            </option>
                            <option value="superadmin" class="role-option">
                                <i class="fas fa-user-shield"></i> 超级管理员
                            </option>
                        </select>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" style="background: #666;" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> 取消
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 保存更改
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // 表单验证
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        let isValid = true;
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        
        // 用户名验证
        if (username.value.length < 3) {
            document.getElementById('username-validation').textContent = '用户名至少需要3个字符';
            document.getElementById('username-validation').style.display = 'block';
            isValid = false;
        } else {
            document.getElementById('username-validation').style.display = 'none';
        }
        
        // 密码验证
        if (password.value.length < 6) {
            document.getElementById('password-validation').textContent = '密码至少需要6个字符';
            document.getElementById('password-validation').style.display = 'block';
            isValid = false;
        } else {
            document.getElementById('password-validation').style.display = 'none';
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });

    // 编辑用户表单验证
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        const password = document.getElementById('edit_password');
        
        if (password.value !== '' && password.value.length < 6) {
            document.getElementById('edit-password-validation').textContent = '密码至少需要6个字符';
            document.getElementById('edit-password-validation').style.display = 'block';
            e.preventDefault();
        } else {
            document.getElementById('edit-password-validation').style.display = 'none';
        }
    });

    // 打开编辑模态框
    function openEditModal(userId, username, role, nickname) {
        document.getElementById('editUserModal').style.display = 'flex';
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_nickname').value = nickname || '';
        document.getElementById('edit_password').value = '';
    }

    // 关闭编辑模态框
    function closeEditModal() {
        document.getElementById('editUserModal').style.display = 'none';
        document.getElementById('edit-password-validation').style.display = 'none';
    }

    // 为编辑按钮添加点击事件
    document.querySelectorAll('.edit-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const username = this.dataset.username;
            const role = this.dataset.role;
            const nickname = this.dataset.nickname || '';
            openEditModal(userId, username, role, nickname);
        });
    });

    // 点击模态框外部关闭
    window.onclick = function(event) {
        const modal = document.getElementById('editUserModal');
        if (event.target == modal) {
            closeEditModal();
        }
    }

    // 添加角色选项格式化代码
    function formatRoleOptions() {
        const roleIcons = {
            'user': '<i class="fas fa-user"></i>',
            'admin': '<i class="fas fa-user-cog"></i>',
            'superadmin': '<i class="fas fa-user-shield"></i>'
        };
        
        const roleNames = {
            'user': '普通用户',
            'admin': '管理员',
            'superadmin': '超级管理员'
        };
        
        const selects = document.querySelectorAll('select[name="role"]');
        selects.forEach(select => {
            const options = select.querySelectorAll('option');
            options.forEach(option => {
                const role = option.value;
                option.innerHTML = `<span class="role-option">${roleIcons[role]} ${roleNames[role]}</span>`;
            });
        });
    }

    // 页面加载完成后执行格式化
    </script>
</body>
</html> 