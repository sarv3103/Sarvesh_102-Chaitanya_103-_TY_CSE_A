# Notice Sender - Complete Testing Checklist

Use this checklist to verify all features are working correctly.

## ✅ Pre-Testing Setup

- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running
- [ ] Database imported successfully
- [ ] Can access http://localhost/notice-sender
- [ ] Browser console open (F12) to check for errors

---

## 🔐 Authentication Tests

### Test 1: Admin Login
- [ ] Go to http://localhost/notice-sender
- [ ] Enter: admin@noticeboard.com / admin123
- [ ] Click Login
- [ ] Redirects to admin-dashboard.html
- [ ] Shows "System Admin (Admin)" in navbar
- [ ] No console errors

### Test 2: Student Registration
- [ ] Logout from admin
- [ ] Click "Register" link
- [ ] Fill all fields:
  - Name: Test Student 1
  - Email: student1@test.com
  - Class: S.E.
  - Branch: Computer Science
  - Roll No: 101
  - Password: test123
  - Confirm Password: test123
- [ ] Click Register
- [ ] OTP displayed on screen (or sent to email if configured)
- [ ] Copy OTP
- [ ] Enter OTP in modal
- [ ] Click Verify
- [ ] Success message: "Account verified successfully! Please wait for admin approval."
- [ ] Redirects to login page

### Test 3: Login Before Approval (Should Fail)
- [ ] Try to login with student1@test.com / test123
- [ ] Should show error: "Your account is pending admin approval"
- [ ] Cannot access dashboard

### Test 4: Admin Approval
- [ ] Login as admin
- [ ] Go to "Pending Approvals" section
- [ ] See "Test Student 1" in list
- [ ] Click "Approve" button
- [ ] Success message shown
- [ ] User disappears from pending list

### Test 5: Student Login After Approval
- [ ] Logout from admin
- [ ] Login with student1@test.com / test123
- [ ] Redirects to student-dashboard.html
- [ ] Shows "Test Student 1 (Student)" in navbar
- [ ] Dashboard loads correctly

### Test 6: Forgot Password
- [ ] Logout
- [ ] Click "Forgot Password?" link
- [ ] Enter: student1@test.com
- [ ] Click "Send OTP"
- [ ] OTP displayed (or sent to email)
- [ ] Copy OTP
- [ ] Enter OTP
- [ ] Click "Verify OTP"
- [ ] New password form appears
- [ ] Enter new password: newpass123
- [ ] Confirm password: newpass123
- [ ] Click "Reset Password"
- [ ] Success message shown
- [ ] Redirects to login
- [ ] Login with new password works

### Test 7: Invalid Login Attempts
- [ ] Try login with wrong email
- [ ] Error: "Invalid email or password"
- [ ] Try login with wrong password
- [ ] Error: "Invalid email or password"
- [ ] Try login with unverified email
- [ ] Error: "Please verify your email first"

---

## 📢 Notice Management Tests

### Test 8: Create Notice - All Students (Admin)
- [ ] Login as admin
- [ ] Go to "Create Notice" section
- [ ] Fill form:
  - Title: Welcome Notice
  - Content: Welcome to all students!
  - Target: All Students
- [ ] Click "Send Notice"
- [ ] Success message shown
- [ ] Notice appears in "All Notices" section

### Test 9: Create Notice with Attachment (Admin)
- [ ] Go to "Create Notice"
- [ ] Fill form:
  - Title: Important Document
  - Content: Please review the attached document.
  - Attachments: Select a PDF file
  - Target: All Students
- [ ] Click "Send Notice"
- [ ] Success message shown
- [ ] Notice created with attachment

### Test 10: Create Notice - Specific Class (Admin)
- [ ] Go to "Create Notice"
- [ ] Fill form:
  - Title: S.E. CSE Notice
  - Content: This is for S.E. Computer Science only
  - Target: Specific Class(es)
  - Select: S.E. - Computer Science
- [ ] Click "Send Notice"
- [ ] Success message shown

### Test 11: Create Notice - Staff Only (Admin)
- [ ] Go to "Create Notice"
- [ ] Fill form:
  - Title: Staff Meeting
  - Content: Staff meeting tomorrow at 10 AM
  - Target: All Staff Only
- [ ] Click "Send Notice"
- [ ] Success message shown
- [ ] Notice has "Staff Only" badge

### Test 12: Create Notice - Everyone (Admin)
- [ ] Go to "Create Notice"
- [ ] Fill form:
  - Title: Holiday Announcement
  - Content: Holiday on Friday
  - Target: Everyone
- [ ] Click "Send Notice"
- [ ] Success message shown

### Test 13: View Notice as Student
- [ ] Logout and login as student1@test.com
- [ ] Should see:
  - Welcome Notice ✅
  - Important Document ✅
  - S.E. CSE Notice ✅
  - Holiday Announcement ✅
