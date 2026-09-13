<?php
return [
    'smtp_host' => getenv('PROFILE_SMTP_HOST') ?: '',
    'smtp_username' => getenv('PROFILE_SMTP_USERNAME') ?: '',
    'smtp_password' => getenv('PROFILE_SMTP_PASSWORD') ?: '',
    'smtp_port' => (int) (getenv('PROFILE_SMTP_PORT') ?: 465),
    'smtp_secure' => getenv('PROFILE_SMTP_SECURE') ?: 'ssl',
    'mail_from' => getenv('PROFILE_MAIL_FROM') ?: 'info@kiel.my.id',
    'mail_to' => getenv('PROFILE_MAIL_TO') ?: 'info@kiel.my.id',
    'recaptcha_secret' => getenv('PROFILE_RECAPTCHA_SECRET') ?: '',
];
