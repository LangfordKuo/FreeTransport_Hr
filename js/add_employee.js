document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('add-employee-form');
    const errorMessages = document.getElementById('error-messages');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        errorMessages.style.display = 'none';
        errorMessages.textContent = '';

        // 获取表单数据
        const formData = {
            employee_number: document.getElementById('employee_number').value.trim(),
            name: document.getElementById('name').value.trim(),
            qq_number: document.getElementById('qq_number').value.trim(),
            truckers_mp_id: document.getElementById('truckers_mp_id').value.trim()
        };

        // 表单验证
        if (!formData.employee_number || !formData.name || !formData.qq_number || !formData.truckers_mp_id) {
            showError('所有字段都是必填的');
            return;
        }

        // 验证QQ号码格式
        if (!/^\d{5,11}$/.test(formData.qq_number)) {
            showError('QQ号码格式不正确');
            return;
        }

        try {
            const formDataObj = new FormData();
            for (const key in formData) {
                formDataObj.append(key, formData[key]);
            }

            const response = await fetch('api/add_employee.php', {
                method: 'POST',
                body: formDataObj
            });

            const data = await response.json();

            if (data.success) {
                // 添加成功，跳转到首页
                window.location.href = 'index.php';
            } else {
                showError(data.message || '添加员工失败');
            }
        } catch (error) {
            showError('系统错误，请稍后重试');
            console.error('Error:', error);
        }
    });

    function showError(message) {
        errorMessages.textContent = message;
        errorMessages.style.display = 'block';
    }
});