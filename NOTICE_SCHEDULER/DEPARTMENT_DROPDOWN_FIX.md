# Department Dropdown Not Loading - Troubleshooting Guide

## Problem
When clicking on the department dropdown in the registration page, no options appear.

---

## Root Cause
The departments are not loading because either:
1. Database schema has not been imported
2. No departments exist in the database
3. JavaScript is not able to fetch data from the API

---

## Solution Steps

### Step 1: Test if Departments Exist in Database

**Run the test script:**
1. Open browser and go to: `http://localhost/NOTICE_SCHEDULER/test-departments.php`
2. This will show you:
   - All departments in database
   - All classes in database
   - Test results from API endpoints

**Expected Result:**
- You should see 5 departments: Computer Science, Information Technology, Electronics, Mechanical, Civil
- You should see 8 classes linked to departments

**If NO departments shown:**
- Proceed to Step 2 (Import Database)

**If departments ARE shown:**
- Proceed to Step 3 (Check Browser Console)

---

### Step 2: Import Database Schema

**If departments are missing, import the schema:**

1. **Open phpMyAdmin:**
   - URL: `http://localhost/phpmyadmin`

2. **Import the schema:**
   - Click on "Import" tab
   - Click "Choose File"
   - Navigate to: `C:\xampp\htdocs\NOTICE_SCHEDULER\database\schema.sql`
   - Click "Go" button at bottom

3. **Verify import:**
   - You should see "notice_sender" database created
   - Click on "notice_sender" database in left sidebar
   - You should see these tables:
     - roles
     - departments (with 5 rows)
     - classes (with 8 rows)
     - users
     - notices
     - notice_targets
     - otp_tokens
     - comments
     - notice_attachments
     - notice_views

4. **Create admin user:**
   - After import, run: `http://localhost/NOTICE_SCHEDULER/fix-admin-password.php`
   - This creates admin user: admin@noticeboard.com / admin123

5. **Test again:**
   - Go to: `http://localhost/NOTICE_SCHEDULER/test-departments.php`
   - Departments should now appear

---

### Step 3: Check Browser Console for Errors

**If departments exist in database but dropdown is still empty:**

1. **Open registration page:**
   - URL: `http://localhost/NOTICE_SCHEDULER/register.html`

2. **Open browser console:**
   - Press `F12` or `Ctrl+Shift+I`
   - Click on "Console" tab

3. **Look for errors:**
   - Red error messages indicate problems
   - Common errors:
     - `404 Not Found` - API file doesn't exist
     - `CORS error` - Cross-origin issue
     - `Failed to fetch` - Network or server issue
     - `Unexpected token` - JSON parsing error

4. **Check Network tab:**
   - Click "Network" tab in browser dev tools
   - Refresh the page
   - Look for `get-departments.php` request
   - Click on it to see:
     - Status code (should be 200)
     - Response data (should show departments JSON)

---

### Step 4: Verify API Files Exist

**Check if API files are present:**

1. Navigate to: `C:\xampp\htdocs\NOTICE_SCHEDULER\api\`

2. Verify these files exist:
   - `get-departments.php` ✓
   - `get-classes-by-department.php` ✓

3. **Test API directly in browser:**
   - Open: `http://localhost/NOTICE_SCHEDULER/api/get-departments.php`
   - You should see JSON response like:
     ```json
     {
       "success": true,
       "data": [
         {
           "department_id": "1",
           "department_name": "Computer Science",
           "department_code": "CS",
           "is_active": "1"
         },
         ...
       ]
     }
     ```

---

### Step 5: Check Apache and MySQL are Running

**Verify XAMPP services:**

1. Open XAMPP Control Panel
2. Check that both are running (green):
   - ✅ Apache
   - ✅ MySQL

3. If not running, click "Start" button for each

---

## How the Department Loading Works

### 1. Page Load Sequence:
```
register.html loads
  ↓
register.js executes
  ↓
DOMContentLoaded event fires
  ↓
loadDepartments() function called
  ↓
Fetch request to api/get-departments.php
  ↓
PHP queries database for departments
  ↓
Returns JSON with department list
  ↓
JavaScript populates dropdown with options
```

