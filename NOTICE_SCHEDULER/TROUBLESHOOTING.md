# 🔧 Notice Sender - Troubleshooting Guide

## 🚨 Login Issues - QUICK FIX

### Problem: Cannot login as admin or any user

**SOLUTION - Follow these steps:**

### Step 1: Run Diagnostic Test
1. Open browser
2. Go to: `http://localhost/NOTICE_SCHEDULER/test-connection.php`
3. Check all test results
4. Look for any RED ✗ marks

### Step 2: Fix Admin User
1. Go to: `http://localhost/NOTICE_SCHEDULER/setup-admin.php`
2. This will create/reset the admin user with correct password
3. You should see "✓ Admin user CREATED/UPDATED successfully!"

### Step 3: Try Login Again
1. Go to: `http://localhost/NOTICE_SCHEDULER`
2. Login with:
   - Email: `admin@noticeboard.com`
   - Password: `admin123`

---

## 🔍 Common Issues & Solutions

### Issue 1: "Database connection error"

**Symptoms:**
- Cannot login
- Registration fails
- Blank pages

**Solutions:**
1. **Check MySQL is running:**
   - Open XAMPP Control Panel
   - MySQL should show "Running" in green
   - If not, click "Start" button

2. **Check database exists:**
   - Go to: `http://localhost/phpmyadmin`
   - Look for `notice_sender` database in left sidebar
   - If not found, import `database/schema.sql` again

3. **Check database credentials:**
   - Open `config/database.php`
   - Verify:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');  // Usually empty for XAMPP
     define('DB_NAME', 'notice_sender');
     ```

---

### Issue 2: "Invalid email or password"

**Symptoms:**
- Login fails with admin credentials
- Password seems correct but doesn't work

**Solutions:**
1. **Reset admin password:**
   - Run: `http://localhost/NOTICE_SCHEDULER/setup-admin.php`
   - This creates admin with correct password hash

2. **Check admin user exists:**
   - Go to phpMyAdmin: `http://localhost/phpmyadmin`
   - Open `notice_sender` database
   - Click on `users` table
   - Look for `admin@noticeboard.com`
   - If not found, run `setup-admin.php`

3. **Verify password hash:**
   - In phpMyAdmin, check `password_hash` column
   - Should start with `$2y$10$`
   - If different format, run `setup-admin.php`

---

### Issue 3: Registration not working

**Symptoms:**
- Registration form submits but nothing happens
- OTP not showing
- Error messages

**Solutions:**
1. **Check database connection:**
   - Run: `http://localhost/NOTICE_SCHEDULER/test-connection.php`
   - All tests should be green ✓

2. **Check email configuration:**
   - Open `config/config.php`
   - Find: `define('EMAIL_ENABLED', false);`
   - Should be `false` for testing (OTP shows on screen)
   - If `true`, configure SMTP settings or set to `false`

3. **Check browser console:**
   - Press F12 in browser
   - Go to "Console" tab
   - Look for error messages
   - Check "Network" tab for failed API calls

---

### Issue 4: "Your account is pending admin approval"

**Symptoms:**
- Student registered successfully
- Verified OTP
- Cannot login

**Solution:**
This is NORMAL behavior! Students need admin approval:
1. Login as admin
2. Go to "Pending Approvals" section
3. Click "Approve" button for the student
4. Now student can login

---

### Issue 5: Blank page or white screen

**Symptoms:**
- Page loads but shows nothing
- No error message

**Solutions:**
1. **Check PHP errors:**
   - Open `config/config.php`
   - Find these lines:
     ```php
     error_reporting(E_ALL);
     ini_set('display_errors', 1);
     ```
   - Make sure they exist

2. **Check Apache error log:**
   - Open XAMPP Control Panel
   - Click "Logs" button next to Apache
   - Click "Error Log"
   - Look for recent errors

3. **Check file paths:**
   - Make sure all files are in correct location
   - Path should be: `C:\xampp\htdocs\NOTICE_SCHEDULER\`

---

### Issue 6: "Session not found" or keeps logging out

**Symptoms:**
- Login successful but redirects back to login
- Dashboard loads then goes back to login

**Solutions:**
1. **Clear browser cache:**
   - Press Ctrl+Shift+Delete
   - Clear cookies and cache
   - Try again

2. **Check session configuration:**
   - Run: `http://localhost/NOTICE_SCHEDULER/test-connection.php`
   - Check "Session Test" section
   - Should show green ✓

3. **Check browser cookies:**
   - Make sure cookies are enabled
   - Check browser privacy settings

---

### Issue 7: File upload not working

**Symptoms:**
- Cannot upload attachments
- Upload fails silently

