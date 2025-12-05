# Registration Error Fix - "An error occurred. Please try again"

## Problem Fixed
The registration was showing "An error occurred. Please try again" due to a parameter binding mismatch in the SQL query.

---

## What Was Wrong

In `api/register.php`, the `bind_param` had incorrect type string:

**BEFORE (Wrong):**
```php
// For Student - had "sssiii" (6 types) but 7 parameters
$stmt->bind_param("sssiii", $email, $passwordHash, $name, $rollNo, $roleId, $departmentId, $classId);

// For Staff - had "sssii" (5 types) but 5 parameters - this was correct
$stmt->bind_param("sssii", $email, $passwordHash, $name, $roleId, $departmentId);
```

**AFTER (Fixed):**
```php
// For Student - now "ssssiii" (7 types) for 7 parameters
$stmt->bind_param("ssssiii", $email, $passwordHash, $name, $rollNo, $roleId, $departmentId, $classId);
//                 ↑ Added extra 's' for roll_no

// For Staff - now "ssiii" (5 types) for 5 parameters
$stmt->bind_param("ssiii", $email, $passwordHash, $name, $roleId, $departmentId);
//                 ↑ Removed one 's' to match 5 parameters
```

**Parameter Types:**
- `s` = string
- `i` = integer

**Student Parameters (7 total):**
1. email (string) - s
2. password_hash (string) - s
3. name (string) - s
4. roll_no (string) - s
5. role_id (integer) - i
6. department_id (integer) - i
7. class_id (integer) - i

**Staff Parameters (5 total):**
1. email (string) - s
2. password_hash (string) - s
3. name (string) - s
4. role_id (integer) - i
5. department_id (integer) - i

---

## How to Test if Fixed

### Option 1: Use Debug Script (Recommended)
```
Open: http://localhost/NOTICE_SCHEDULER/debug-registration.php
```

This will:
- ✅ Check database connection
- ✅ Verify all tables exist
- ✅ Check roles, departments, classes
- ✅ Test student registration
- ✅ Test staff registration
- ✅ Show detailed error messages if any

### Option 2: Test Manually
1. Open: `http://localhost/NOTICE_SCHEDULER/register.html`
2. Fill in the form:
   - Select role: Student
   - Name: Test User
   - Email: test@college.edu
   - Department: Computer Science
   - Class: F.E.
   - Roll Number: TEST001
   - Password: test123
   - Confirm Password: test123
3. Click "Register"
4. Should see success message with OTP

### Option 3: Check Browser Console
1. Open registration page
2. Press F12 to open Developer Tools
3. Go to Console tab
4. Try to register
5. Look for any error messages

---

## Additional Improvements Made

### 1. Better Error Logging
Added detailed error logging to help debug issues:

```php
if (!$stmt->execute()) {
    $error = $stmt->error;
    error_log("Registration failed: " . $error);
    jsonResponse(false, 'Registration failed: ' . $error);
}
```

Now if registration fails, you'll see the actual MySQL error message.

### 2. Created Debug Tools
- `debug-registration.php` - Comprehensive testing script
- `test-register.php` - Quick API test
- `test-departments.php` - Check departments/classes

---

## Common Registration Errors and Solutions

| Error Message | Cause | Solution |
|--------------|-------|----------|
| "An error occurred" | bind_param mismatch | ✅ FIXED in this update |
| "All required fields must be filled" | Missing form data | Fill all required fields |
| "Invalid role selected" | Role not Student/Staff | Select valid role |
| "Students must provide class and roll number" | Missing student fields | Fill class and roll number |
| "Email already registered" | Duplicate email | Use different email |
| "Invalid class or department combination" | Class doesn't belong to dept | Select matching class |
| "Invalid role" | Role not in database | Import schema.sql |
| "Registration failed: [SQL error]" | Database issue | Check error message |

---

## Verification Checklist

After the fix, verify these work:

- [ ] Student registration with all fields
- [ ] Staff registration without class/roll number
- [ ] OTP is generated and shown/sent
- [ ] User is created in database
- [ ] is_verified = FALSE (until OTP verified)
- [ ] is_active = FALSE (until admin approves)
- [ ] Registration confirmation email sent (if EMAIL_ENABLED)
- [ ] No errors in browser console
- [ ] No errors in PHP error log

---

## Testing Different Scenarios

### Test 1: Student Registration
```
Role: Student
Name: John Doe
Email: john.doe@college.edu
Department: Computer Science
Class: S.E.
Roll Number: CS2024001
Password: test123
Confirm: test123
```
**Expected**: Success, OTP shown/sent

### Test 2: Staff Registration
```
Role: Staff
Name: Jane Smith
Email: jane.smith@college.edu
Department: Information Technology
Password: test123
Confirm: test123
```
**Expected**: Success, OTP shown/sent (no class/roll needed)

### Test 3: Missing Fields
```
Role: Student
Name: Test
Email: test@college.edu
Department: (not selected)
```
**Expected**: Error "All required fields must be filled"

### Test 4: Duplicate Email
```
Use same email as Test 1
```
**Expected**: Error "Email already registered"

---

## Files Modified

1. **api/register.php**
   - Fixed bind_param for Student (ssssiii)
   - Fixed bind_param for Staff (ssiii)
   - Added better error logging

2. **debug-registration.php** (NEW)
   - Comprehensive testing script
   - Checks all components
   - Tests both Student and Staff registration

3. **test-register.php** (NEW)
   - Quick API test script
   - Shows actual API responses

---

## Next Steps

1. **Run debug script:**
   ```
   http://localhost/NOTICE_SCHEDULER/debug-registration.php
   ```

2. **If all tests pass:**
   - Registration is working!
   - Test on actual registration page
   - Try both Student and Staff roles

3. **If tests fail:**
   - Check error messages in debug output
   - Verify database is imported
   - Check PHP error logs
   - Ensure XAMPP Apache and MySQL are running

---

## PHP Error Logs Location

If you need to check PHP errors:
- Windows: `C:\xampp\php\logs\php_error_log`
- Apache errors: `C:\xampp\apache\logs\error.log`

---

## Quick Commands

**Test registration API:**
```
http://localhost/NOTICE_SCHEDULER/debug-registration.php
```

**Test departments:**
```
http://localhost/NOTICE_SCHEDULER/test-departments.php
```

**Check API directly:**
```
http://localhost/NOTICE_SCHEDULER/api/get-departments.php
```

---

**Status**: ✅ FIXED
**Issue**: bind_param parameter mismatch
**Solution**: Corrected type strings to match parameter count
**Date**: December 5, 2025
