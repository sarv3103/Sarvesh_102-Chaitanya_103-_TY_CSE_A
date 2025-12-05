// Admin dashboard - includes all staff features plus user management
// Import staff dashboard functionality and extend it

let currentUser = null;
let currentNoticeId = null;
let currentEditNoticeId = null;
let allClasses = [];

window.addEventListener('DOMContentLoaded', async () => {
    await checkAuth();
    await loadNotices();
    await loadClasses();
});

async function checkAuth() {
    try {
        const response = await fetch('api/check-session.php');
        const data = await response.json();
        
        if (!data.success || data.data.role !== 'Admin') {
            window.location.href = 'index.html';
            return;
        }
        
        currentUser = data.data;
        document.getElementById('userName').textContent = currentUser.name + ' (' + currentUser.role + ')';
    } catch (error) {
        window.location.href = 'index.html';
    }
}

// Notice functions (same as staff)
async function loadNotices() {
    try {
        const response = await fetch('api/notices/list-with-counts.php');
        const data = await response.json();
        
        if (data.success) {
            displayNotices(data.data);
        }
    } catch (error) {
        console.error('Error loading notices:', error);
    }
}

function displayNotices(notices) {
    const container = document.getElementById('noticesList');
    
    if (notices.length === 0) {
        container.innerHTML = '<p>No notices available.</p>';
        return;
    }
    
    container.innerHTML = notices.map(notice => {
        // Determine badge based on target_info
        let badge = '';
        if (notice.target_info && notice.target_info.label) {
            const badgeClass = notice.target_info.type === 'staff_only' ? 'badge-warning' : 
                              notice.target_info.type === 'everyone' ? 'badge-info' :
                              notice.target_info.type === 'all_students' ? 'badge-success' :
                              'badge-primary';
            badge = `<span class="badge ${badgeClass}">${notice.target_info.label}</span>`;
        }
        
        return `
            <div class="notice-card" onclick="viewNotice(${notice.notice_id})">
                <h3>${notice.title}</h3>
                <div class="meta">
                    <strong>From:</strong> ${notice.sender_name} | 
                    <strong>Date:</strong> ${new Date(notice.created_at).toLocaleString()}
                    ${badge}
                </div>
                <div class="content">${notice.content.substring(0, 200)}${notice.content.length > 200 ? '...' : ''}</div>
                <div class="notice-stats">
                    <span>👁️ ${notice.view_count} views</span>
                    <span>💬 ${notice.comment_count} comments</span>
                </div>
            </div>
        `;
    }).join('');
}

