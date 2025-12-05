let currentUser = null;
let currentNoticeId = null;
let allClasses = [];

// Check authentication on page load
window.addEventListener('DOMContentLoaded', async () => {
    await checkAuth();
    await loadNotices();
    await loadClasses();
});

async function checkAuth() {
    try {
        const response = await fetch('api/check-session.php');
        const data = await response.json();
        
        if (!data.success) {
            window.location.href = 'index.html';
            return;
        }
        
        currentUser = data.data;
        document.getElementById('userName').textContent = currentUser.name + ' (' + currentUser.role + ')';
        
        // Show/hide menu items based on role
        if (currentUser.role === 'Staff' || currentUser.role === 'Admin') {
            document.getElementById('createNoticeMenu').style.display = 'block';
        }
        
        if (currentUser.role === 'Admin') {
            document.getElementById('adminMenu').style.display = 'block';
        }
    } catch (error) {
        window.location.href = 'index.html';
    }
}

async function loadNotices() {
    try {
        const response = await fetch('api/notices/list.php');
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
    
    container.innerHTML = notices.map(notice => `
        <div class="notice-card" onclick="viewNotice(${notice.notice_id})">
            <h3>${notice.title}</h3>
            <div class="meta">
                <strong>From:</strong> ${notice.sender_name} | 
                <strong>Date:</strong> ${new Date(notice.created_at).toLocaleString()}
                ${notice.is_staff_only ? '<span class="badge badge-warning">Staff Only</span>' : ''}
            </div>
            <div class="content">${notice.content.substring(0, 150)}${notice.content.length > 150 ? '...' : ''}</div>
        </div>
    `).join('');
}

async function viewNotice(noticeId) {
    currentNoticeId = noticeId;
    
    try {
        const response = await fetch(`api/notices/list.php`);
        const data = await response.json();
        
        if (data.success) {
            const notice = data.data.find(n => n.notice_id == noticeId);
            if (notice) {
                displayNoticeDetail(notice);
                await loadComments(noticeId);
                document.getElementById('noticeModal').style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error loading notice:', error);
    }
}

function displayNoticeDetail(notice) {
    const canDelete = currentUser.role === 'Admin' || 
                     (currentUser.role === 'Staff' && notice.sent_by_user_id == currentUser.user_id);
    
    const deleteButton = canDelete ? 
        `<button onclick="deleteNotice(${notice.notice_id})" class="btn btn-danger">Delete Notice</button>` : '';
    
    document.getElementById('noticeDetail').innerHTML = `
        <h2>${notice.title}</h2>
        <div class="meta">
            <strong>From:</strong> ${notice.sender_name} (${notice.sender_email})<br>
            <strong>Date:</strong> ${new Date(notice.created_at).toLocaleString()}
            ${notice.is_staff_only ? '<span class="badge badge-warning">Staff Only</span>' : ''}
        </div>
        <div class="content" style="margin: 20px 0; line-height: 1.8;">${notice.content}</div>
        ${deleteButton}
    `;
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
        container.innerHTML = '<p>No comments yet.</p>';
        return;
    }
    
    container.innerHTML = comments.map(comment => `
        <div class="comment">
            <div class="author">${comment.user_name} (${comment.role_name})</div>
            <div class="text">${comment.comment_text}</div>
            <div class="time">${new Date(comment.created_at).toLocaleString()}</div>
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

function closeNoticeModal() {
    document.getElementById('noticeModal').style.display = 'none';
    currentNoticeId = null;
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
    const classSelection = document.getElementById('classSelection');
    
    if (targetType === 'specific_class') {
        classSelection.style.display = 'block';
        displayClassList();
    } else {
        classSelection.style.display = 'none';
    }
}

function displayClassList() {
    const container = document.getElementById('classList');
    container.innerHTML = allClasses.map(cls => `
        <label>
            <input type="checkbox" name="classes" value="${cls.class_id}">
            ${cls.class_name} - ${cls.branch}
        </label>
    `).join('');
}

document.getElementById('createNoticeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const title = document.getElementById('title').value;
    const content = document.getElementById('content').value;
    const targetType = document.getElementById('target_type').value;
    
    let targetClasses = [];
    if (targetType === 'specific_class') {
        const checkboxes = document.querySelectorAll('input[name="classes"]:checked');
        targetClasses = Array.from(checkboxes).map(cb => parseInt(cb.value));
        
        if (targetClasses.length === 0) {
            showCreateMessage('Please select at least one class', 'error');
            return;
        }
    }
    
    try {
        const response = await fetch('api/notices/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title,
                content,
                target_type: targetType,
                target_classes: targetClasses
            })
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
    }, 3000);
}

// Admin Panel
async function loadUsers() {
    try {
        const response = await fetch('api/admin/users.php');
        const data = await response.json();
        
        if (data.success) {
            displayUsers(data.data);
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

function displayUsers(users) {
    const container = document.getElementById('usersList');
    
    container.innerHTML = `
        <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Class</th>
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
                        <td>${user.class_name ? user.class_name + ' - ' + user.branch : 'N/A'}</td>
                        <td>
                            ${user.is_verified ? '<span class="badge badge-success">Verified</span>' : '<span class="badge badge-warning">Not Verified</span>'}
                            ${user.is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>'}
                        </td>
                        <td>
                            ${!user.is_active ? `<button onclick="approveUser(${user.user_id})" class="btn btn-success">Approve</button>` : ''}
                            ${user.role_name !== 'Admin' ? `<button onclick="deleteUser(${user.user_id})" class="btn btn-danger">Delete</button>` : ''}
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
            await loadUsers();
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
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Error deleting user');
    }
}

// Navigation
function showSection(section) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.menu a').forEach(a => a.classList.remove('active'));
    
    if (section === 'notices') {
        document.getElementById('noticesSection').classList.add('active');
        loadNotices();
    } else if (section === 'create') {
        document.getElementById('createSection').classList.add('active');
    } else if (section === 'admin') {
        document.getElementById('adminSection').classList.add('active');
        loadUsers();
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
