# 🔧 init_app.bat - Troubleshooting Guide

## ✅ Fixes Implemented

### Issue: Script crashes after selecting MariaDB

**Root Cause:**

- PowerShell commands failed due to lack of error handling
- Empty passwords were not correctly handled
- No validation for `.env.example` file

**Fixes Applied:**

1. ✅ **Error Handling for PowerShell**
   - Added `2>nul` to suppress PowerShell errors
   - Added `if errorlevel 1` checks
   - Added proper exit codes

2. ✅ **Empty Password Handling**

   ```batch
   if "!DB_PASS!"=="" (
       powershell -Command "..." 'DB_PASSWORD=' ...
   ) else (
       powershell -Command "..." 'DB_PASSWORD=!DB_PASS!' ...
   )
   ```

3. ✅ **File Validation**
   - Checks for `.env.example` existence before starting
   - Verifies copy operation success
   - Displays clear error messages

4. ✅ **JWT Generation Error Handling**
   - Checks if PHP is available
   - Verifies JWT secret was generated
   - Displays helpful error messages upon failure

---

## 🐛 Common Issues & Solutions

### 1. Script Stops After Database Selection

**Symptoms:**

- Script exits after choosing MySQL/MariaDB/PostgreSQL
- No error message shown

**Solution:**
✅ **FIXED!** Updated script now has proper error handling.

If still occurs:

1. Check if `.env.example` exists
2. Ensure PowerShell is available
3. Run as Administrator if permission issues persist

---

### 2. "ERROR: .env.example file not found!"

**Cause:** Missing `.env.example` file

**Solution:**

1. Ensure you're in the project root directory
2. Download `.env.example` from repository
3. Or create manually based on template

---

### 3. "ERROR: Failed to generate JWT secret"

**Cause:** PHP not installed or not in PATH

**Solution:**

**Check PHP:**

```bash
php -v
```

**If not found:**

1. Install PHP from https://windows.php.net/download/
2. Add PHP to system PATH
3. Restart terminal

**Manual JWT Generation:**

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Then paste into `.env`:

```
JWT_SECRET=<generated_key>
```

---

### 4. "Error updating DB_CONNECTION"

**Cause:** PowerShell execution policy or `.env` file locked

**Solution:**

**Check PowerShell Policy:**

```powershell
Get-ExecutionPolicy
```

**If Restricted:**

```powershell
Set-ExecutionPolicy RemoteSigned -Scope CurrentUser
```

**Or run script as Administrator**

---

### 5. Empty Password Not Accepted

**Cause:** Previous versions couldn't handle empty passwords

**Solution:**
✅ **FIXED!** Script now properly handles empty passwords.

Just press Enter for empty password:

```
Database Password [press enter for empty]: [Enter]
```

---

### 6. Migration Fails

**Symptoms:**

```
Error: SQLSTATE[HY000] [1045] Access denied
```

**Solution:**

**For MySQL/MariaDB:**

1. Check if server is running:

   ```bash
   # MySQL
   net start mysql

   # MariaDB
   net start mariadb
   ```

2. Check credentials:

   ```bash
   mysql -u root -p
   ```

3. Create database manually:
   ```sql
   CREATE DATABASE rest_api_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

**For PostgreSQL:**

1. Check if server is running:

   ```bash
   pg_ctl status
   ```

2. Test connection:

   ```bash
   psql -U postgres
   ```

3. Create database:
   ```sql
   CREATE DATABASE rest_api_db;
   ```

---

### 7. "Command completed but database not updated"

**Cause:** Migration silent failure

**Solution:**

**Check database manually:**

```bash
# MySQL/MariaDB
mysql -u root -p
USE rest_api_db;
SHOW TABLES;

# PostgreSQL
psql -U postgres -d rest_api_db
\dt

# SQLite
sqlite3 database/database.sqlite
.tables
```

**Run migration manually:**

```bash
php scripts/migrate.php migrate
```

---

## 🔍 Debug Mode

Want to see what's happening?

**Add echo statements:**

Open `init_app.bat` and add after the line that fails:

```batch
echo DEBUG: DB_DRIVER=!DB_DRIVER!
echo DEBUG: DB_HOST=!DB_HOST!
echo DEBUG: DB_NAME=!DB_NAME!
pause
```

---

## ✅ Verification Checklist

Before running `init_app.bat`:

- [ ] `.env.example` exists in project root
- [ ] PHP is installed and in PATH (`php -v` works)
- [ ] PowerShell is available (`pwsh` or `powershell` works)
- [ ] Database server is running (MySQL/MariaDB/PostgreSQL)
- [ ] Permissions to create database are granted
- [ ] You are in the project root directory

---

## 🆘 Still Having Issues?

### Option 1: Manual Setup

Follow: [docs/INIT_APP_GUIDE.md](INIT_APP_GUIDE.md) (Manual Setup section)

### Option 2: Review Logs

Check PowerShell errors:

```batch
:: Add to init_app.bat for debugging
powershell -Command "the command" 2>&1 | tee log.txt
```

### Option 3: Step-by-Step

Run commands one by one instead of using batch script:

1. `copy .env.example .env`
2. Edit `.env` manually
3. `php -r "echo bin2hex(random_bytes(32));"` → Copy to JWT_SECRET
4. `php scripts/migrate.php migrate`
5. `php scripts/generate.php crud-all --write`

---

## 💡 Tips

1. **Run as Administrator** if you have permission issues
2. **Use SQLite** for quick testing (no database server needed)
3. **Check PHP version**: Minimum PHP 7.4 recommended
4. **Empty password OK** for local development (not for production!)
5. **Read error messages** carefully - they're now more helpful

---

**Script should work perfectly now!** 🎉

If you still encounter issues, please:

1. Note the exact error message
2. Check which step failed (1/7 through 7/7)
3. Try manual setup as fallback
