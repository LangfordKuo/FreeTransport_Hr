document.addEventListener('DOMContentLoaded', function() {
    // 表单验证函数
    function validateForm(formData) {
        const errors = [];
        
        // 验证员工编号
        if (!formData.get('employee_number').trim()) {
            errors.push('员工编号不能为空');
        }
        
        // 验证姓名
        if (!formData.get('name').trim()) {
            errors.push('姓名不能为空');
        }
        
        // 验证QQ号码
        const qqNumber = formData.get('qq_number').trim();
        if (!qqNumber || !/^\d{5,11}$/.test(qqNumber)) {
            errors.push('请输入有效的QQ号码');
        }
        
        // 验证TruckersMP ID
        if (!formData.get('truckers_mp_id').trim()) {
            errors.push('TruckersMP ID不能为空');
        }
        
        return errors;
    }
    
    // 显示错误信息
    function showErrors(errors) {
        const errorContainer = document.getElementById('error-messages');
        errorContainer.innerHTML = '';
        if (errors.length > 0) {
            const ul = document.createElement('ul');
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                ul.appendChild(li);
            });
            errorContainer.appendChild(ul);
            errorContainer.style.display = 'block';
        } else {
            errorContainer.style.display = 'none';
        }
    }
    
    // 处理恢复员工
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('restore-button')) {
            if (confirm('确定要恢复该员工吗？')) {
                const employeeId = e.target.dataset.employeeId;
                fetch('api/restore_employee.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `employee_id=${employeeId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('操作失败，请稍后重试');
                });
            }
        }
    });

    // 处理删除员工
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-button')) {
            if (confirm('确定要永久删除该员工记录吗？此操作不可恢复！')) {
                const employeeId = e.target.dataset.employeeId;
                fetch('api/delete_employee.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `employee_id=${employeeId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('操作失败，请稍后重试');
                });
            }
        }
    });

    // 处理表单提交
    const addEmployeeForm = document.getElementById('add-employee-form');
    if (addEmployeeForm) {
        addEmployeeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const errors = validateForm(formData);
            
            if (errors.length === 0) {
                fetch('api/add_employee.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showErrors([data.message]);
                    }
                })
                .catch(error => {
                    showErrors(['提交数据时发生错误']);
                });
            } else {
                showErrors(errors);
            }
        });
    }
    
    // 处理员工离职
    const leaveButtons = document.querySelectorAll('.leave-button');
    leaveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const employeeId = this.dataset.employeeId;
            const reason = prompt('请输入离职原因：');
            
            if (reason !== null) {
                const formData = new FormData();
                formData.append('employee_id', employeeId);
                formData.append('reason', reason);
                
                fetch('api/leave_employee.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    alert('处理离职请求时发生错误');
                });
            }
        });
    });
});