- [ ] Should NOT see:
  - Staff Meeting ❌ (staff only)
- [ ] View counts shown (👁️ icon)
- [ ] Comment counts shown (💬 icon)

### Test 14: View Full Notice Details
- [ ] Click on "Welcome Notice"
- [ ] Modal opens with:
  - Full title
  - Full content
  - Sender name and email
  - Posted date
  - View count
  - Comment count
- [ ] Attachments section (if any)
- [ ] Comments section
- [ ] Comment form

### Test 15: Download Attachment
- [ ] Click on "Important Document" notice
- [ ] See attachment in "📎 Attachments" section
- [ ] Click on attachment filename
- [ ] File downloads or opens in new tab
- [ ] File size displayed correctly

### Test 16: Edit Notice (Creator Only)
- [ ] Login as admin
- [ ] Open any notice created by admin
- [ ] "Edit Notice" button visible
- [ ] Click "Edit Notice"
- [ ] Modal opens with current title and content
- [ ] Change title to: "Updated Welcome Notice"
- [ ] Change content
- [ ] Click "Update Notice"
- [ ] Success message shown
- [ ] Notice updated
- [ ] "Updated" timestamp shown

### Test 17: Delete Notice (Creator)
- [ ] Open a notice you created
- [ ] "Delete Notice" button visible
- [ ] Click "Delete Notice"
- [ ] Confirmation dialog appears
- [ ] Click OK
- [ ] Success message shown
- [ ] Notice removed from list

### Test 18: View Tracking
- [ ] Login as admin
- [ ] Create a new notice for all students
- [ ] Logout and login as student
- [ ] Open the notice
- [ ] Logout and login as admin
- [ ] Open the same notice
- [ ] "👁️ Viewed By" section visible
- [ ] Shows "Test Student 1 (Student)" with timestamp
- [ ] View count incremented

---

## 💬 Comments Tests

### Test 19: Add Comment
- [ ] Login as student
- [ ] Open any notice
- [ ] Scroll to comments section
- [ ] Type: "This is my first comment"
- [ ] Click "Post Comment"
- [ ] Comment appears immediately
- [ ] Shows your name and role
- [ ] Shows timestamp

### Test 20: Add Multiple Comments
- [ ] Add another comment: "Second comment"
- [ ] Both comments visible
- [ ] Ordered by time (oldest first)

### Test 21: Edit Own Comment
- [ ] Find your comment
- [ ] "Edit" button visible
- [ ] Click "Edit"
- [ ] Prompt appears with current text
- [ ] Change to: "This is my edited comment"
- [ ] Click OK
- [ ] Comment updated
- [ ] "(edited)" label appears

### Test 22: Delete Own Comment
- [ ] Find your comment
- [ ] "Delete" button visible
- [ ] Click "Delete"
- [ ] Confirmation dialog
- [ ] Click OK
- [ ] Comment removed

### Test 23: Comment Permissions
- [ ] Login as different student (create new if needed)
- [ ] Open notice with comments
- [ ] Other users' comments visible
- [ ] No "Edit" button on others' comments
- [ ] No "Delete" button on others' comments (unless admin)

### Test 24: Admin Delete Any Comment
- [ ] Login as admin
- [ ] Open notice with comments
- [ ] "Delete" button visible on ALL comments
- [ ] Delete a student's comment
- [ ] Comment removed successfully

---

## 👥 User Management Tests (Admin Only)

### Test 25: View All Users
- [ ] Login as admin
- [ ] Go to "User Management" section
- [ ] Table shows all active users
- [ ] Columns: Name, Email, Role, Class, Roll No, Actions
- [ ] Admin users don't have delete button

### Test 26: View Pending Approvals
- [ ] Register a new student (student2@test.com)
- [ ] Verify with OTP
- [ ] Login as admin
- [ ] Go to "Pending Approvals"
- [ ] New student visible in list
- [ ] Shows registration date

### Test 27: Approve User
- [ ] Click "Approve" on pending user
- [ ] Success message
- [ ] User moves to active users list
- [ ] User can now login

### Test 28: Reject User
- [ ] Register another student (student3@test.com)
- [ ] Verify with OTP
- [ ] Login as admin
- [ ] Go to "Pending Approvals"
- [ ] Click "Reject" (Delete button)
- [ ] Confirmation dialog
- [ ] Click OK
- [ ] User removed from system
- [ ] Cannot login with those credentials

### Test 29: Delete Active User
- [ ] Go to "User Management"
- [ ] Find a student user
- [ ] Click "Delete"
- [ ] Confirmation dialog
- [ ] Click OK
- [ ] User removed
- [ ] User cannot login anymore

### Test 30: Cannot Delete Admin
- [ ] Try to find delete button on admin user
- [ ] No delete button visible
- [ ] Admin accounts protected

