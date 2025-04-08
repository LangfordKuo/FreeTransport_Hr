document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('add-employee-form');
    const errorMessages = document.getElementById('error-messages');
    const generateNumberBtn = document.getElementById('generate-number');
    const employeeNumberInput = document.getElementById('employee_number');

    function showError(message) {
        errorMessages.textContent = message;
        errorMessages.style.display = 'block';
    }

    // 处理生成编号按钮点击
    generateNumberBtn.addEventListener('click', async function() {
        try {
            const response = await fetch('api/generate_number.php');
            const data = await response.json();

            if (data.success) {
                employeeNumberInput.value = data.number;
            } else {
                showError(data.message || '生成编号失败');
            }
        } catch (error) {
            showError('系统错误，请稍后重试');
            console.error('Error:', error);
        }
    });

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
        if (!formData.name || !formData.qq_number || !formData.truckers_mp_id) {
            showError('姓名、QQ号码和TruckersMP ID是必填项');
            return;
        }

        // 验证QQ号码格式
        if (!/^\d{5,11}$/.test(formData.qq_number)) {
            showError('QQ号码格式不正确');
            return;
        }

        // 验证员工编号格式（如果填写了的话）
        if (formData.employee_number && (!/^\d+$/.test(formData.employee_number) || parseInt(formData.employee_number) < 0)) {
            showError('员工编号必须为0或0以上的整数');
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
});