async function viewNotice(noticeId) {
    currentNoticeId = noticeId;
    
    try {
        const response = await fetch(`api/notices/get-detail.php?notice_id=${noticeId}`);
        const data = await response.json();
        
        if (data.success) {
            displayNoticeDetail(data.data);
            displayAttachments(data.data.attachments);
            displayViewers(data.data.viewers);
            await loadComments(noticeId);
            document.getElementById('noticeModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading notice:', error);
    }
}

function displayNoticeDetail(data) {
    const notice = data.notice;
    
    const editButton = data.can_edit ? 
        `<button onclick="openEditModal(${notice.notice_id}, '${escapeHtml(notice.title)}', '${escapeHtml(notice.content)}')" class="btn btn-primary">Edit Notice</button>` : '';
    
    const deleteButton = data.can_delete ? 
        `<button onclick="deleteNotice(${notice.notice_id})" class="btn btn-danger">Delete Notice</button>` : '';
    
    document.getElementById('noticeDetail').innerHTML = `
        <h2>${notice.title}</h2>
        <div class="meta">
            <strong>From:</strong> ${notice.sender_name} (${notice.sender_email})<br>
            <strong>Date:</strong> ${new Date(notice.created_at).toLocaleString()}<br>
            ${notice.updated_at !== notice.created_at ? `<strong>Updated:</strong> ${new Date(notice.updated_at).toLocaleString()}<br>` : ''}
            <strong>Views:</strong> 👁️ ${data.view_count} | <strong>Comments:</strong> 💬 ${data.comment_count}
            ${notice.is_staff_only ? '<span class="badge badge-warning">Staff Only</span>' : ''}
        </div>
        <div class="content" style="margin: 20px 0; line-height: 1.8; white-space: pre-wrap;">${notice.content}</div>
        <div class="notice-actions">
            ${editButton}
            ${deleteButton}
        </div>
    `;
}

function displayAttachments(attachments) {
    const container = document.getElementById('attachmentsSection');
    
    if (attachments.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    container.innerHTML = `
        <h3>📎 Attachments</h3>
        <div class="attachments-list">
            ${attachments.map(att => `
                <div class="attachment-item">
                    <a href="${att.file_path}" target="_blank" download="${att.file_name}">
                        ${getFileIcon(att.file_type)} ${att.file_name}
                    </a>
                    <span class="file-size">(${formatFileSize(att.file_size)})</span>
                </div>
            `).join('')}
        </div>
    `;
}

function displayViewers(viewers) {
    const container = document.getElementById('viewsSection');
    
    if (viewers.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    container.innerHTML = `
        <div class="viewers-section">
            <button onclick="toggleViewersList()" class="btn btn-secondary" id="viewersBtn">
                👁️ View Who Saw This (${viewers.length} ${viewers.length === 1 ? 'person' : 'people'})
            </button>
            <div id="viewersList" class="viewers-list" style="display: none; margin-top: 15px;">
                ${viewers.map(viewer => `
                    <div class="viewer-item">
                        <strong>${viewer.name}</strong> (${viewer.role_name}) - ${new Date(viewer.viewed_at).toLocaleString()}
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function toggleViewersList() {
    const list = document.getElementById('viewersList');
    const btn = document.getElementById('viewersBtn');
    
    if (list.style.display === 'none') {
        list.style.display = 'block';
        btn.textContent = btn.textContent.replace('View', 'Hide');
    } else {
        list.style.display = 'none';
        btn.textContent = btn.textContent.replace('Hide', 'View');
    }
}

function getFileIcon(fileType) {
    if (fileType.includes('pdf')) return '📄';
    if (fileType.includes('image')) return '🖼️';
    return '📎';
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

function escapeHtml(text) {
    return text.replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, '\\n');
}

async function loadComments(noticeId) {
    try {
        const response = await fetch(`api/comments/list.php?notice_id=${noticeId}`);
        const data = await response.json();
        
        if (data.success) {
            displayComments(data.data);
        }
    } catch (error) {
        console.error('Error loading comments:', error);
    }
}

function displayComments(comments) {
    const container = document.getElementById('commentsList');
    
    if (comments.length === 0) {
        container.innerHTML = '<p>No comments yet. Be the first to comment!</p>';
        return;
    }
    
    container.innerHTML = comments.map(comment => `
        <div class="comment">
            <div class="author">${comment.user_name} (${comment.role_name})</div>
            <div class="text">${comment.comment_text}</div>
            <div class="comment-footer">
                <span class="time">${new Date(comment.created_at).toLocaleString()}</span>
                ${comment.updated_at !== comment.created_at ? '<span class="edited">(edited)</span>' : ''}
                ${comment.can_edit ? `<button onclick="editComment(${comment.comment_id}, '${escapeHtml(comment.comment_text)}')" class="btn-link">Edit</button>` : ''}
                ${comment.can_delete ? `<button onclick="deleteComment(${comment.comment_id})" class="btn-link">Delete</button>` : ''}
            </div>
        </div>
    `).join('');
}

document.getElementById('commentForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const commentText = document.getElementById('commentText').value;
    
    try {
        const response = await fetch('api/comments/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notice_id: currentNoticeId,
                comment_text: commentText
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('commentText').value = '';
            await loadComments(currentNoticeId);
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error posting comment');
    }
});

async function editComment(commentId, currentText) {
    const newText = prompt('Edit your comment:', currentText);
    
    if (newText === null || newText.trim() === '') return;
    
    try {
        const response = await fetch('api/comments/edit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                comment_id: commentId,
                comment_text: newText
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            await loadComments(currentNoticeId);
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error editing comment');
    }
}

async function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }
    
    try {
        const response = await fetch('api/comments/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ comment_id: commentId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            await loadComments(currentNoticeId);
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error deleting comment');
    }
}

async function deleteNotice(noticeId) {
    if (!confirm('Are you sure you want to delete this notice?')) {
        return;
    }
    
    try {
        const response = await fetch('api/notices/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notice_id: noticeId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Notice deleted successfully');
            closeNoticeModal();
            await loadNotices();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error deleting notice');
    }
}

function openEditModal(noticeId, title, content) {
    currentEditNoticeId = noticeId;
    document.getElementById('editTitle').value = title;
    document.getElementById('editContent').value = content;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    currentEditNoticeId = null;
}

document.getElementById('editNoticeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const title = document.getElementById('editTitle').value;
    const content = document.getElementById('editContent').value;
    
    try {
        const response = await fetch('api/notices/edit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notice_id: currentEditNoticeId,
                title: title,
                content: content
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Notice updated successfully');
            closeEditModal();
            closeNoticeModal();
            await loadNotices();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error updating notice');
    }
});

function closeNoticeModal() {
    document.getElementById('noticeModal').style.display = 'none';
    currentNoticeId = null;
    loadNotices();
}

// Create Notice
async function loadClasses() {
    try {
        const response = await fetch('api/admin/classes.php');
        const data = await response.json();
        
        if (data.success) {
            allClasses = data.data;
        }
    } catch (error) {
        console.error('Error loading classes:', error);
    }
}

function handleTargetChange() {
    const targetType = document.getElementById('target_type').value;
    const departmentSelection = document.getElementById('departmentSelection');
    const classSelection = document.getElementById('classSelection');
    
    // Hide both first
    departmentSelection.style.display = 'none';
    classSelection.style.display = 'none';
    
    if (targetType === 'specific_department') {
        departmentSelection.style.display = 'block';
        displayDepartmentList();
    } else if (targetType === 'specific_class') {
        classSelection.style.display = 'block';
        displayClassList();
    }
}

function displayDepartmentList() {
    const container = document.getElementById('departmentList');
    
    // Get unique departments from classes
    const departments = {};
    allClasses.forEach(cls => {
        if (!departments[cls.department_id]) {
            departments[cls.department_id] = {
                id: cls.department_id,
                name: cls.department_name,
                code: cls.department_code
            };
        }
    });
    
    container.innerHTML = Object.values(departments).map(dept => `
        <label style="display: block; padding: 8px; background: #f8f9fa; margin: 5px 0; border-radius: 4px;">
            <input type="checkbox" name="departments" value="${dept.id}">
            <strong>${dept.code}</strong> - ${dept.name}
        </label>
    `).join('');
}

function displayClassList() {
    const container = document.getElementById('classList');
    
    // Group classes by department
    const classesByDept = {};
    allClasses.forEach(cls => {
        if (!classesByDept[cls.department_code]) {
            classesByDept[cls.department_code] = [];
        }
        classesByDept[cls.department_code].push(cls);
    });
    
    // Display grouped by department
    let html = '';
    Object.keys(classesByDept).sort().forEach(deptCode => {
        html += `<div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">`;
        html += `<strong style="color: #667eea; display: block; margin-bottom: 8px;">${deptCode} - ${classesByDept[deptCode][0].department_name}</strong>`;
        
        classesByDept[deptCode].forEach(cls => {
            html += `
                <label style="display: block; padding: 4px 0 4px 20px;">
                    <input type="checkbox" name="classes" value="${cls.class_id}">
                    ${cls.class_name} (${cls.student_count} students)
                </label>
            `;
        });
        
        html += `</div>`;
    });
    
    container.innerHTML = html;
}

document.getElementById('createNoticeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('title', document.getElementById('title').value);
    formData.append('content', document.getElementById('content').value);
    formData.append('target_type', document.getElementById('target_type').value);
    
    const targetType = document.getElementById('target_type').value;
    
    if (targetType === 'specific_department') {
        const checkboxes = document.querySelectorAll('input[name="departments"]:checked');
        const targetDepartments = Array.from(checkboxes).map(cb => parseInt(cb.value));
        
        if (targetDepartments.length === 0) {
            showCreateMessage('Please select at least one department', 'error');
            return;
        }
        
        formData.append('target_departments', JSON.stringify(targetDepartments));
    } else if (targetType === 'specific_class') {
        const checkboxes = document.querySelectorAll('input[name="classes"]:checked');
        const targetClasses = Array.from(checkboxes).map(cb => parseInt(cb.value));
        
        if (targetClasses.length === 0) {
            showCreateMessage('Please select at least one class', 'error');
            return;
        }
        
        formData.append('target_classes', JSON.stringify(targetClasses));
    }
    
    // Add files
    const files = document.getElementById('attachments').files;
    for (let i = 0; i < files.length; i++) {
        formData.append('attachments[]', files[i]);
    }
    
    try {
        const response = await fetch('api/notices/create-with-files.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showCreateMessage('Notice created successfully!', 'success');
            document.getElementById('createNoticeForm').reset();
            document.getElementById('classSelection').style.display = 'none';
            await loadNotices();
        } else {
            showCreateMessage(data.message, 'error');
        }
    } catch (error) {
        showCreateMessage('Error creating notice', 'error');
    }
});

function showCreateMessage(message, type) {
    const messageDiv = document.getElementById('createMessage');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
    
    setTimeout(() => {
        messageDiv.className = 'message';
    }, 5000);
}

// User Management
async function loadUsers() {
    try {
        const response = await fetch('api/admin/users.php');
        const data = await response.json();
        
        if (data.success) {
            displayUsers(data.data.filter(u => u.is_verified && u.is_active));
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

async function loadPendingUsers() {
    try {
        const response = await fetch('api/admin/users.php');
        const data = await response.json();
        
        if (data.success) {
            const pendingUsers = data.data.filter(u => u.is_verified == 1 && u.is_active == 0);
            console.log('Total users:', data.data.length);
            console.log('Pending users:', pendingUsers.length);
            displayPendingUsers(pendingUsers);
        } else {
            console.error('Failed to load users:', data.message);
            document.getElementById('pendingList').innerHTML = '<p class="error">Failed to load pending users</p>';
        }
    } catch (error) {
        console.error('Error loading pending users:', error);
        document.getElementById('pendingList').innerHTML = '<p class="error">Error loading pending users</p>';
    }
}

function displayUsers(users) {
    const container = document.getElementById('usersList');
    
    if (users.length === 0) {
        container.innerHTML = '<p>No active users.</p>';
        return;
    }
    
    container.innerHTML = `
        <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Class</th>
                    <th>Roll No</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${users.map(user => `
                    <tr>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td><span class="badge badge-success">${user.role_name}</span></td>
                        <td>${user.class_name ? user.class_name + ' - ' + user.branch : 'N/A'}</td>
                        <td>${user.roll_no || 'N/A'}</td>
                        <td>
                            ${user.role_name !== 'Admin' ? `<button onclick="deleteUser(${user.user_id})" class="btn btn-danger btn-sm">Delete</button>` : ''}
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

function displayPendingUsers(users) {
    const container = document.getElementById('pendingList');
    
    if (users.length === 0) {
        container.innerHTML = `
            <div style="padding: 20px; text-align: center; background: #f8f9fa; border-radius: 8px;">
                <h3>📭 No Pending Approvals</h3>
                <p>There are no users waiting for approval at the moment.</p>
                <p style="margin-top: 15px; color: #666;">
                    <strong>To test this feature:</strong><br>
                    1. Register a new user at <a href="register.html" target="_blank">Registration Page</a><br>
                    2. Verify the OTP from email<br>
                    3. User will appear here for approval
                </p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = `
        <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Class</th>
                    <th>Roll No</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${users.map(user => `
                    <tr>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>${user.role_name}</td>
                        <td>${user.department_name || 'N/A'}</td>
                        <td>${user.class_name || 'N/A'}</td>
                        <td>${user.roll_no || 'N/A'}</td>
                        <td>${new Date(user.created_at).toLocaleDateString()}</td>
                        <td>
                            <button onclick="approveUser(${user.user_id})" class="btn btn-success btn-sm">✅ Approve</button>
                            <button onclick="openRejectModal(${user.user_id}, '${user.name}')" class="btn btn-danger btn-sm">❌ Reject</button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

async function approveUser(userId) {
    try {
        const response = await fetch('api/admin/approve-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('User approved successfully');
            await loadPendingUsers();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error approving user');
    }
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }
    
    try {
        const response = await fetch('api/admin/delete-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('User deleted successfully');
            await loadUsers();
            await loadPendingUsers();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error deleting user');
    }
}

function showSection(section) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.menu a').forEach(a => a.classList.remove('active'));
    
    if (section === 'notices') {
        document.getElementById('noticesSection').classList.add('active');
        loadNotices();
    } else if (section === 'create') {
        document.getElementById('createSection').classList.add('active');
    } else if (section === 'users') {
        document.getElementById('usersSection').classList.add('active');
        loadUsers();
    } else if (section === 'pending') {
        document.getElementById('pendingSection').classList.add('active');
        loadPendingUsers();
    } else if (section === 'bulk') {
        document.getElementById('bulkSection').classList.add('active');
        loadBulkClasses();
    } else if (section === 'departments') {
        document.getElementById('departmentsSection').classList.add('active');
        loadDepartments();
    } else if (section === 'classes') {
        document.getElementById('classesSection').classList.add('active');
        loadClassesManagement();
    }
    
    event.target.classList.add('active');
}

async function logout() {
    try {
        await fetch('api/logout.php');
        window.location.href = 'index.html';
    } catch (error) {
        window.location.href = 'index.html';
    }
}


// ==========================================
// DEPARTMENT MANAGEMENT
// ==========================================

async function loadDepartments() {
    try {
        const response = await fetch('api/admin/departments.php');
        const data = await response.json();
        
        if (data.success) {
            displayDepartments(data.data);
        }
    } catch (error) {
        console.error('Error loading departments:', error);
    }
}

function displayDepartments(departments) {
    const container = document.getElementById('departmentsList');
    
    if (departments.length === 0) {
        container.innerHTML = '<p>No departments found.</p>';
        return;
    }
    
    container.innerHTML = `
        <table class="user-table">
            <thead>
                <tr>
                    <th>Department Name</th>
                    <th>Code</th>
                    <th>Classes</th>
                    <th>Users</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${departments.map(dept => `
                    <tr>
                        <td><strong>${dept.department_name}</strong></td>
                        <td><span class="badge badge-success">${dept.department_code}</span></td>
                        <td>${dept.class_count} classes</td>
                        <td>${dept.user_count} users</td>
                        <td>${dept.is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>'}</td>
                        <td>
                            <button onclick="openEditDepartmentModal(${dept.department_id}, '${dept.department_name}', '${dept.department_code}')" class="btn btn-primary btn-sm">Edit</button>
                            <button onclick="deleteDepartment(${dept.department_id})" class="btn btn-danger btn-sm">Delete</button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

function openCreateDepartmentModal() {
    document.getElementById('createDepartmentModal').style.display = 'block';
    document.getElementById('createDepartmentForm').reset();
    document.getElementById('deptMessage').className = 'message';
}

function closeCreateDepartmentModal() {
    document.getElementById('createDepartmentModal').style.display = 'none';
}

document.getElementById('createDepartmentForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const departmentName = document.getElementById('deptName').value;
    const departmentCode = document.getElementById('deptCode').value;
    
    try {
        const response = await fetch('api/admin/create-department.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                department_name: departmentName,
                department_code: departmentCode
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showDeptMessage('Department created successfully!', 'success');
            setTimeout(() => {
                closeCreateDepartmentModal();
                loadDepartments();
            }, 1500);
        } else {
            showDeptMessage(data.message, 'error');
        }
    } catch (error) {
        showDeptMessage('Error creating department', 'error');
    }
});

function openEditDepartmentModal(deptId, deptName, deptCode) {
    document.getElementById('editDeptId').value = deptId;
    document.getElementById('editDeptName').value = deptName;
    document.getElementById('editDeptCode').value = deptCode;
    document.getElementById('editDepartmentModal').style.display = 'block';
    document.getElementById('editDeptMessage').className = 'message';
}

function closeEditDepartmentModal() {
    document.getElementById('editDepartmentModal').style.display = 'none';
}

document.getElementById('editDepartmentForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const departmentId = document.getElementById('editDeptId').value;
    const departmentName = document.getElementById('editDeptName').value;
    const departmentCode = document.getElementById('editDeptCode').value;
    
    try {
        const response = await fetch('api/admin/edit-department.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                department_id: departmentId,
                department_name: departmentName,
                department_code: departmentCode
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showEditDeptMessage('Department updated successfully!', 'success');
            setTimeout(() => {
                closeEditDepartmentModal();
                loadDepartments();
            }, 1500);
        } else {
            showEditDeptMessage(data.message, 'error');
        }
    } catch (error) {
        showEditDeptMessage('Error updating department', 'error');
    }
});

async function deleteDepartment(deptId) {
    if (!confirm('Are you sure you want to delete this department? This will fail if there are users or classes assigned to it.')) {
        return;
    }
    
    try {
        const response = await fetch('api/admin/delete-department.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ department_id: deptId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Department deleted successfully');
            loadDepartments();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error deleting department');
    }
}

function showDeptMessage(message, type) {
    const messageDiv = document.getElementById('deptMessage');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
}

function showEditDeptMessage(message, type) {
    const messageDiv = document.getElementById('editDeptMessage');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
}

// ==========================================
// CLASS MANAGEMENT
// ==========================================

async function loadClassesManagement() {
    try {
        const response = await fetch('api/admin/classes.php');
        const data = await response.json();
        
        if (data.success) {
            displayClassesManagement(data.data);
        }
    } catch (error) {
        console.error('Error loading classes:', error);
    }
}

function displayClassesManagement(classes) {
    const container = document.getElementById('classesList');
    
    if (classes.length === 0) {
        container.innerHTML = '<p>No classes found.</p>';
        return;
    }
    
    container.innerHTML = `
        <table class="user-table">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Department</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${classes.map(cls => `
                    <tr>
                        <td><strong>${cls.class_name}</strong></td>
                        <td>${cls.department_name} (${cls.department_code})</td>
                        <td>${cls.student_count} students</td>
                        <td>${cls.is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>'}</td>
                        <td>
                            <button onclick="openEditClassModal(${cls.class_id}, '${cls.class_name}', ${cls.department_id})" class="btn btn-primary btn-sm">Edit</button>
                            <button onclick="deleteClass(${cls.class_id})" class="btn btn-danger btn-sm">Delete</button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

async function openCreateClassModal() {
    // Load departments first
    await loadDepartmentsForDropdown('classDept');
    document.getElementById('createClassModal').style.display = 'block';
    document.getElementById('createClassForm').reset();
    document.getElementById('classMessage').className = 'message';
}

function closeCreateClassModal() {
    document.getElementById('createClassModal').style.display = 'none';
}

async function openEditClassModal(classId, className, deptId) {
    // Load departments first
    await loadDepartmentsForDropdown('editClassDept');
    
    document.getElementById('editClassId').value = classId;
    document.getElementById('editClassName').value = className;
    document.getElementById('editClassDept').value = deptId;
    document.getElementById('editClassModal').style.display = 'block';
    document.getElementById('editClassMessage').className = 'message';
}

function closeEditClassModal() {
    document.getElementById('editClassModal').style.display = 'none';
}

async function loadDepartmentsForDropdown(selectId) {
    try {
        const response = await fetch('api/admin/departments.php');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">Select Department</option>';
            data.data.forEach(dept => {
                select.innerHTML += `<option value="${dept.department_id}">${dept.department_name} (${dept.department_code})</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading departments:', error);
    }
}

document.getElementById('createClassForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const className = document.getElementById('className').value;
    const departmentId = document.getElementById('classDept').value;
    
    try {
        const response = await fetch('api/admin/create-class.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                class_name: className,
                department_id: departmentId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showClassMessage('Class created successfully!', 'success');
            setTimeout(() => {
                closeCreateClassModal();
                loadClassesManagement();
            }, 1500);
        } else {
            showClassMessage(data.message, 'error');
        }
    } catch (error) {
        showClassMessage('Error creating class', 'error');
    }
});

document.getElementById('editClassForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const classId = document.getElementById('editClassId').value;
    const className = document.getElementById('editClassName').value;
    const departmentId = document.getElementById('editClassDept').value;
    
    try {
        const response = await fetch('api/admin/edit-class.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                class_id: classId,
                class_name: className,
                department_id: departmentId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showEditClassMessage('Class updated successfully!', 'success');
            setTimeout(() => {
                closeEditClassModal();
                loadClassesManagement();
            }, 1500);
        } else {
            showEditClassMessage(data.message, 'error');
        }
    } catch (error) {
        showEditClassMessage('Error updating class', 'error');
    }
});

async function deleteClass(classId) {
    if (!confirm('Are you sure you want to delete this class? This will fail if there are students assigned to it.')) {
        return;
    }
    
    try {
        const response = await fetch('api/admin/delete-class.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ class_id: classId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Class deleted successfully');
            loadClassesManagement();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error deleting class');
    }
}

function showClassMessage(message, type) {
    const messageDiv = document.getElementById('classMessage');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
}

function showEditClassMessage(message, type) {
    const messageDiv = document.getElementById('editClassMessage');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
}


// ========== NEW FEATURES: Profile, All Users, Rejection ==========

// Show user profile
async function showProfile() {
    try {
        const response = await fetch('api/get-profile.php');
        const data = await response.json();
        
        if (data.success) {
            displayProfile(data.data);
            document.getElementById('profileModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading profile:', error);
    }
}

function displayProfile(user) {
    const profileHtml = `
        <div class="profile-info">
            <p><strong>Name:</strong> ${user.name}</p>
            <p><strong>Email:</strong> ${user.email}</p>
            <p><strong>Role:</strong> ${user.role_name}</p>
            <p><strong>Department:</strong> ${user.department_name || 'N/A'}</p>
            ${user.class_name ? `<p><strong>Class:</strong> ${user.class_name}</p>` : ''}
            ${user.roll_no ? `<p><strong>Roll Number:</strong> ${user.roll_no}</p>` : ''}
            <p><strong>Account Status:</strong> ${user.is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-warning">Inactive</span>'}</p>
            <p><strong>Email Verified:</strong> ${user.is_verified ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-warning">No</span>'}</p>
            <p><strong>Member Since:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
        </div>
    `;
    document.getElementById('profileDetails').innerHTML = profileHtml;
}

function closeProfileModal() {
    document.getElementById('profileModal').style.display = 'none';
}

// Load all users
async function loadAllUsers() {
    try {
        const response = await fetch('api/admin/get-all-users.php');
        const data = await response.json();
        
        if (data.success) {
            displayAllUsers(data.data);
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

function displayAllUsers(users) {
    const container = document.getElementById('usersList');
    
    if (users.length === 0) {
        container.innerHTML = '<p>No active users found.</p>';
        return;
    }
    
    const usersHtml = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Class</th>
                    <th>Roll No</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${users.map(user => `
                    <tr>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>${user.role_name}</td>
                        <td>${user.department_name || 'N/A'}</td>
                        <td>${user.class_name || 'N/A'}</td>
                        <td>${user.roll_no || 'N/A'}</td>
                        <td>${user.is_verified ? '<span class="badge badge-success">Verified</span>' : '<span class="badge badge-warning">Not Verified</span>'}</td>
                        <td>
                            <button onclick="viewUserDetails(${user.user_id})" class="btn btn-sm btn-primary">👁️ View</button>
                            <button onclick="openEditUserModal(${user.user_id})" class="btn btn-sm btn-warning">✏️ Edit</button>
                            <button onclick="confirmDeleteUser(${user.user_id}, '${user.name.replace(/'/g, "\\'")}')" class="btn btn-sm btn-danger">🗑️ Delete</button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
    
    container.innerHTML = usersHtml;
}

// View user details
async function viewUserDetails(userId) {
    try {
        const response = await fetch(`api/admin/get-all-users.php`);
        const data = await response.json();
        
        if (data.success) {
            const user = data.data.find(u => u.user_id == userId);
            if (user) {
                displayUserDetails(user);
                document.getElementById('userDetailsModal').style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error loading user details:', error);
    }
}

function displayUserDetails(user) {
    const detailsHtml = `
        <div class="user-details">
            <p><strong>User ID:</strong> ${user.user_id}</p>
            <p><strong>Name:</strong> ${user.name}</p>
            <p><strong>Email:</strong> ${user.email}</p>
            <p><strong>Role:</strong> ${user.role_name}</p>
            <p><strong>Department:</strong> ${user.department_name || 'N/A'}</p>
            ${user.class_name ? `<p><strong>Class:</strong> ${user.class_name}</p>` : ''}
            ${user.roll_no ? `<p><strong>Roll Number:</strong> ${user.roll_no}</p>` : ''}
            <p><strong>Email Verified:</strong> ${user.is_verified ? 'Yes' : 'No'}</p>
            <p><strong>Account Active:</strong> ${user.is_active ? 'Yes' : 'No'}</p>
            <p><strong>Registered On:</strong> ${new Date(user.created_at).toLocaleString()}</p>
        </div>
    `;
    document.getElementById('userDetailsContent').innerHTML = detailsHtml;
}

function closeUserDetailsModal() {
    document.getElementById('userDetailsModal').style.display = 'none';
}

// Reject user with reason
function openRejectModal(userId, userName) {
    document.getElementById('rejectUserId').value = userId;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectMessage').textContent = '';
    document.getElementById('rejectModal').style.display = 'block';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

// Handle reject form submission
document.addEventListener('DOMContentLoaded', () => {
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const userId = document.getElementById('rejectUserId').value;
            const reason = document.getElementById('rejectReason').value;
            
            if (!reason.trim()) {
                showRejectMessage('Please provide a reason for rejection', 'error');
                return;
            }
            
            try {
                const response = await fetch('api/admin/reject-user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        reason: reason
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showRejectMessage(data.message, 'success');
                    setTimeout(() => {
                        closeRejectModal();
                        loadPendingUsers();
                    }, 2000);
                } else {
                    showRejectMessage(data.message, 'error');
                }
            } catch (error) {
                showRejectMessage('An error occurred. Please try again.', 'error');
            }
        });
    }
});

function showRejectMessage(message, type) {
    const messageDiv = document.getElementById('rejectMessage');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
}

// Update showSection to load users when needed
const originalShowSection = showSection;
showSection = function(section) {
    originalShowSection(section);
    
    if (section === 'users') {
        loadAllUsers();
    }
};


// ========== EDIT USER FEATURE ==========

// Open edit user modal
async function openEditUserModal(userId) {
    try {
        const response = await fetch(`api/admin/get-user-details.php?user_id=${userId}`);
        const data = await response.json();
        
        if (data.success) {
            await loadRolesForEdit();
            await loadDepartmentsForEdit();
            populateEditUserForm(data.data);
            document.getElementById('editUserModal').style.display = 'block';
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error loading user details:', error);
        alert('Failed to load user details');
    }
}

// Load roles for edit form
async function loadRolesForEdit() {
    try {
        const conn = await fetch('api/admin/get-all-users.php');
        // For now, hardcode roles - you can create a separate API if needed
        const roleSelect = document.getElementById('editUserRole');
        roleSelect.innerHTML = `
            <option value="">Select Role</option>
            <option value="3">Student</option>
            <option value="2">Staff</option>
            <option value="1">Admin</option>
        `;
    } catch (error) {
        console.error('Error loading roles:', error);
    }
}

// Load departments for edit form
async function loadDepartmentsForEdit() {
    try {
        const response = await fetch('api/get-departments.php');
        const data = await response.json();
        
        if (data.success) {
            const deptSelect = document.getElementById('editUserDepartment');
            deptSelect.innerHTML = '<option value="">Select Department</option>';
            data.data.forEach(dept => {
                deptSelect.innerHTML += `<option value="${dept.department_id}">${dept.department_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading departments:', error);
    }
}

// Load classes for edit form
async function loadEditClasses() {
    const deptId = document.getElementById('editUserDepartment').value;
    const classSelect = document.getElementById('editUserClass');
    
    if (!deptId) {
        classSelect.innerHTML = '<option value="">Select Class</option>';
        return;
    }
    
    try {
        const response = await fetch(`api/get-classes-by-department.php?department_id=${deptId}`);
        const data = await response.json();
        
        if (data.success) {
            classSelect.innerHTML = '<option value="">Select Class</option>';
            data.data.forEach(cls => {
                classSelect.innerHTML += `<option value="${cls.class_id}">${cls.class_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading classes:', error);
    }
}

// Populate edit form with user data
function populateEditUserForm(user) {
    document.getElementById('editUserId').value = user.user_id;
    document.getElementById('editUserName').value = user.name;
    document.getElementById('editUserEmail').value = user.email;
    document.getElementById('editUserRole').value = user.role_id;
    document.getElementById('editUserDepartment').value = user.department_id || '';
    document.getElementById('editUserRollNo').value = user.roll_no || '';
    document.getElementById('editUserPassword').value = '';
    
    // Load classes for the selected department
    if (user.department_id) {
        loadEditClasses().then(() => {
            document.getElementById('editUserClass').value = user.class_id || '';
        });
    }
    
    // Handle role-specific fields
    handleEditRoleChange();
}

// Handle role change in edit form
function handleEditRoleChange() {
    const roleId = document.getElementById('editUserRole').value;
    const classGroup = document.getElementById('editUserClassGroup');
    const rollNoGroup = document.getElementById('editUserRollNoGroup');
    const classSelect = document.getElementById('editUserClass');
    const rollNoInput = document.getElementById('editUserRollNo');
    
    // Role ID: 3 = Student, 2 = Staff, 1 = Admin
    if (roleId == '3') { // Student
        classGroup.style.display = 'block';
        rollNoGroup.style.display = 'block';
        classSelect.required = true;
    } else { // Staff or Admin
        classGroup.style.display = 'none';
        rollNoGroup.style.display = 'none';
        classSelect.required = false;
        classSelect.value = '';
        rollNoInput.value = '';
    }
}

// Handle edit user form submission
document.addEventListener('DOMContentLoaded', () => {
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const userId = document.getElementById('editUserId').value;
            const name = document.getElementById('editUserName').value;
            const email = document.getElementById('editUserEmail').value;
            const roleId = document.getElementById('editUserRole').value;
            const departmentId = document.getElementById('editUserDepartment').value;
            const classId = document.getElementById('editUserClass').value;
            const rollNo = document.getElementById('editUserRollNo').value;
            const password = document.getElementById('editUserPassword').value;
            
            const updateData = {
                user_id: userId,
                name: name,
                email: email,
                role_id: roleId,
                department_id: departmentId || null,
                class_id: classId || null,
                roll_no: rollNo || ''
            };
            
            if (password) {
                updateData.password = password;
            }
            
            try {
                const response = await fetch('api/admin/update-user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showEditUserMessage(data.message, 'success');
                    setTimeout(() => {
                        closeEditUserModal();
                        loadAllUsers();
                    }, 2000);
                } else {
                    showEditUserMessage(data.message, 'error');
                }
            } catch (error) {
                showEditUserMessage('An error occurred. Please try again.', 'error');
            }
        });
    }
});

function showEditUserMessage(message, type) {
    const messageDiv = document.getElementById('editUserMessage');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
}

function closeEditUserModal() {
    document.getElementById('editUserModal').style.display = 'none';
    document.getElementById('editUserMessage').textContent = '';
}

// ========== DELETE USER FEATURE ==========

// Confirm delete user
function confirmDeleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete user "${userName}"?\n\nThis action cannot be undone and will delete:\n- User account\n- All their notices\n- All their comments\n- All related data`)) {
        deleteUser(userId);
    }
}

// Delete user
async function deleteUser(userId) {
    try {
        const response = await fetch('api/admin/delete-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            loadAllUsers(); // Reload user list
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('An error occurred while deleting user');
    }
}


// ========== BULK OPERATIONS ==========

// Load all classes for bulk operations
async function loadBulkClasses() {
    try {
        const response = await fetch('api/admin/classes.php');
        const data = await response.json();
        
        if (data.success) {
            const fromSelect = document.getElementById('bulkFromClass');
            const toSelect = document.getElementById('bulkToClass');
            
            fromSelect.innerHTML = '<option value="">Select Source Class</option>';
            toSelect.innerHTML = '<option value="">Select Target Class</option>';
            
            data.data.forEach(cls => {
                const option1 = `<option value="${cls.class_id}">${cls.class_name} - ${cls.department_name}</option>`;
                const option2 = `<option value="${cls.class_id}">${cls.class_name} - ${cls.department_name}</option>`;
                fromSelect.innerHTML += option1;
                toSelect.innerHTML += option2;
            });
        }
    } catch (error) {
        console.error('Error loading classes:', error);
    }
}

// Load students by class
async function loadStudentsByClass() {
    const classId = document.getElementById('bulkFromClass').value;
    const studentArea = document.getElementById('studentSelectionArea');
    const studentsList = document.getElementById('studentsList');
    
    if (!classId) {
        studentArea.style.display = 'none';
        return;
    }
    
    try {
        const response = await fetch('api/admin/get-all-users.php');
        const data = await response.json();
        
        if (data.success) {
            const students = data.data.filter(u => u.role_name === 'Student' && u.class_id == classId);
            
            if (students.length === 0) {
                studentsList.innerHTML = '<p>No students found in this class</p>';
                studentArea.style.display = 'block';
                return;
            }
            
            studentsList.innerHTML = students.map(student => `
                <div class="student-item">
                    <label>
                        <input type="checkbox" class="student-checkbox" value="${student.user_id}">
                        ${student.name} (${student.roll_no}) - ${student.email}
                    </label>
                </div>
            `).join('');
            
            studentArea.style.display = 'block';
            document.getElementById('selectAllStudents').checked = false;
        }
    } catch (error) {
        console.error('Error loading students:', error);
    }
}

// Toggle select all students
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllStudents').checked;
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll);
}

// Execute bulk class change
async function executeBulkClassChange() {
    const fromClassId = document.getElementById('bulkFromClass').value;
    const toClassId = document.getElementById('bulkToClass').value;
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    const userIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    if (!toClassId) {
        showBulkMessage('Please select target class', 'error');
        return;
    }
    
    if (userIds.length === 0) {
        showBulkMessage('Please select at least one student', 'error');
        return;
    }
    
    if (fromClassId == toClassId) {
        showBulkMessage('Source and target class cannot be the same', 'error');
        return;
    }
    
    if (!confirm(`Are you sure you want to move ${userIds.length} student(s) to the selected class?`)) {
        return;
    }
    
    try {
        const response = await fetch('api/admin/bulk-change-class.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                from_class_id: fromClassId,
                to_class_id: toClassId,
                user_ids: userIds
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showBulkMessage(data.message, 'success');
            // Reload students list
            setTimeout(() => {
                loadStudentsByClass();
            }, 2000);
        } else {
            showBulkMessage(data.message, 'error');
        }
    } catch (error) {
        showBulkMessage('An error occurred. Please try again.', 'error');
    }
}

function showBulkMessage(message, type) {
    const messageDiv = document.getElementById('bulkMessage');
    messageDiv.textContent = message;
    messageDiv.className = 'message ' + type;
}

// Update showSection to load bulk classes when needed
const originalShowSection2 = showSection;
showSection = function(section) {
    originalShowSection2(section);
    
    if (section === 'bulk') {
        loadBulkClasses();
    }
};
