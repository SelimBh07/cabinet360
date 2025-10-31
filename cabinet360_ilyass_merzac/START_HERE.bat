@echo off
echo ========================================
echo   CABINET360 - QUICK START SCRIPT
echo ========================================
echo.
echo This script will help you start Cabinet360
echo.
echo STEP 1: Start XAMPP Control Panel
echo ----------------------------------------
echo Please start XAMPP and enable:
echo   - Apache
echo   - MySQL
echo.
pause
echo.
echo STEP 2: Create Database
echo ----------------------------------------
echo Opening phpMyAdmin in browser...
start http://localhost/phpmyadmin
echo.
echo In phpMyAdmin:
echo 1. Click "New" to create database
echo 2. Database name: lexmanage
echo 3. Encoding: utf8mb4_unicode_ci
echo 4. Click "Create"
echo 5. Select "lexmanage" database
echo 6. Go to "Import" tab
echo 7. Choose file: database.sql (in this folder)
echo 8. Click "Go"
echo.
pause
echo.
echo STEP 3: Open Cabinet360
echo ----------------------------------------
echo Opening Cabinet360 in browser...
start http://localhost/Cabinet360/login.php
echo.
echo ========================================
echo   LOGIN CREDENTIALS
echo ========================================
echo Username: admin
echo Password: admin123
echo ========================================
echo.
echo Enjoy using Cabinet360!
echo.
pause

