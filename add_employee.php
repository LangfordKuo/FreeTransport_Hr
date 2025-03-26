<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>员工录入 - 自由运输人力管理系统</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <nav class="nav-bar">
                <h1>自由运输人力管理系统</h1>
                <div class="nav-links">
                    <a href="index.php" class="btn btn-primary">返回首页</a>
                    <a href="search.php" class="btn btn-primary">员工查询</a>
                </div>
            </nav>
            
            <!-- 添加员工表单 -->
            <form id="add-employee-form" class="glass-card">
                <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">添加新员工</h2>
                <div id="error-messages" style="display: none; color: #ff6b6b; margin-bottom: 1rem;"></div>
                
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
                
                <button type="submit" class="btn btn-primary">添加员工</button>
            </form>
        </div>
    </div>
    
    <script src="js/add_employee.js"></script>
</body>
</html>