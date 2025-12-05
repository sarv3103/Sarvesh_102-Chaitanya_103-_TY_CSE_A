let currentUser = null;
let currentNoticeId = null;

window.addEventListener('DOMContentLoaded', async () => {
    await checkAuth();
    await loadNotices();
});

async function checkAuth() {
    try {
        const response = await fetch('api/check-session.php');
        const data = await response.json();
        
        if (!data.success || data.data.role !== 'Student') {
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
        container.innerHTML = '<p>No notices available for you.</p>';
        return;
    }
    
    container.innerHTML = notices.map(notice => {
        // Determine badge based on target_info
        let badge = '';
        if (notice.target_info && notice.target_info.label) {
            const badgeClass = notice.target_info.type === 'everyone' ? 'badge-info' :
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
            await loadComments(noticeId);
            document.getElementById('noticeModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading notice:', error);
    }
}

function displayNoticeDetail(data) {
    const notice = data.notice;
    
    document.getElementById('noticeDetail').innerHTML = `
        <h2>${notice.title}</h2>
        <div class="meta">
            <strong>From:</strong> ${notice.sender_name} (${notice.sender_email})<br>
            <strong>Date:</strong> ${new Date(notice.created_at).toLocaleString()}<br>
            <strong>Views:</strong> 👁️ ${data.view_count} | <strong>Comments:</strong> 💬 ${data.comment_count}
        </div>
        <div class="content" style="margin: 20px 0; line-height: 1.8; white-space: pre-wrap;">${notice.content}</div>
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
        <div class="comment" id="comment-${comment.comment_id}">
            <div class="author">${comment.user_name} (${comment.role_name})</div>
            <div class="text" id="text-${comment.comment_id}">${comment.comment_text}</div>
            <div class="comment-footer">
                <span class="time">${new Date(comment.created_at).toLocaleString()}</span>
                ${comment.updated_at !== comment.created_at ? '<span class="edited">(edited)</span>' : ''}
                ${comment.can_edit ? `<button onclick="editComment(${comment.comment_id}, '${escapeHtml(comment.comment_text)}')" class="btn-link">Edit</button>` : ''}
                ${comment.can_delete ? `<button onclick="deleteComment(${comment.comment_id})" class="btn-link">Delete</button>` : ''}
            </div>
        </div>
    `).join('');
}

function escapeHtml(text) {
    return text.replace(/'/g, "\\'").replace(/"/g, '&quot;');
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

function closeNoticeModal() {
    document.getElementById('noticeModal').style.display = 'none';
    currentNoticeId = null;
    loadNotices(); // Refresh to update view counts
}

function showSection(section) {
    // Student only has one section
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
