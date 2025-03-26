document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    // 处理员工离职
    window.handleLeave = function(employeeId) {
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
                    document.getElementById('searchForm').dispatchEvent(new Event('submit'));
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert('处理离职请求时发生错误');
            });
        }
    };
    
    // 处理员工复职
    // 处理彻底删除员工
    window.handleDelete = function(employeeId) {
        if (confirm('确定要永久删除该员工记录吗？此操作不可恢复！')) {
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
                    document.getElementById('searchForm').dispatchEvent(new Event('submit'));
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('操作失败，请稍后重试');
            });
        }
    };

    window.handleRestore = function(employeeId) {
        if (confirm('确定要恢复该员工吗？')) {
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
                    document.getElementById('searchForm').dispatchEvent(new Event('submit'));
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('操作失败，请稍后重试');
            });
        }
    };

    searchForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const searchTerm = searchInput.value.trim();
        const searchType = document.getElementById('searchType').value;
        
        if (!searchTerm) {
            alert('请输入搜索关键词');
            return;
        }

        try {
            const response = await fetch(`api/search_employee.php?search_term=${encodeURIComponent(searchTerm)}&search_type=${encodeURIComponent(searchType)}`);
            const data = await response.json();

            if (data.success) {
                if (data.data.length > 0) {
                    const tableHTML = `
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>员工编号</th>
                                    <th>姓名</th>
                                    <th>QQ号码</th>
                                    <th>TruckersMP ID</th>
                                    <th>入职时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.data.map(employee => `
                                    <tr>
                                        <td>${employee.employee_number}</td>
                                        <td>${employee.name}</td>
                                        <td>${employee.qq_number}</td>
                                        <td>${employee.truckers_mp_id}</td>
                                        <td>${new Date(employee.join_date).toLocaleDateString()}</td>
                                        <td>
                                            <button class="btn btn-primary" onclick="window.location.href='edit.php?id=${employee.id}'">编辑</button>
                                            ${employee.status === 'active' ? 
                                                `<button class="btn btn-primary leave-button" onclick="handleLeave(${employee.id})">办理离职</button>` :
                                                `
                                                <button class="btn btn-primary restore-button" onclick="handleRestore(${employee.id})">复职</button>
                                                <button class="btn btn-danger delete-button" onclick="handleDelete(${employee.id})">彻底删除</button>
                                                `
                                            }
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                    searchResults.innerHTML = tableHTML;
                } else {
                    searchResults.innerHTML = '<p class="no-results">未找到匹配的员工</p>';
                }
            } else {
                searchResults.innerHTML = `<p class="error">${data.message}</p>`;
            }
        } catch (error) {
            console.error('搜索请求失败:', error);
            searchResults.innerHTML = '<p class="error">搜索过程中发生错误</p>';
        }
    });
});