<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑员工信息 - FreeTransport HR</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <nav class="nav-bar">
                <h1>编辑员工信息</h1>
                <div class="nav-links">
                    <a href="index.php" class="btn btn-primary">返回主页</a>
                </div>
            </nav>
            
            <form id="edit-employee-form" class="glass-card">
                <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">编辑员工信息</h2>
                <div id="error-messages" style="display: none; color: #ff6b6b; margin-bottom: 1rem;"></div>
                
                <input type="hidden" id="employee_id" name="employee_id">
                
                <div class="form-group">
                    <label class="form-label" for="employee_number">员工编号</label>
                    <input type="text" id="employee_number" name="employee_number" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="name">姓名</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="qq_number">QQ号码</label>
                    <input type="text" id="qq_number" name="qq_number" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="truckers_mp_id">TruckersMP ID</label>
                    <input type="text" id="truckers_mp_id" name="truckers_mp_id" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">保存修改</button>
            </form>
        </div>
    </div>
    
    <script src="js/edit.js"></script>
</body>
</html>