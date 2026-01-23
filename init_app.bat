@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

:: Colors
set "GREEN=[32m"
set "YELLOW=[33m"
set "RED=[31m"
set "BLUE=[34m"
set "RESET=[0m"

cls
echo.
echo %BLUE%╔════════════════════════════════════════════════════════════════╗%RESET%
echo %BLUE%║             Padi REST API - Initialization Script              ║%RESET%
echo %BLUE%║                        Version 2.0                             ║%RESET%
echo %BLUE%╚════════════════════════════════════════════════════════════════╝%RESET%
echo.

:: Step 1: Copy .env.example to .env
echo %YELLOW%[1/7] Checking environment file...%RESET%

:: Check if .env.example exists
if not exist .env.example (
    echo %RED%ERROR: .env.example file not found!%RESET%
    echo Please ensure .env.example exists in the project root.
    pause
    exit /b 1
)

if exist .env (
    echo %YELLOW%File .env already exists.%RESET%
    set /p "overwrite=Do you want to overwrite it? (y/n): "
    if /i "!overwrite!"=="y" (
        copy /y .env.example .env >nul
        if errorlevel 1 (
            echo %RED%ERROR: Failed to copy .env.example%RESET%
            pause
            exit /b 1
        )
        echo %GREEN%✓ .env file updated from .env.example%RESET%
    ) else (
        echo %YELLOW%→ Skipping .env creation%RESET%
    )
) else (
    copy .env.example .env >nul
    if errorlevel 1 (
        echo %RED%ERROR: Failed to create .env file%RESET%
        pause
        exit /b 1
    )
    echo %GREEN%✓ .env file created from .env.example%RESET%
)
echo.

:: Step 2: Choose Database Driver
echo %YELLOW%[2/7] Select Database Driver%RESET%
echo.
echo Please select your database:
echo   1. MySQL (Default)
echo   2. MariaDB
echo   3. PostgreSQL
echo   4. SQLite
echo.
set /p "db_choice=Enter your choice (1-4) [1]: "
if "!db_choice!"=="" set db_choice=1

set "DB_DRIVER=mysql"
set "DB_PORT=3306"
if "!db_choice!"=="1" (
    set "DB_DRIVER=mysql"
    set "DB_PORT=3306"
    echo %GREEN%✓ MySQL selected%RESET%
)
if "!db_choice!"=="2" (
    set "DB_DRIVER=mysql"
    set "DB_PORT=3306"
    echo %GREEN%✓ MariaDB selected (using MySQL driver)%RESET%
)
if "!db_choice!"=="3" (
    set "DB_DRIVER=pgsql"
    set "DB_PORT=5432"
    echo %GREEN%✓ PostgreSQL selected%RESET%
)
if "!db_choice!"=="4" (
    set "DB_DRIVER=sqlite"
    echo %GREEN%✓ SQLite selected%RESET%
)
echo.

:: Step 3: Database Configuration
echo %YELLOW%[3/7] Database Configuration%RESET%
echo.

if "!DB_DRIVER!"=="sqlite" (
    set /p "DB_PATH=SQLite database path [database/database.sqlite]: "
    if "!DB_PATH!"=="" set DB_PATH=database/database.sqlite
    
    :: Create database directory
    if not exist "database" mkdir database
    
    echo %GREEN%✓ SQLite will use: !DB_PATH!%RESET%
) else (
    set /p "DB_HOST=Database Host [localhost]: "
    if "!DB_HOST!"=="" set DB_HOST=localhost
    
    set /p "DB_PORT_INPUT=Database Port [!DB_PORT!]: "
    if not "!DB_PORT_INPUT!"=="" set DB_PORT=!DB_PORT_INPUT!
    
    set /p "DB_NAME=Database Name [rest_api_db]: "
    if "!DB_NAME!"=="" set DB_NAME=rest_api_db
    
    set /p "DB_USER=Database Username [root]: "
    if "!DB_USER!"=="" set DB_USER=root
    
    set /p "DB_PASS=Database Password [press enter for empty]: "
    
    echo %GREEN%✓ Database configured%RESET%
    echo   Host: !DB_HOST!
    echo   Port: !DB_PORT!
    echo   Database: !DB_NAME!
    echo   Username: !DB_USER!
)
echo.

:: Step 4: Update .env file
echo %YELLOW%[4/7] Updating .env file...%RESET%

:: Update DB_CONNECTION
powershell -Command "(Get-Content .env) -replace '^DB_CONNECTION=.*', 'DB_CONNECTION=!DB_DRIVER!' | Set-Content .env" 2>nul
if errorlevel 1 (
    echo %RED%Error updating DB_CONNECTION%RESET%
    pause
    exit /b 1
)

