<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$config = require __DIR__ . '/config.php';

if ($config['smtp_host'] === '' || $config['smtp_username'] === '' || $config['smtp_password'] === '' || $config['recaptcha_secret'] === '') {
    http_response_code(500);
    error_log('Contact form is missing SMTP or reCAPTCHA configuration.');
    exit('The contact form is not configured.');
}

$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'profile_contact_' . hash('sha256', $clientIp) . '.json';
$now = time();
$rateData = is_file($rateFile) ? json_decode((string) file_get_contents($rateFile), true) : [];
$recentRequests = array_values(array_filter(is_array($rateData) ? $rateData : [], static fn($timestamp): bool => is_int($timestamp) && $timestamp > $now - 3600));
if (count($recentRequests) >= 3) {
    http_response_code(429);
    exit('Too many requests. Please try again later.');
}
$recentRequests[] = $now;
file_put_contents($rateFile, json_encode($recentRequests), LOCK_EX);

if (!empty($_POST['website'] ?? '')) {
    http_response_code(400);
    exit('Invalid submission.');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$project = trim((string) ($_POST['project'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$token = trim((string) ($_POST['recaptcha_token'] ?? ''));
$name = preg_replace('/[\r\n]+/', ' ', $name) ?? '';
$project = preg_replace('/[\r\n]+/', ' ', $project) ?? '';

if (
    $token === '' || $name === '' || $project === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || strlen($name) > 100 || strlen($project) > 80 || strlen($message) > 4000
) {
    http_response_code(422);
    exit('Please complete the form with valid information.');
}

$recaptchaContext = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => http_build_query(['secret' => $config['recaptcha_secret'], 'response' => $token, 'remoteip' => $clientIp]), 'timeout' => 8]]);
$recaptchaResponse = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $recaptchaContext);
$recaptcha = json_decode((string) $recaptchaResponse, true);
if (!is_array($recaptcha) || empty($recaptcha['success']) || ($recaptcha['action'] ?? '') !== 'contact_form' || (float) ($recaptcha['score'] ?? 0) < 0.5) {
    http_response_code(400);
    exit('Spam verification failed. Please try again.');
}

require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_username'];
    $mail->Password = $config['smtp_password'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port = $config['smtp_port'];
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config['mail_from'], 'Website Contact');
    $mail->addAddress($config['mail_to']);
    $mail->addReplyTo($email, $name);
    $mail->Subject = '[Website] ' . $project;
    $mail->Body = "Name: {$name}\nEmail: {$email}\nProject: {$project}\nIP: {$clientIp}\n\n{$message}";
    $mail->send();
    exit('OK');
} catch (Exception $exception) {
    error_log('Contact mail failed: ' . $mail->ErrorInfo);
    http_response_code(500);
    exit('Unable to send your message right now.');
}
