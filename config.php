<?php
/**
 * Email Configuration for Global Peace Pledge
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

// Security Configuration
define('ENABLE_RATE_LIMITING', true);
define('MAX_SUBMISSIONS_PER_IP', 5); // Max submissions per IP per hour
define('SUBMISSION_COOLDOWN', 300); // 5 minutes between submissions from same IP

// Application Configuration
define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('ENABLE_DEBUG_MODE', false); // Set to true for debugging, false for production
?>