document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const employeeId = urlParams.get('id');
    const form = document.getElementById('edit-employee-form');
    const errorMessages = document.getElementById('error-messages');
    
    // 获取员工信息并填充表单
    fetch(`api/get_employee.php?id=${employeeId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                errorMessages.textContent = data.message;
                errorMessages.style.display = 'block';
                return;
            }
            
            const employee = data.data;
            document.getElementById('employee_id').value = employee.id;
            document.getElementById('employee_number').value = employee.employee_number;
            document.getElementById('name').value = employee.name;
            document.getElementById('qq_number').value = employee.qq_number;
            document.getElementById('truckers_mp_id').value = employee.truckers_mp_id;
        })
        .catch(error => {
            errorMessages.textContent = '加载员工信息失败';
            errorMessages.style.display = 'block';
        });
    
    // 处理表单提交
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            id: document.getElementById('employee_id').value,
            employee_number: document.getElementById('employee_number').value,
            name: document.getElementById('name').value,
            qq_number: document.getElementById('qq_number').value,
            truckers_mp_id: document.getElementById('truckers_mp_id').value
        };
        
        fetch('api/update_employee.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                errorMessages.textContent = data.error;
                errorMessages.style.display = 'block';
                return;
            }
            
            window.location.href = 'index.php';
        })
        .catch(error => {
            errorMessages.textContent = '保存失败';
            errorMessages.style.display = 'block';
        });
    });
});