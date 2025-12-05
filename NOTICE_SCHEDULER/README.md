# CampusChrono - Notice Management System

A comprehensive web-based notice management system for educational institutions with role-based access control.

## 🚀 Quick Start Guide

### For First-Time Setup:

1. **Prerequisites**:
   - XAMPP installed (Apache + MySQL)
   - Gmail account with 2-Step Verification enabled

2. **Clone or Download** this repository to `C:\xampp\htdocs\`

3. **Copy Configuration File**:
   ```bash
   Copy config/config.example.php to config/config.php
   ```

4. **Setup Gmail App Password** (See "Email & OTP Configuration" section below):
   - Go to: https://myaccount.google.com/apppasswords
   - Generate app password for "Mail"
   - Copy the 16-character password

5. **Update Email Credentials** in `config/config.php`:
   ```php
   define('SMTP_USER', 'your-email@gmail.com');
   define('SMTP_PASS', 'xxxx xxxx xxxx xxxx');  // App password from step 4
   define('SMTP_FROM', 'your-email@gmail.com');
   ```

6. **Import Database**:
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create new database: `notice_sender`
   - Click "Import" tab
   - Select file: `database/schema.sql`
   - Click "Go"

7. **Access Application**:
   - URL: http://localhost/NOTICE_SCHEDULER/
   - Admin Login: admin@noticeboard.com / admin123

### For Forking This Project:

1. Click **"Fork"** button on GitHub
2. Clone your forked repository
3. Follow the setup steps above
4. Start using!

## 📧 Email & OTP Configuration (IMPORTANT)

This system uses **Gmail SMTP** for sending OTP codes and notifications. Follow these steps:

### Step 1: Get Gmail App Password
1. Go to your Google Account: https://myaccount.google.com/
2. Click on **Security** (left sidebar)
3. Enable **2-Step Verification** (if not already enabled)
4. Scroll down to **2-Step Verification** section
5. Click on **App passwords** at the bottom
6. Select **Mail** and **Other (Custom name)**
7. Enter name: "CampusChrono" or any name
8. Click **Generate**
9. Copy the 16-character password (format: xxxx xxxx xxxx xxxx)

### Step 2: Update Configuration File
1. Open `config/config.php`
2. Update these lines:
   ```php
   define('SMTP_USER', 'your-email@gmail.com');        // Your Gmail address
   define('SMTP_PASS', 'xxxx xxxx xxxx xxxx');         // App password from Step 1
   define('SMTP_FROM', 'your-email@gmail.com');        // Same Gmail address
   ```
3. Save the file

### Step 3: Test Email
1. Try registering a new user
2. Check if OTP email is received
3. If email fails, OTP will be displayed on screen for testing

**Note:** If you don't want to use email, set `EMAIL_ENABLED = false` in config.php. OTP will be displayed on screen instead.

## 🔒 Security Note

**NEVER commit `config/config.php` to GitHub!** It contains sensitive credentials.
- The file is already in `.gitignore`
- Use `config.example.php` as a template
- Each user must create their own `config.php`

## Features

### User Roles
1. **Student** - View notices targeted to their class/role
2. **Staff** - Send notices to students and staff, view all relevant notices
3. **Admin** - Full control over users and notices

### Key Functionalities

#### Authentication & Security
- **OTP-based Registration** - Email verification with 5-minute valid OTP
- **Secure Login** - Email and password authentication with role-based dashboard redirection
- **Password Reset** - OTP-based password recovery
- **Role-based Access Control** - Separate dashboards for each user role

#### Notice Management
- **Targeted Notices** - Send to specific classes, all students, all staff, or everyone
- **File Attachments** - Upload PDF, JPG, PNG files (max 5MB each) with notices
- **Edit Notices** - Notice creators can edit their own notices
- **Delete Notices** - Creators and admins can delete notices
- **View Tracking** - Track who viewed each notice (visible to creator, staff, admin)
- **View & Comment Counts** - Display on notice list for quick overview

#### Comments System
- **Add Comments** - All targeted users can comment on notices
- **Edit Comments** - Users can edit their own comments
- **Delete Comments** - Users can delete their own comments, admins can delete any
- **Comment Timestamps** - Shows when comment was posted and if edited

#### Admin Panel
- **User Approval** - Approve/reject student registrations
- **User Management** - Delete users, view all user details
- **Pending Approvals** - Separate section for pending user approvals
- **Full Notice Control** - Delete any notice, view all statistics

## Technology Stack
- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: XAMPP (Apache + MySQL)

## Installation Instructions

### Prerequisites
- XAMPP installed on your system
- Web browser (Chrome, Firefox, etc.)

### Setup Steps

1. **Install XAMPP**
   - Download from https://www.apachefriends.org/
   - Install and start Apache and MySQL services

2. **Copy Project Files**
   - Copy the entire project folder to `C:\xampp\htdocs\notice-sender`

3. **Create Database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Click on "Import" tab
   - Select the file: `database/schema.sql`
   - Click "Go" to import
   - This will create the database `notice_sender` with all tables and sample data

4. **Configure Email (Optional)**
   - Open `config/config.php`
   - Update SMTP settings with your email credentials:
     ```php
     define('SMTP_USER', 'your-email@gmail.com');
     define('SMTP_PASS', 'your-app-password');
     define('SMTP_FROM', 'your-email@gmail.com');
     ```
   - For Gmail, you need to create an "App Password" from your Google Account settings
   - If email is not configured, OTP will be displayed on screen for testing

5. **Access the Application**
   - Open browser and go to: http://localhost/notice-sender
   - Default admin credentials:
     - Email: admin@noticeboard.com
     - Password: admin123

## Usage Guide

### For Students
1. **Register**
   - Go to registration page
   - Fill in all details (Name, Email, Class, Branch, Roll No, Password)
   - Verify email with OTP
   - Wait for admin approval

2. **Login**
   - Use email and password to login
   - View notices targeted to your class
   - Comment on notices

### For Staff
1. **Account Creation**
   - Contact admin to create staff account
   - Login with provided credentials

2. **Send Notices**
   - Click "Create Notice"
   - Enter title and content
   - Select target audience:
     - All Students
     - Specific Class(es)
     - All Staff
     - Everyone
   - Submit

3. **Manage Notices**
   - View all notices
   - Delete your own notices
   - Comment on any notice

### For Admin
1. **User Management**
   - Go to Admin Panel
   - Approve pending student registrations
   - Delete users if needed
   - Change user roles or classes

2. **Notice Management**
   - View all notices
   - Delete any notice
   - Create notices for any audience

## Database Structure

### Tables
- `users` - User accounts with credentials and profile info
- `roles` - User roles (Admin, Staff, Student)
- `classes` - Available classes and branches
- `notices` - Notice content and metadata
- `notice_targets` - Targeting rules for notices
- `otp_tokens` - OTP codes for verification and password reset
- `comments` - User comments on notices

## Security Features
- Password hashing using bcrypt
- OTP expiration (5 minutes)
- Session-based authentication
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Role-based access control

## Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Default: host=localhost, user=root, password=(empty)

### Email Not Sending
- OTP will be displayed on screen if email fails
- Configure SMTP settings in `config/config.php`
- For Gmail, enable "Less secure app access" or use App Password

### Session Issues
- Clear browser cookies
- Restart Apache in XAMPP
- Check PHP session settings

## Project Structure
```
notice-sender/
├── api/                    # Backend API endpoints
│   ├── admin/             # Admin-specific APIs
│   ├── comments/          # Comment management
│   ├── notices/           # Notice management
│   └── *.php              # Auth APIs
├── assets/
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript files
├── config/                # Configuration files
├── database/              # SQL schema
├── includes/              # PHP helper functions
├── index.html             # Login page
├── register.html          # Registration page
├── forgot-password.html   # Password reset
├── dashboard.html         # Main dashboard
└── README.md
```

## Default Credentials

**Admin Account:**
- Email: admin@noticeboard.com
- Password: admin123

**Sample Classes:**
- F.E., S.E., T.E., B.E.
- Branches: Computer Science, Information Technology

## Future Enhancements
- File attachments for notices
- Read receipts
- Notice categories
- Email notifications
- Mobile responsive design improvements
- Search and filter functionality

## Support
For issues or questions, please check the troubleshooting section or contact your system administrator.
