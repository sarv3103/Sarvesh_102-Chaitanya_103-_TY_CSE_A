@echo off
echo ========================================
echo  CampusChrono - Delete Sensitive Files
echo ========================================
echo.
echo This will delete files containing your Gmail credentials
echo These files are NOT needed for the project to work
echo.
pause

echo.
echo Deleting files...
echo.

REM Delete HTML files with credentials
if exist "START-HERE-NOW.html" (
    del "START-HERE-NOW.html"
    echo [DELETED] START-HERE-NOW.html
)
if exist "START_HERE_FINAL.html" (
    del "START_HERE_FINAL.html"
    echo [DELETED] START_HERE_FINAL.html
)

REM Delete TXT files with credentials
if exist "FINAL-SETUP-INSTRUCTIONS.txt" (
    del "FINAL-SETUP-INSTRUCTIONS.txt"
    echo [DELETED] FINAL-SETUP-INSTRUCTIONS.txt
)

REM Delete MD files with credentials
if exist "EMAIL_SETUP.md" (
    del "EMAIL_SETUP.md"
    echo [DELETED] EMAIL_SETUP.md
)
if exist "DEPLOYMENT_READY.md" (
    del "DEPLOYMENT_READY.md"
    echo [DELETED] DEPLOYMENT_READY.md
)
if exist "REGISTRATION_ENHANCEMENTS_COMPLETE.md" (
    del "REGISTRATION_ENHANCEMENTS_COMPLETE.md"
    echo [DELETED] REGISTRATION_ENHANCEMENTS_COMPLETE.md
)
if exist "FINAL_FEATURES_COMPLETE.md" (
    del "FINAL_FEATURES_COMPLETE.md"
    echo [DELETED] FINAL_FEATURES_COMPLETE.md
)

REM Delete PHP test files with credentials
if exist "FINAL_FIX.php" (
    del "FINAL_FIX.php"
    echo [DELETED] FINAL_FIX.php
)
if exist "COMPLETE_FIX_AND_TEST.php" (
    del "COMPLETE_FIX_AND_TEST.php"
    echo [DELETED] COMPLETE_FIX_AND_TEST.php
)
if exist "test-email-final.php" (
    del "test-email-final.php"
    echo [DELETED] test-email-final.php
)
if exist "test-email-simple.php" (
    del "test-email-simple.php"
    echo [DELETED] test-email-simple.php
)
if exist "test-phpmailer-email.php" (
    del "test-phpmailer-email.php"
    echo [DELETED] test-phpmailer-email.php
)

echo.
echo ========================================
echo  DONE! Sensitive files deleted
echo ========================================
echo.
echo Next steps:
echo 1. Open GitHub Desktop
echo 2. You'll see these files as "deleted"
echo 3. Commit with message: "Remove files with credentials"
echo 4. Push to GitHub
echo.
echo Your project will still work perfectly!
echo These were just documentation/test files.
echo.
pause
