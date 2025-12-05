# Notice Sender - Complete Features Documentation

## Table of Contents
1. [User Roles & Permissions](#user-roles--permissions)
2. [Authentication Features](#authentication-features)
3. [Notice Management](#notice-management)
4. [Comments System](#comments-system)
5. [File Attachments](#file-attachments)
6. [View Tracking](#view-tracking)
7. [Admin Panel](#admin-panel)

---

## User Roles & Permissions

### 1. Student Role
**Dashboard:** `student-dashboard.html`

**Can Do:**
- ✅ View notices targeted to their class
- ✅ View notices targeted to all students
- ✅ View notices targeted to everyone
- ✅ Comment on visible notices
- ✅ Edit their own comments
- ✅ Delete their own comments
- ✅ Download attachments from notices
- ✅ View comment count and view count

**Cannot Do:**
- ❌ Create notices
- ❌ View staff-only notices
- ❌ Edit or delete notices
- ❌ See who viewed notices
- ❌ Delete other users' comments

### 2. Staff Role
**Dashboard:** `staff-dashboard.html`

**Can Do:**
- ✅ Everything students can do, PLUS:
- ✅ Create notices with attachments
- ✅ Target notices to:
  - All students
  - Specific class(es)
  - All staff only
  - Everyone (staff + students)
- ✅ View all staff-only notices
- ✅ View all student notices
- ✅ Edit their own notices
- ✅ Delete their own notices
- ✅ See who viewed their notices
- ✅ View detailed viewer list with timestamps
- ✅ Upload files (PDF, JPG, PNG) with notices

**Cannot Do:**
- ❌ Edit other staff members' notices
- ❌ Delete other staff members' notices
- ❌ Approve/delete users
- ❌ Delete notices created by admin

### 3. Admin Role
**Dashboard:** `admin-dashboard.html`

**Can Do:**
- ✅ Everything staff can do, PLUS:
- ✅ Delete ANY notice (including staff notices)
- ✅ View all notices system-wide
- ✅ Approve student registrations
- ✅ Reject student registrations
- ✅ Delete any user account
- ✅ View all users
- ✅ View pending approvals
- ✅ Delete any comment
- ✅ Full system control

**Special Privileges:**
- 🔐 Cannot be deleted by other admins
- 🔐 Can override all permissions
- 🔐 Access to user management panel

---

## Authentication Features

### Registration (Students Only)
**File:** `register.html`

**Process:**
1. Student fills registration form:
   - Full Name
   - Email (username)
   - Class (F.E., S.E., T.E., B.E.)
   - Branch (Computer Science, IT, etc.)
   - Roll Number
   - Password (min 6 characters)
   - Confirm Password

2. System validates:
   - All fields filled
   - Valid email format
   - Passwords match
   - Email not already registered
   - Valid class and branch combination

3. OTP sent to email (6-digit code)
   - Valid for 5 minutes
   - If email not configured, OTP shown on screen

4. Student enters OTP to verify email

5. Account status: **Verified but Inactive**
   - Waiting for admin approval

6. Admin approves → Account becomes **Active**

7. Student can now login

### Login
**File:** `index.html`

**Process:**
1. Enter email and password
2. System checks:
   - Valid credentials
   - Email verified
   - Account active (admin approved)
3. Redirects to role-specific dashboard:
   - Admin → `admin-dashboard.html`
   - Staff → `staff-dashboard.html`
   - Student → `student-dashboard.html`

### Forgot Password
**File:** `forgot-password.html`

**Process:**
1. Enter registered email
2. OTP sent to email (6-digit, 5-minute validity)
3. Enter OTP to verify
4. Set new password
5. Confirm new password
6. Password updated
7. Redirect to login

---

## Notice Management

### Creating Notices (Staff & Admin)

**Features:**
- Title (required)
- Content (required, supports multi-line text)
- File attachments (optional, multiple files)
- Target audience selection

**Target Options:**

1. **All Students**
   - Visible to: All students, all staff, admin
   - Use case: General announcements for students

2. **Specific Class(es)**
   - Select one or multiple classes
   - Visible to: Selected class students, all staff, admin
   - Use case: Class-specific announcements

3. **All Staff Only**
   - Visible to: All staff, admin only
   - Hidden from: All students
   - Use case: Internal staff communications

4. **Everyone**
   - Visible to: All students, all staff, admin
   - Use case: Important institution-wide announcements

### Viewing Notices

**Notice List View:**
- Title
- Sender name
- Date posted
- Content preview (first 200 characters)
- View count (👁️ icon)
- Comment count (💬 icon)
- Staff-only badge (if applicable)

**Full Notice View (Click to open):**
- Complete title
- Full content (preserves line breaks)
- Sender details (name and email)
- Posted date
- Updated date (if edited)
- View count
- Comment count
- File attachments (if any)
- Viewer list (for creator, staff, admin)
- All comments
- Comment form

### Editing Notices

**Who Can Edit:**
- Only the notice creator

**What Can Be Edited:**
- Title
- Content

**What Cannot Be Edited:**
- Target audience
- Attachments (cannot add/remove after creation)
- Sender information
- Posted date

**Process:**
1. Open notice in full view
2. Click "Edit Notice" button
3. Modal opens with current title and content
4. Make changes
5. Click "Update Notice"
6. Notice updated with timestamp

### Deleting Notices

**Who Can Delete:**
- Notice creator
- Admin (can delete any notice)

**Process:**
1. Open notice in full view
2. Click "Delete Notice" button
3. Confirm deletion
4. Notice permanently deleted
5. All associated comments deleted
6. All view records deleted
7. All attachments remain on server (can be manually cleaned)

---

## Comments System

### Adding Comments

**Who Can Comment:**
- Anyone who can view the notice

**Process:**
1. Open notice in full view
2. Scroll to comments section
3. Type comment in text area
4. Click "Post Comment"
5. Comment appears immediately

**Features:**
- Real-time posting
- No character limit
- Supports multi-line text
- Shows commenter name and role
- Timestamp displayed

### Editing Comments

**Who Can Edit:**
- Only the comment author

**Process:**
1. Click "Edit" button on your comment
2. Prompt appears with current text
3. Modify text
4. Click OK
5. Comment updated with "(edited)" label

### Deleting Comments

**Who Can Delete:**
- Comment author
- Admin (can delete any comment)

**Process:**
1. Click "Delete" button
2. Confirm deletion
3. Comment permanently removed

**Comment Display:**
- Author name and role
- Comment text
- Posted timestamp
- "(edited)" indicator if modified
- Edit button (if you're the author)
- Delete button (if you're the author or admin)

---

## File Attachments

### Uploading Files

**Supported Formats:**
- PDF (.pdf)
- JPEG (.jpg, .jpeg)
- PNG (.png)

**Limitations:**
- Maximum file size: 5MB per file
- Multiple files allowed
- Total upload size limited by PHP settings

**Process:**
1. When creating notice, click "Choose Files"
2. Select one or multiple files
3. Files uploaded when notice is submitted
4. Files stored in `uploads/notices/` directory
5. Unique filename generated to prevent conflicts

**File Naming:**
- Format: `notice_{noticeId}_{timestamp}_{uniqueId}.{extension}`
- Example: `notice_123_1234567890_abc123.pdf`

### Viewing/Downloading Attachments

**Display:**
- Icon based on file type (📄 for PDF, 🖼️ for images)
- Original filename
- File size (formatted: B, KB, MB)
- Download link

**Process:**
1. Open notice in full view
2. Attachments section shows all files
3. Click filename to download
4. Opens in new tab (for PDFs and images)
5. Downloads directly (for other types)

---

## View Tracking

### How It Works

**Automatic Tracking:**
- When user opens a notice in full view
- View recorded in database
- One view per user per notice (no duplicates)
- Timestamp recorded

**View Count:**
- Displayed on notice list
- Displayed in full notice view
- Updates in real-time

### Who Can See Viewer Details

**Viewer List Visible To:**
- ✅ Notice creator
- ✅ All staff members
- ✅ Admin

**Viewer List Hidden From:**
- ❌ Students (they only see view count)

**Viewer Information Shown:**
- Viewer name
- Viewer role (Student/Staff/Admin)
- Viewed timestamp
- Sorted by most recent first

**Use Cases:**
- Track notice reach
- Verify important notices were seen
- Monitor engagement
- Follow up with users who haven't viewed

---

## Admin Panel

### User Management

**View All Users:**
- Table showing all active users
- Columns: Name, Email, Role, Class, Roll No
- Delete button for non-admin users

**Pending Approvals:**
- Separate section for unverified users
- Shows registration date
- Approve or Reject buttons
- Approve → User can login
- Reject → User deleted from system

**User Actions:**

1. **Approve User**
   - Changes `is_active` to TRUE
   - User can now login
   - Email notification (if configured)

2. **Delete User**
   - Permanently removes user
   - All their notices remain (attributed to deleted user)
   - All their comments remain (attributed to deleted user)
   - Cannot delete admin users
   - Cannot delete yourself

### Notice Management

**Admin Privileges:**
- View all notices (including staff-only)
- Delete any notice
- View all viewer lists
- View all comments
- Delete any comment

**Statistics Dashboard:**
- Total notices
- Total users
- Pending approvals
- Recent activity

---

## Security Features

### Password Security
- Bcrypt hashing (PASSWORD_BCRYPT)
- Minimum 6 characters
- Salted automatically
- Never stored in plain text

### OTP Security
- 6-digit random code
- 5-minute expiration
- One-time use only
- Marked as used after verification
- Separate OTPs for registration and password reset

### Session Security
- PHP sessions for authentication
- Session-based user tracking
- Automatic logout on session expiry
- Role verification on each request

### Input Validation
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- File upload validation
- Email format validation
- CSRF protection (can be enhanced)

### Access Control
- Role-based permissions
- Dashboard-level separation
- API-level authorization checks
- Database-level foreign key constraints

---

## Technical Implementation

### Database Tables
- `users` - User accounts
- `roles` - User roles (Admin, Staff, Student)
- `classes` - Available classes
- `notices` - Notice content
- `notice_targets` - Targeting rules
- `notice_attachments` - File attachments
- `notice_views` - View tracking
- `comments` - User comments
- `otp_tokens` - OTP codes

### API Endpoints
- `api/register.php` - Student registration
- `api/verify-otp.php` - OTP verification
- `api/login.php` - User login
- `api/logout.php` - User logout
- `api/forgot-password.php` - Request password reset
- `api/reset-password.php` - Reset password
- `api/notices/create-with-files.php` - Create notice with files
- `api/notices/list-with-counts.php` - List notices with stats
- `api/notices/get-detail.php` - Get notice details
- `api/notices/edit.php` - Edit notice
- `api/notices/delete.php` - Delete notice
- `api/comments/create.php` - Add comment
- `api/comments/list.php` - List comments
- `api/comments/edit.php` - Edit comment
- `api/comments/delete.php` - Delete comment
- `api/admin/users.php` - List users
- `api/admin/approve-user.php` - Approve user
- `api/admin/delete-user.php` - Delete user
- `api/admin/classes.php` - List classes

### File Structure
```
notice-sender/
├── api/                    # Backend APIs
├── assets/                 # Frontend assets
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript files
├── config/                # Configuration
├── database/              # SQL schema
├── includes/              # PHP functions
├── uploads/               # Uploaded files
│   └── notices/          # Notice attachments
├── index.html            # Login page
├── register.html         # Registration
├── forgot-password.html  # Password reset
├── student-dashboard.html # Student dashboard
├── staff-dashboard.html   # Staff dashboard
├── admin-dashboard.html   # Admin dashboard
└── README.md             # Documentation
```

---

## Future Enhancements

Potential features for future versions:
- Email notifications for new notices
- Push notifications
- Notice categories/tags
- Search and filter functionality
- Notice scheduling (post at specific time)
- Rich text editor for notice content
- Image preview for attachments
- Bulk user import (CSV)
- User profile management
- Notice templates
- Analytics dashboard
- Mobile app
- Read receipts
- Notice expiration dates
- Archive functionality
