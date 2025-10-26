<?php
/**
 * Email Configuration for Global Peace Pledge - PRODUCTION VERSION
 * 
 * SECURITY NOTE: Never commit this file to version control!
 * Add this file to your .gitignore
 */

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', '1111@globalpeacepledge.com');
define('SMTP_PASSWORD', 'xyncqbfltsefnmng'); // Replace with your Google Workspace app password
define('SMTP_FROM_EMAIL', '1111@globalpeacepledge.com');
define('SMTP_FROM_NAME', 'Global Peace Pledge');
define('SMTP_TO_EMAIL', '1111@globalpeacepledge.com');
define('SMTP_TO_NAME', 'Peace Foundation Admin');

// Security Configuration - PRODUCTION SETTINGS
define('ENABLE_RATE_LIMITING', true);
define('MAX_SUBMISSIONS_PER_IP', 3); // Stricter limit for production
define('SUBMISSION_COOLDOWN', 600); // 10 minutes between submissions

// Application Configuration - PRODUCTION SETTINGS
define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('ENABLE_DEBUG_MODE', false); // MUST be false in production

// Production Error Handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
?>