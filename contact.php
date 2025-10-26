<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load configuration
require_once 'config.php';
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Start session for rate limiting
session_start();

    // Local toggle to temporarily disable rate limiting without changing global config.
    // Set to true to re-enable local rate limiting checks: $RATE_LIMITING_ACTIVE = true;
    $RATE_LIMITING_ACTIVE = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting check
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $current_time = time();
    
    if ($RATE_LIMITING_ACTIVE && ENABLE_RATE_LIMITING) {
        // Check for submission cooldown
        if (isset($_SESSION['last_submission']) && 
            ($current_time - $_SESSION['last_submission']) < SUBMISSION_COOLDOWN) {
            $wait_time = SUBMISSION_COOLDOWN - ($current_time - $_SESSION['last_submission']);
            echo json_encode([
                'type' => 'error', 
                'text' => "Please wait {$wait_time} seconds before submitting again."
            ]);
            exit;
        }
        
        // Check hourly rate limit
        if (!isset($_SESSION['submissions'])) $_SESSION['submissions'] = [];
        $_SESSION['submissions'] = array_filter($_SESSION['submissions'], function($time) use ($current_time) {
            return ($current_time - $time) < 3600; // Keep only last hour
        });
        
        if (count($_SESSION['submissions']) >= MAX_SUBMISSIONS_PER_IP) {
            echo json_encode([
                'type' => 'error', 
                'text' => 'Too many submissions. Please try again in an hour.'
            ]);
            exit;
        }
    }

    // Sanitize and validate input
    $firstName    = filter_var($_POST["firstName"] ?? '', FILTER_SANITIZE_STRING);
    $lastName     = filter_var($_POST["lastName"] ?? '', FILTER_SANITIZE_STRING);
    $user_Name    = trim($firstName . ' ' . $lastName);
    $user_Email   = filter_var($_POST["email"] ?? '', FILTER_SANITIZE_EMAIL);
    $country      = filter_var($_POST["country"] ?? '', FILTER_SANITIZE_STRING);
    $user_Subject = 'Global Peace Pledge Submission from ' . $user_Name;
    $user_Message = "A new Global Peace Pledge has been submitted:\n\nName: {$user_Name}\nCountry: {$country}\nEmail: {$user_Email}\n\nThis person has pledged for global peace.";

    if (strlen($firstName) < 1 || strlen($lastName) < 1) {
        echo json_encode(['type' => 'error', 'text' => 'First name and last name are required!']);
        exit;
    }

    if (strlen($user_Name) < 2) {
        echo json_encode(['type' => 'error', 'text' => 'Full name is too short!']);
        exit;
    }

    if (!filter_var($user_Email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['type' => 'error', 'text' => 'Please enter a valid email address!']);
        exit;
    }

    if (strlen($country) < 2) {
        echo json_encode(['type' => 'error', 'text' => 'Please select your country!']);
        exit;
    }

    // Use an explicit HTML wrapper and charset so emoji (e.g. 🕊️) render correctly in mail clients
    $message_Body = "<!DOCTYPE html>\n<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" /></head><body>\n"
        . "<h2>🕊️ Global Peace Pledge Submission</h2>\n"
        . "<p><strong>Name:</strong> {$user_Name}</p>\n"
        . "<p><strong>Email:</strong> {$user_Email}</p>\n"
        . "<p><strong>Country:</strong> {$country}</p>\n"
        . "<p><strong>Date:</strong> " . date('F j, Y \\a\\t g:i A') . "</p>\n"
        . "<p><strong>IP Address:</strong> {$client_ip}</p>\n"
        . "<hr>\n"
        . "<p>{$user_Message}</p>\n"
        . "<hr>\n"
        . "<p><em>This submission was made through the Global Peace Pledge website.</em></p>\n"
        . "</body></html>";

    if (ENABLE_EMAIL_NOTIFICATIONS) {
        // Validate SMTP configuration
        if (empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
            echo json_encode([
                'type' => 'error', 
                'text' => 'Email service is not properly configured. Please contact support.'
            ]);
            exit;
        }
        
        try {
            // Send email to user
            $userMail = new PHPMailer(true);
            // Ensure UTF-8 charset so emoji characters render correctly
            $userMail->CharSet = 'UTF-8';
            // Use base64 encoding for reliable transmission of non-ASCII characters
            $userMail->Encoding = 'base64';
            $userMail->isSMTP();
            $userMail->Host       = SMTP_HOST;
            $userMail->SMTPAuth   = true;
            $userMail->Username   = SMTP_USERNAME;
            $userMail->Password   = SMTP_PASSWORD;
            $userMail->SMTPSecure = (SMTP_SECURE === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $userMail->Port       = SMTP_PORT;

            // Debug mode
            if (ENABLE_DEBUG_MODE) {
                $userMail->SMTPDebug = 2;
                $userMail->Debugoutput = 'html';
            }

            // User email content
            $userMail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $userMail->addAddress($user_Email, $user_Name);
            $userMail->isHTML(true);
            $userMail->Subject = 'Thank You for Taking the Global Peace Pledge 🕊️';
            
            $userEmailBody = "
               
                <p>Dear {$user_Name},</p>

                 <h2>Vanakkam, Santhosham and Peace be with you!</h2>
                <p>On behalf of <strong>His Holiness GuruMahan</strong> and the entire <strong>Universal Peace Foundation</strong> family, we extend our heartfelt gratitude for adding your voice to the <strong>Global Peace Pledge</strong> on this momentous occasion.Your digital signature represents more than just support—it embodies hope, compassion, and the collective power of humanity working toward a more peaceful tomorrow. By joining millions of hearts and minds from every corner of the world, you have become an integral part of a sacred movement for global harmony.</p>
                
                <p>Your digital signature represents more than just support—it embodies hope, compassion, and the collective power of humanity working toward a more peaceful tomorrow. By joining millions of hearts and minds from every corner of the world, you have become an integral part of a sacred movement for global harmony.</p>

                <strong>Your Commitment Matters</strong>
                <p>As His Holiness GuruMahan reminds us:</p>

                <p><strong>\"Peace is not merely the absence of conflict, but the presence of love, understanding, and compassion in every heart. When we unite in the spirit of universal brotherhood, we become instruments of divine harmony.\"</strong></p>

                <p>Your participation in the Global Peace Day 2025 celebration in Pondicherry on November 11, 2025, marks a significant milestone in our collective journey toward world peace.</p>
                <Strong>Your Certificate of Appreciation</strong>

                <p>We trust you downloaded your personalized Certificate of Appreciation from our website.</p>
                <p>The beautifully designed certificate you downloaded is perfect for framing and display—a lasting reminder of your commitment to world peace that can inspire others in your home or workplace.</p>

                <strong>Spread the Message</strong>
                <p>We encourage you to share this meaningful initiative with your family, friends, and community. Together, we can amplify the message that a better, more peaceful world is possible, and it starts with each one of us.
                </p>
                <p>Thank you once again for being a beacon of peace and universal brotherhood.</p>


                <p>With Gratitude and Blessings,<br>
                <strong>The Universal Peace Foundation Team</strong><br>
                Under the guidance of <strong>His Holiness GuruMahan</strong><br>
                Global Peace Day 2025 | Pondicherry, India<br>
                <a href=\"https://www.universalpeacefoundation.org\" target=\"_blank\">https://universalpeacefoundation.org</a></p>

                <hr>
                <p><em>This email was sent from the Global Peace Pledge website. Keep this email as a record of your pledge.</em></p>
            ";
            
            $userMail->Body = $userEmailBody;
            
            $userMail->send();
            
            // Send notification to admin
            $adminMail = new PHPMailer(true);
            // Ensure UTF-8 charset for admin notification as well
            $adminMail->CharSet = 'UTF-8';
            $adminMail->Encoding = 'base64';
            $adminMail->isSMTP();
            $adminMail->Host       = SMTP_HOST;
            $adminMail->SMTPAuth   = true;
            $adminMail->Username   = SMTP_USERNAME;
            $adminMail->Password   = SMTP_PASSWORD;
            $adminMail->SMTPSecure = (SMTP_SECURE === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $adminMail->Port       = SMTP_PORT;

            $adminMail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $adminMail->addAddress(SMTP_TO_EMAIL, SMTP_TO_NAME);
            $adminMail->addReplyTo($user_Email, $user_Name);
            $adminMail->isHTML(true);
            $adminMail->Subject = $user_Subject;
            $adminMail->Body = $message_Body;
            
            $adminMail->send();
            
            // Record successful submission for rate limiting
            if ($RATE_LIMITING_ACTIVE && ENABLE_RATE_LIMITING) {
                $_SESSION['last_submission'] = $current_time;
                $_SESSION['submissions'][] = $current_time;
            }

            echo json_encode([
                'type' => 'success', 
                'text' => 'Thank you for your pledge for global peace! A confirmation email has been sent to your email address.'
            ]);
        } catch (Exception $e) {
            // Log the error for debugging
            error_log("Email sending failed: " . $e->getMessage());
            
            $error_message = ENABLE_DEBUG_MODE ? 'Mailer Error: ' . $e->getMessage() : 'Sorry, there was an error sending your pledge. Please check your internet connection and try again.';
            echo json_encode(['type' => 'error', 'text' => $error_message]);
        }
    } else {
        echo json_encode([
            'type' => 'success', 
            'text' => 'Thank you for your pledge for global peace! (Email notifications are currently disabled)'
        ]);
    }
} else {
    echo json_encode(['type' => 'error', 'text' => 'Invalid request method.']);
}