---

## 🎭 Role-Based Access Tests

### Test 31: Student Cannot Create Notices
- [ ] Login as student
- [ ] No "Create Notice" menu item
- [ ] Cannot access staff-dashboard.html directly
- [ ] Cannot access admin-dashboard.html directly

### Test 32: Student Cannot See Staff-Only Notices
- [ ] Login as admin
- [ ] Create staff-only notice
- [ ] Logout and login as student
- [ ] Staff-only notice NOT visible in list

### Test 33: Student Cannot Edit Others' Notices
- [ ] Open any notice
- [ ] No "Edit Notice" button (unless you created it)

### Test 34: Student Cannot Delete Others' Notices
- [ ] Open any notice
- [ ] No "Delete Notice" button (unless you created it)

### Test 35: Student Cannot See Viewer List
- [ ] Open any notice
- [ ] No "👁️ Viewed By" section visible
- [ ] Only see view count number

### Test 36: Staff Can See All Student Notices
- [ ] Create a staff account (manually in database or add staff registration)
- [ ] Login as staff
- [ ] Can see all student-targeted notices
- [ ] Can see staff-only notices
- [ ] Cannot see notices not targeted to staff

### Test 37: Staff Can Create Notices
- [ ] Login as staff
- [ ] "Create Notice" menu visible
- [ ] Can create notices
- [ ] Can upload attachments
- [ ] Can target different audiences

### Test 38: Staff Can Edit Own Notices Only
- [ ] Create a notice as staff
- [ ] Can edit own notice
- [ ] Open admin's notice
- [ ] No "Edit" button on admin's notice

### Test 39: Staff Can Delete Own Notices Only
- [ ] Can delete own notices
- [ ] Cannot delete admin's notices
- [ ] Cannot delete other staff's notices

### Test 40: Staff Can See Viewer Lists
- [ ] Open any notice
- [ ] "👁️ Viewed By" section visible
- [ ] Shows who viewed with details

### Test 41: Admin Can Do Everything
- [ ] Login as admin
- [ ] Can see all notices
- [ ] Can edit own notices
- [ ] Can delete ANY notice
- [ ] Can see all viewer lists
- [ ] Can manage all users
- [ ] Can approve/reject registrations

---

## 📊 Statistics & Counts Tests

### Test 42: View Count Accuracy
- [ ] Create new notice as admin
- [ ] Note initial view count (should be 0)
- [ ] Open notice (view count = 1)
- [ ] Close and reopen (still 1, no duplicate)
- [ ] Login as different user
- [ ] Open same notice (view count = 2)
- [ ] Verify count is accurate

### Test 43: Comment Count Accuracy
- [ ] Create new notice
- [ ] Note comment count (should be 0)
- [ ] Add 1 comment (count = 1)
- [ ] Add 2 more comments (count = 3)
- [ ] Delete 1 comment (count = 2)
- [ ] Verify count updates in list view

### Test 44: View Count in List
- [ ] Go to notices list
- [ ] Each notice shows 👁️ icon with number
- [ ] Numbers match actual views

### Test 45: Comment Count in List
- [ ] Go to notices list
- [ ] Each notice shows 💬 icon with number
- [ ] Numbers match actual comments

---

## 🔒 Security Tests

### Test 46: SQL Injection Prevention
- [ ] Try login with: admin' OR '1'='1
- [ ] Should fail (not bypass authentication)
- [ ] Try in comment: '; DROP TABLE users; --
- [ ] Should be saved as text, not executed

### Test 47: XSS Prevention
- [ ] Try comment with: <script>alert('XSS')</script>
- [ ] Should display as text, not execute
- [ ] Try notice title with HTML tags
- [ ] Should be sanitized

### Test 48: Session Security
- [ ] Login as student
- [ ] Copy session cookie
- [ ] Logout
- [ ] Try to access dashboard with old cookie
- [ ] Should redirect to login

### Test 49: File Upload Security
- [ ] Try uploading .exe file
- [ ] Should be rejected
- [ ] Try uploading 10MB file
- [ ] Should be rejected (max 5MB)
- [ ] Try uploading .php file
- [ ] Should be rejected

### Test 50: Direct URL Access
- [ ] Logout completely
- [ ] Try accessing: /student-dashboard.html
- [ ] Should redirect to login
- [ ] Try accessing: /admin-dashboard.html
- [ ] Should redirect to login
- [ ] Try accessing: /api/admin/users.php
- [ ] Should return unauthorized error

---

## 📧 Email Tests (If Configured)

### Test 51: Registration OTP Email
- [ ] Configure email in config.php
- [ ] Register new student
- [ ] Check email inbox
- [ ] OTP received
- [ ] OTP is 6 digits
- [ ] Email has proper formatting