### 2. When User Selects Department:
```
User selects department
  ↓
onchange="loadClassesByDept()" fires
  ↓
Fetch request to api/get-classes-by-department.php?department_id=X
  ↓
PHP queries database for classes in that department
  ↓
Returns JSON with class list
  ↓
JavaScript populates class dropdown with options
```

### 3. Auto-Update Feature:
- When admin creates/edits/deletes departments or classes, changes are saved to database
- Next time registration page loads, `loadDepartments()` fetches fresh data
- Dropdown automatically shows updated list
- No manual refresh needed - it's dynamic!

---

## Admin Can Manage Departments and Classes

**Admin has full control:**

1. **Login as admin:**
   - Email: admin@noticeboard.com
   - Password: admin123

2. **Manage Departments:**
   - Click "Departments" in sidebar
   - Create new department
   - Edit existing department
   - Delete department (if no users/classes linked)
   - Activate/deactivate departments

3. **Manage Classes:**
   - Click "Classes" in sidebar
   - Create new class (must select department)
   - Edit existing class
   - Delete class (if no users linked)
   - Activate/deactivate classes

4. **Changes Reflect Immediately:**
   - When admin adds department → appears in registration dropdown
   - When admin adds class → appears when that department is selected
   - When admin deletes/deactivates → removed from dropdowns
   - All changes are user-specific and role-specific

---

## User-Specific and Role-Specific Features

### For Students:
- Must select: Department + Class + Roll Number
- Can only see classes belonging to selected department
- Notices are targeted based on their class

### For Staff:
- Must select: Department only
- No class or roll number required
- Can send notices to any class in any department

### For Admin:
- Full control over departments and classes
- Can manage all users
- Can see all notices

---

## Quick Diagnostic Commands

**Test in browser console (F12):**

```javascript
// Test if fetch works
fetch('api/get-departments.php')
  .then(r => r.json())
  .then(d => console.log(d));

// Should output: {success: true, data: [...]}
```

**Test in PHP:**
```php
// Create test.php in root folder
<?php
require_once 'config/database.php';
$conn = getDBConnection();
$result = $conn->query("SELECT COUNT(*) as count FROM departments");
$row = $result->fetch_assoc();
echo "Departments count: " . $row['count'];
?>
```

---

## Common Issues and Fixes

| Issue | Cause | Fix |
|-------|-------|-----|
| Dropdown empty | No departments in DB | Import schema.sql |
| 404 error | API file missing | Check api/ folder |
| CORS error | Wrong URL | Use localhost, not 127.0.0.1 |
| No classes load | Department not selected | Select department first |
| Old data showing | Browser cache | Hard refresh (Ctrl+F5) |

---

## Files Involved

```
NOTICE_SCHEDULER/
├── register.html              # Registration form
├── assets/js/register.js      # Loads departments/classes
├── api/
│   ├── get-departments.php    # Returns all active departments
│   └── get-classes-by-department.php  # Returns classes for dept
├── database/
│   └── schema.sql             # Contains sample departments/classes
└── test-departments.php       # Diagnostic script (NEW)
```

---

## Success Checklist

- [ ] XAMPP Apache and MySQL are running
- [ ] Database "notice_sender" exists
- [ ] Departments table has 5 rows
- [ ] Classes table has 8 rows
- [ ] test-departments.php shows departments
- [ ] api/get-departments.php returns JSON
- [ ] Browser console shows no errors
- [ ] Department dropdown populates on page load
- [ ] Class dropdown populates when department selected
- [ ] Admin can create/edit/delete departments
- [ ] Admin can create/edit/delete classes
- [ ] Changes reflect immediately in registration form

---

**If you've followed all steps and still have issues, check:**
1. PHP error logs: `C:\xampp\php\logs\php_error_log`
2. Apache error logs: `C:\xampp\apache\logs\error.log`
3. Browser console for JavaScript errors
4. Network tab for failed requests

**Need help? Run test-departments.php and share the output!**
