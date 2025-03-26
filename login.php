<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 自由运输人力管理系统</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="glass-card" style="max-width: 400px; margin: 100px auto;">
            <h1 style="text-align: center; color: var(--primary-color); margin-bottom: 2rem;">自由运输人力管理系统</h1>
            
            <form id="login-form" method="post" action="api/login.php">
                <div id="error-message" style="display: none; color: #ff6b6b; margin-bottom: 1rem; text-align: center;"></div>
                
                <div class="form-group">
                    <label class="form-label" for="username">用户名</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">密码</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">登录</button>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('api/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'index.php';
            } else {
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = data.message || '登录失败，请检查用户名和密码';
                errorMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = '登录请求失败，请稍后重试';
            errorMessage.style.display = 'block';
        });
    });
    </script>
</body>
</html>