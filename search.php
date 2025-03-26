<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>员工查询 - FreeTransport HR</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <nav class="nav-bar">
                <h1>员工查询</h1>
                <div class="nav-links">
                    <a href="index.php" class="btn btn-primary">返回主页</a>
                    <a href="add_employee.php" class="btn btn-primary">员工录入</a>
                </div>
            </nav>
            
            <div class="glass-card">
                <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">搜索员工</h2>
                <form id="searchForm" class="search-form">
                    <div class="form-group">
                        <select id="searchType" class="form-control">
                            <option value="employee_number">员工编号</option>
                            <option value="name">姓名</option>
                            <option value="qq_number">QQ号码</option>
                            <option value="truckers_mp_id">TruckersMP ID</option>
                        </select>
                        <input type="text" id="searchInput" class="form-control" placeholder="请输入搜索关键词" required>
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>
                </form>
                <div id="searchResults" class="search-results"></div>
            </div>
        </div>
    </div>
    <script src="js/search.js"></script>
</body>
</html>