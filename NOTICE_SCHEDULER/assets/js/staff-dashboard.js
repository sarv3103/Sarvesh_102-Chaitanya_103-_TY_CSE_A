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
        
        if (!data.success || data.data.role !== 'Staff') {
            window.location.href = 'index.html';
            return;
        }
        
        currentUser = data.data;
        document.getElementById('userName').textContent = currentUser.name + ' (' + currentUser.role + ')';
    } catch (error) {
        window.location.href = 'index.html';
    }
}

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

function showSection(section) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.menu a').forEach(a => a.classList.remove('active'));
    
    if (section === 'notices') {
        document.getElementById('noticesSection').classList.add('active');
        loadNotices();
    } else if (section === 'create') {
        document.getElementById('createSection').classList.add('active');
    } else if (section === 'mynotices') {
        document.getElementById('mynoticesSection').classList.add('active');
        loadMyNotices();
    }
    
    event.target.classList.add('active');
}

async function loadMyNotices() {
    try {
        const response = await fetch('api/notices/list-with-counts.php');
        const data = await response.json();
        
        if (data.success) {
            const myNotices = data.data.filter(n => n.sender_email === currentUser.email);
            displayMyNotices(myNotices);
        }
    } catch (error) {
        console.error('Error loading my notices:', error);
    }
}

function displayMyNotices(notices) {
    const container = document.getElementById('myNoticesList');
    
    if (notices.length === 0) {
        container.innerHTML = '<p>You haven\'t created any notices yet.</p>';
        return;
    }
    
    container.innerHTML = notices.map(notice => `
        <div class="notice-card" onclick="viewNotice(${notice.notice_id})">
            <h3>${notice.title}</h3>
            <div class="meta">
                <strong>Date:</strong> ${new Date(notice.created_at).toLocaleString()}
                ${notice.is_staff_only ? '<span class="badge badge-warning">Staff Only</span>' : ''}
            </div>
            <div class="content">${notice.content.substring(0, 200)}${notice.content.length > 200 ? '...' : ''}</div>
            <div class="notice-stats">
                <span>👁️ ${notice.view_count} views</span>
                <span>💬 ${notice.comment_count} comments</span>
            </div>
        </div>
    `).join('');
}

async function logout() {
    try {
        await fetch('api/logout.php');
        window.location.href = 'index.html';
    } catch (error) {
        window.location.href = 'index.html';
    }
}


// ========== PROFILE FEATURE ==========

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
            <p><strong>Department:</strong> ${user.department_name || 'N/A'} (${user.department_code || ''})</p>
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
