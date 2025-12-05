# Quick Start Guide - Notice Sender

Get your Notice Sender system up and running in 10 minutes!

## Prerequisites
- ✅ XAMPP installed
- ✅ Web browser (Chrome, Firefox, Edge)
- ✅ Basic knowledge of XAMPP

## Step-by-Step Setup

### 1. Install XAMPP (5 minutes)
```
1. Download XAMPP from: https://www.apachefriends.org/
2. Run installer
3. Install to default location (C:\xampp)
4. Complete installation
```

### 2. Copy Project Files (1 minute)
```
1. Copy the entire "notice-sender" folder
2. Paste into: C:\xampp\htdocs\
3. Final path: C:\xampp\htdocs\notice-sender\
```

### 3. Start XAMPP Services (1 minute)
```
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Click "Start" next to MySQL
4. Both should show green "Running" status
```

### 4. Create Database (2 minutes)
```
1. Open browser
2. Go to: http://localhost/phpmyadmin
3. Click "Import" tab
4. Click "Choose File"
5. Navigate to: C:\xampp\htdocs\notice-sender\database\schema.sql
6. Click "Go" button at bottom
7. Wait for "Import has been successfully finished" message
```

### 5. Access the System (1 minute)
```
1. Open browser
2. Go to: http://localhost/notice-sender
3. You should see the login page
```

## First Login - Admin Account

Use these credentials to login as admin:
```
Email: admin@noticeboard.com
Password: admin123
```

**Important:** Change this password after first login!

## Test the System

### Test 1: Admin Login
```
1. Go to: http://localhost/notice-sender
2. Enter admin credentials
3. You should see Admin Dashboard
4. Check "Pending Approvals" - should be empty
```

### Test 2: Student Registration
```
1. Logout from admin
2. Click "Register" on login page
3. Fill in the form:
   - Name: Test Student
   - Email: student@test.com
   - Class: S.E.
   - Branch: Computer Science
   - Roll No: 101
   - Password: test123
   - Confirm Password: test123
4. Click "Register"
5. OTP will be shown on screen (since email not configured)
6. Copy the 6-digit OTP
7. Enter OTP in the popup
8. Click "Verify"
9. You should see success message
```

### Test 3: Approve Student
```
1. Login as admin again
2. Go to "Pending Approvals" section
3. You should see "Test Student"
4. Click "Approve" button
5. Student is now active
```

### Test 4: Student Login
```
1. Logout from admin
2. Login with:
   - Email: student@test.com
   - Password: test123
3. You should see Student Dashboard
4. No notices yet (empty)
```

### Test 5: Create Notice (as Admin)
```
1. Logout and login as admin
2. Go to "Create Notice" section
3. Fill in:
   - Title: Welcome to Notice Sender
   - Content: This is a test notice for all students.
   - Target: All Students
4. Click "Send Notice"
5. Success message should appear
```

### Test 6: View Notice (as Student)
```
1. Logout and login as student
2. You should see the notice in the list
3. Click on the notice to open it
4. You should see full content
5. Try adding a comment
6. Comment should appear below
```

## Email Configuration (Optional)

If you want to enable email OTP sending:

### Quick Gmail Setup
```
1. Open: config/config.php
2. Find these lines:
   define('SMTP_USER', 'your-email@gmail.com');
   define('SMTP_PASS', 'your-app-password');
   define('SMTP_FROM', 'your-email@gmail.com');
   define('EMAIL_ENABLED', false);

3. Replace with your Gmail:
   define('SMTP_USER', 'youremail@gmail.com');
   define('SMTP_PASS', 'xxxx xxxx xxxx xxxx'); // Get from Google
   define('SMTP_FROM', 'youremail@gmail.com');
   define('EMAIL_ENABLED', true);

4. To get App Password:
   - Go to: https://myaccount.google.com/apppasswords
   - Generate password for "Mail"
   - Copy the 16-character code
   - Use it in SMTP_PASS
```

