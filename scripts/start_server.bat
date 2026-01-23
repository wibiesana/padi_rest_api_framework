@echo off
echo Starting Padi REST API...
echo Server will run on http://localhost:8000
echo Press Ctrl+C to stop the server
echo.
cd ../public
php -S localhost:8000