if "!DB_DRIVER!"=="sqlite" (
    powershell -Command "(Get-Content .env) -replace '^SQLITE_DATABASE=.*', 'SQLITE_DATABASE=!DB_PATH!' | Set-Content .env" 2>nul
) else (
    :: Update database configuration
    powershell -Command "(Get-Content .env) -replace '^DB_HOST=.*', 'DB_HOST=!DB_HOST!' | Set-Content .env" 2>nul
    powershell -Command "(Get-Content .env) -replace '^DB_PORT=.*', 'DB_PORT=!DB_PORT!' | Set-Content .env" 2>nul
    powershell -Command "(Get-Content .env) -replace '^DB_DATABASE=.*', 'DB_DATABASE=!DB_NAME!' | Set-Content .env" 2>nul
    powershell -Command "(Get-Content .env) -replace '^DB_USERNAME=.*', 'DB_USERNAME=!DB_USER!' | Set-Content .env" 2>nul
    
    :: Handle empty password
    if "!DB_PASS!"=="" (
        powershell -Command "(Get-Content .env) -replace '^DB_PASSWORD=.*', 'DB_PASSWORD=' | Set-Content .env" 2>nul
    ) else (
        powershell -Command "(Get-Content .env) -replace '^DB_PASSWORD=.*', 'DB_PASSWORD=!DB_PASS!' | Set-Content .env" 2>nul
    )
)

echo %GREEN%✓ .env file updated%RESET%
echo.

:: Step 5: Generate JWT Secret
echo %YELLOW%[5/7] Generating JWT Secret...%RESET%

for /f %%i in ('php -r "echo bin2hex(random_bytes(32));" 2^>nul') do set JWT_SECRET=%%i

if "!JWT_SECRET!"=="" (
    echo %RED%ERROR: Failed to generate JWT secret%RESET%
    echo Please ensure PHP is installed and in PATH
    echo You can generate manually: php -r "echo bin2hex(random_bytes(32));"
    pause
    exit /b 1
)

powershell -Command "(Get-Content .env) -replace '^JWT_SECRET=.*', 'JWT_SECRET=!JWT_SECRET!' | Set-Content .env" 2>nul

echo %GREEN%✓ JWT Secret generated and saved%RESET%
echo.

:: Step 6: Run Migrations
echo %YELLOW%[6/7] Database Migrations%RESET%
echo.
echo Available migration options:
echo   1. Migrate base tables only (users)
echo   2. Migrate with examples (users, posts, comments, tags, post_tags)
echo   3. Skip migrations
echo.
set /p "migrate_choice=Enter your choice (1-3) [1]: "
if "!migrate_choice!"=="" set migrate_choice=1

if "!migrate_choice!"=="1" (
    echo.
    echo %BLUE%Running base migrations...%RESET%
    php scripts/migrate.php migrate --tables=users
    echo %GREEN%✓ Base migrations completed%RESET%
)

if "!migrate_choice!"=="2" (
    echo.
    echo %BLUE%Running all migrations with examples...%RESET%
    php scripts/migrate.php migrate
    echo %GREEN%✓ All migrations completed%RESET%
)

if "!migrate_choice!"=="3" (
    echo %YELLOW%→ Migrations skipped%RESET%
)
echo.

:: Step 7: Generate CRUD
echo %YELLOW%[7/7] CRUD Generation%RESET%
echo.
echo Do you want to generate CRUD controllers and models?
echo   1. Yes - Generate for all tables
echo   2. Yes - Select specific tables
echo   3. No - Skip generation
echo.
set /p "generate_choice=Enter your choice (1-3) [3]: "
if "!generate_choice!"=="" set generate_choice=3

if "!generate_choice!"=="1" (
    echo.
    echo %BLUE%Generating CRUD for all tables...%RESET%
    php scripts/generate.php crud-all --write --driver=!DB_DRIVER!
    echo %GREEN%✓ CRUD generation completed%RESET%
)

if "!generate_choice!"=="2" (
    echo.
    echo %BLUE%Available tables:%RESET%
    php scripts/generate.php list
    echo.
    set /p "tables=Enter table names (comma separated): "
    
    for %%t in (!tables!) do (
        echo Generating CRUD for %%t...
        php scripts/generate.php crud %%t --write --driver=!DB_DRIVER!
    )
    echo %GREEN%✓ CRUD generation completed%RESET%
)

if "!generate_choice!"=="3" (
    echo %YELLOW%→ CRUD generation skipped%RESET%
)

echo.
echo %GREEN%╔════════════════════════════════════════════════════════════════╗%RESET%
echo %GREEN%║                  Setup Completed Successfully!                 ║%RESET%
echo %GREEN%╚════════════════════════════════════════════════════════════════╝%RESET%
echo.
echo %BLUE%Next Steps:%RESET%
echo   1. Start the server:    %YELLOW%php -S localhost:8085 -t public%RESET%
echo   2. Visit:               %YELLOW%http://localhost:8085%RESET%
echo   3. API Documentation:   %YELLOW%http://localhost:8085/docs%RESET%
echo.
echo %BLUE%Quick Commands:%RESET%
echo   - List tables:          %YELLOW%php scripts/generate.php list%RESET%
echo   - Generate CRUD:        %YELLOW%php scripts/generate.php crud [table] --write%RESET%
echo   - Run migrations:       %YELLOW%php scripts/migrate.php migrate%RESET%
echo   - Rollback:            %YELLOW%php scripts/migrate.php rollback%RESET%
echo.
echo %GREEN%Happy coding! 🚀%RESET%
echo.

pause
