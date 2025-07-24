<?php
// .env.php - Environment configuration file
// Database configuration - Update these values with your actual database credentials

define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_db_name');

// Site configuration
define('SITE_URL', 'http://localhost'); // Change to your domain
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB in bytes
define('MAX_DAILY_UPLOADS', 2); // Maximum uploads per day for non-premium users

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
?>