For detailed email setup, see: `EMAIL_SETUP.md`

## Common Issues & Solutions

### Issue 1: Cannot access http://localhost/notice-sender
**Solution:**
- Check if Apache is running in XAMPP
- Check if folder is in correct location: C:\xampp\htdocs\notice-sender\
- Try: http://127.0.0.1/notice-sender

### Issue 2: Database connection error
**Solution:**
- Check if MySQL is running in XAMPP
- Verify database was imported correctly
- Go to phpMyAdmin and check if "notice_sender" database exists

### Issue 3: Login not working
**Solution:**
- Clear browser cookies
- Check if you're using correct credentials
- Verify database has admin user (check users table in phpMyAdmin)

### Issue 4: OTP not showing
**Solution:**
- This is normal if EMAIL_ENABLED is false
- OTP should be displayed on screen
- Check browser console (F12) for any errors

### Issue 5: File upload not working
**Solution:**
- Check if "uploads" folder exists in project root
- Check folder permissions (should be writable)
- Verify file size is under 5MB
- Check file type (only PDF, JPG, PNG allowed)

## Next Steps

### For Students:
1. Register your account
2. Wait for admin approval
3. Login and view notices
4. Comment on notices
5. Download attachments

### For Staff:
1. Contact admin to create your account
2. Login with provided credentials
3. Create notices for students
4. Upload attachments
5. Track who viewed your notices
6. Manage your notices

### For Admin:
1. Change default password
2. Approve pending student registrations
3. Create staff accounts manually (via phpMyAdmin or add staff registration page)
4. Create notices for all users
5. Manage users and notices
6. Monitor system activity

## System URLs

- **Login:** http://localhost/notice-sender/
- **Register:** http://localhost/notice-sender/register.html
- **Forgot Password:** http://localhost/notice-sender/forgot-password.html
- **phpMyAdmin:** http://localhost/phpmyadmin
- **Student Dashboard:** http://localhost/notice-sender/student-dashboard.html
- **Staff Dashboard:** http://localhost/notice-sender/staff-dashboard.html
- **Admin Dashboard:** http://localhost/notice-sender/admin-dashboard.html

## Default Accounts

### Admin
```
Email: admin@noticeboard.com
Password: admin123
```

### Test Classes Available
- F.E. - Computer Science
- S.E. - Computer Science
- T.E. - Computer Science
- B.E. - Computer Science
- F.E. - Information Technology
- S.E. - Information Technology
- T.E. - Information Technology
- B.E. - Information Technology

## File Locations

- **Project Root:** C:\xampp\htdocs\notice-sender\
- **Database Schema:** database/schema.sql
- **Configuration:** config/config.php
- **Uploads:** uploads/notices/
- **Logs:** (check XAMPP logs if issues)

## Support & Documentation

- **Full Features:** See `FEATURES.md`
- **Email Setup:** See `EMAIL_SETUP.md`
- **Installation:** See `INSTALLATION.txt`
- **README:** See `README.md`

## Security Reminders

1. ✅ Change default admin password
2. ✅ Don't use in production without HTTPS
3. ✅ Keep XAMPP updated
4. ✅ Use strong passwords
5. ✅ Regular database backups
6. ✅ Configure email properly
7. ✅ Monitor user registrations

## Testing Checklist

- [ ] XAMPP Apache running
- [ ] XAMPP MySQL running
- [ ] Database imported successfully
- [ ] Can access login page
- [ ] Admin login works
- [ ] Student registration works
- [ ] OTP verification works
- [ ] Student approval works
- [ ] Student login works
- [ ] Notice creation works
- [ ] Notice viewing works
- [ ] Comments work
- [ ] File upload works (if tested)

## Congratulations! 🎉

Your Notice Sender system is now ready to use!

For any issues, check the troubleshooting section or refer to the detailed documentation files.

Happy noticing! 📢
