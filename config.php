<?php

$envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';
$env = is_file($envFile) ? parse_ini_file($envFile, false, INI_SCANNER_RAW) : [];
$env = is_array($env) ? $env : [];

$getConfigValue = static function (string $key, string $default = '') use ($env): string {
    $serverValue = getenv($key);
    if ($serverValue !== false && $serverValue !== '') {
        return (string) $serverValue;
    }

    return isset($env[$key]) ? (string) $env[$key] : $default;
};

return [
    'smtp_host' => $getConfigValue('PROFILE_SMTP_HOST'),
    'smtp_username' => $getConfigValue('PROFILE_SMTP_USERNAME'),
    'smtp_password' => $getConfigValue('PROFILE_SMTP_PASSWORD'),
    'smtp_port' => (int) $getConfigValue('PROFILE_SMTP_PORT', '465'),
    'smtp_secure' => $getConfigValue('PROFILE_SMTP_SECURE', 'ssl'),
    'mail_from' => $getConfigValue('PROFILE_MAIL_FROM', 'info@kiel.my.id'),
    'mail_to' => $getConfigValue('PROFILE_MAIL_TO', 'info@kiel.my.id'),
    'recaptcha_secret' => $getConfigValue('PROFILE_RECAPTCHA_SECRET'),
];
