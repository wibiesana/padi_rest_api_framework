@echo off
REM Run the init script from the batch file directory
php "%~dp0scripts\init.php"

IF %ERRORLEVEL% EQU 0 (
	echo.
	echo Setup completed. Happy coding!
) ELSE (
	echo.
	echo Setup finished with errors. Exit code %ERRORLEVEL%.
)

pause