**Solutions:**
1. **Check uploads folder:**
   - Navigate to: `C:\xampp\htdocs\NOTICE_SCHEDULER\uploads\notices\`
   - If folder doesn't exist, create it
   - Right-click → Properties → Make sure it's not Read-only

2. **Check PHP upload settings:**
   - Open: `C:\xampp\php\php.ini`
   - Find: `upload_max_filesize`
   - Should be at least `5M`
   - Find: `post_max_size`
   - Should be at least `8M`
   - Restart Apache after changes

---

### Issue 8: Email/OTP not working

**Symptoms:**
- OTP not received in email
- Registration hangs

**Solution:**
This is NORMAL for testing! Email is disabled by default:
1. OTP will be displayed on screen
2. Copy the 6-digit code
3. Enter in OTP verification popup

**To enable email:**
- See `EMAIL_SETUP.md` for detailed guide
- Configure Gmail SMTP settings
- Set `EMAIL_ENABLED = true` in `config/config.php`

---

## 🛠️ Diagnostic Tools

### Tool 1: test-connection.php
**Purpose:** Check system health
**URL:** `http://localhost/NOTICE_SCHEDULER/test-connection.php`
**Checks:**
- PHP version
- Required extensions
- Database connection
- Tables existence
- Admin user
- Password hashing
- File permissions
- Sessions

### Tool 2: setup-admin.php
**Purpose:** Create/reset admin user
**URL:** `http://localhost/NOTICE_SCHEDULER/setup-admin.php`
**Does:**
- Creates admin user if not exists
- Resets admin password if exists
- Uses correct password hash

### Tool 3: Browser Developer Tools
**How to open:** Press F12
**Tabs to check:**
- **Console:** JavaScript errors
- **Network:** Failed API calls
- **Application:** Cookies and session storage

---

## 📋 Pre-Flight Checklist

Before reporting issues, verify:

- [ ] XAMPP Apache is running (green in control panel)
- [ ] XAMPP MySQL is running (green in control panel)
- [ ] Database `notice_sender` exists in phpMyAdmin
- [ ] All tables exist (9 tables total)
- [ ] Admin user exists in `users` table
- [ ] Ran `test-connection.php` - all tests green
- [ ] Ran `setup-admin.php` - admin created/updated
- [ ] Browser cache cleared
- [ ] Cookies enabled in browser
- [ ] Using correct URL: `http://localhost/NOTICE_SCHEDULER`
- [ ] No typos in email/password

---

## 🔐 Security Note

**After fixing issues, DELETE these files:**
- `test-connection.php`
- `setup-admin.php`

These files expose system information and should not be on production servers!

---

## 📞 Still Having Issues?

### Check These:

1. **XAMPP Status:**
   - Both Apache and MySQL must be green/running
   - Port 80 (Apache) and 3306 (MySQL) must be free

2. **Database Import:**
   - Re-import `database/schema.sql` if tables are missing
   - Make sure import completed without errors

3. **File Permissions:**
   - All files should be readable
   - `uploads` folder should be writable

4. **Browser:**
   - Try different browser (Chrome, Firefox, Edge)
   - Try incognito/private mode
   - Disable browser extensions

5. **Firewall/Antivirus:**
   - May block localhost connections
   - Temporarily disable to test

---

## 🎯 Quick Recovery Steps

If everything is broken, start fresh:

1. **Stop XAMPP:**
   - Stop Apache and MySQL

2. **Drop Database:**
   - Go to phpMyAdmin
   - Drop `notice_sender` database

3. **Re-import:**
   - Import `database/schema.sql` again

4. **Setup Admin:**
   - Run `setup-admin.php`

5. **Test:**
   - Run `test-connection.php`
   - All should be green ✓

6. **Login:**
   - Try admin login again

---

## 📊 Error Messages Explained

### "Invalid email or password"
- Email not in database, OR
- Password incorrect, OR
- Password hash format wrong
- **Fix:** Run `setup-admin.php`

### "Please verify your email first"
- User registered but didn't verify OTP
- **Fix:** Complete OTP verification

### "Your account is pending admin approval"
- Student verified but admin hasn't approved
- **Fix:** Login as admin and approve user

### "Database connection error"
- MySQL not running, OR
- Database doesn't exist, OR
- Wrong credentials
- **Fix:** Check XAMPP MySQL, verify database exists

### "Unauthorized"
- Not logged in, OR
- Session expired
- **Fix:** Login again

---

## ✅ Success Indicators

You'll know it's working when:
- ✓ `test-connection.php` shows all green checks
- ✓ Admin login works
- ✓ Dashboard loads correctly
- ✓ Can create notices
- ✓ Student registration works
- ✓ OTP shows on screen
- ✓ Comments work

---

**Remember:** Most issues are due to:
1. MySQL not running
2. Database not imported
3. Admin password hash incorrect

**Solution:** Run `setup-admin.php` first!