### Test 52: Password Reset OTP Email
- [ ] Use forgot password
- [ ] Check email inbox
- [ ] OTP received
- [ ] Different from registration OTP
- [ ] Email has proper formatting

### Test 53: OTP Expiration
- [ ] Request OTP
- [ ] Wait 6 minutes
- [ ] Try to use OTP
- [ ] Should show "Invalid or expired OTP"

### Test 54: OTP One-Time Use
- [ ] Request OTP
- [ ] Use OTP successfully
- [ ] Try to use same OTP again
- [ ] Should fail

---

## 🎨 UI/UX Tests

### Test 55: Responsive Design
- [ ] Resize browser window
- [ ] Check mobile view (< 768px)
- [ ] All elements visible
- [ ] No horizontal scroll
- [ ] Buttons accessible

### Test 56: Form Validation
- [ ] Try submitting empty forms
- [ ] Required field errors shown
- [ ] Email format validated
- [ ] Password length validated
- [ ] Password match validated

### Test 57: Loading States
- [ ] Check for loading indicators
- [ ] Forms disable during submission
- [ ] No double-submission possible

### Test 58: Error Messages
- [ ] All errors display clearly
- [ ] Success messages display clearly
- [ ] Messages auto-dismiss or closeable

### Test 59: Navigation
- [ ] All links work
- [ ] Back button works
- [ ] Logout works from all pages
- [ ] Breadcrumbs/navigation clear

### Test 60: Modal Functionality
- [ ] Modals open correctly
- [ ] Close button works
- [ ] Click outside to close works
- [ ] Escape key closes modal

---

## 🐛 Edge Cases & Error Handling

### Test 61: Empty States
- [ ] New user with no notices
- [ ] Shows "No notices available"
- [ ] Notice with no comments
- [ ] Shows "No comments yet"
- [ ] Admin with no pending approvals
- [ ] Shows "No pending approvals"

### Test 62: Long Content
- [ ] Create notice with very long title (500 chars)
- [ ] Displays correctly
- [ ] Create notice with very long content (5000 chars)
- [ ] Displays correctly with scrolling
- [ ] Add very long comment
- [ ] Displays correctly

### Test 63: Special Characters
- [ ] Use special chars in title: !@#$%^&*()
- [ ] Saves and displays correctly
- [ ] Use emojis: 😀🎉📢
- [ ] Displays correctly
- [ ] Use quotes and apostrophes
- [ ] No breaking or escaping issues

### Test 64: Concurrent Actions
- [ ] Open same notice in two tabs
- [ ] Add comment in tab 1
- [ ] Refresh tab 2
- [ ] Comment appears in tab 2
- [ ] Delete notice in tab 1
- [ ] Tab 2 shows error when trying to access

### Test 65: Database Connection Loss
- [ ] Stop MySQL in XAMPP
- [ ] Try to login
- [ ] Proper error message shown
- [ ] Start MySQL
- [ ] System recovers

---

## ✅ Final Checks

### Test 66: Browser Compatibility
- [ ] Test in Chrome
- [ ] Test in Firefox
- [ ] Test in Edge
- [ ] Test in Safari (if available)

### Test 67: Performance
- [ ] Create 50+ notices
- [ ] List loads in reasonable time
- [ ] Scrolling is smooth
- [ ] No memory leaks (check dev tools)

### Test 68: Data Integrity
- [ ] Check database after all tests
- [ ] No orphaned records
- [ ] Foreign keys intact
- [ ] Timestamps accurate

### Test 69: Cleanup
- [ ] Delete test users
- [ ] Delete test notices
- [ ] Clear test comments
- [ ] System still works

### Test 70: Documentation
- [ ] README accurate
- [ ] Installation guide works
- [ ] Email setup guide works
- [ ] All features documented

---

## 📝 Test Results Summary

**Total Tests:** 70
**Passed:** ___
**Failed:** ___
**Skipped:** ___

**Critical Issues Found:**
1. 
2. 
3. 

**Minor Issues Found:**
1. 
2. 
3. 

**Recommendations:**
1. 
2. 
3. 

**Tested By:** _______________
**Date:** _______________
**Environment:** XAMPP on Windows/Mac/Linux
**Browser:** _______________
**PHP Version:** _______________
**MySQL Version:** _______________

---

## 🎯 Production Readiness Checklist

Before deploying to production:

- [ ] All 70 tests passed
- [ ] Email configured and tested
- [ ] HTTPS/SSL enabled
- [ ] Default passwords changed
- [ ] Error display disabled
- [ ] Logging enabled
- [ ] Backups configured
- [ ] Security headers configured
- [ ] File upload limits set
- [ ] Rate limiting implemented
- [ ] Monitoring set up
- [ ] Documentation complete
- [ ] User training completed

---

**Status:** ⬜ Not Started | 🟡 In Progress | ✅ Complete

**Overall System Status:** _______________
