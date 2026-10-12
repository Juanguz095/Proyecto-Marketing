<?php
declare(strict_types=1);

date_default_timezone_set('America/Lima');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'use_strict_mode' => true,
    ]);
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function money(float|string $amount): string
{
    return 'S/ ' . number_format((float) $amount, 2);
}

function page_message(string $title, string $message, bool $success = false): void
{
    http_response_code($success ? 200 : 422);
    $safeTitle = e($title);
    $safeMessage = e($message);
    $statusLabel = $success ? 'Solicitud recibida' : 'Revisa tus datos';
    echo <<<HTML
    <!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{$safeTitle} · OnlyFive</title><link rel="stylesheet" href="assets/styles.css"></head>
    <body><main class="message-page"><section class="message-card"><a class="wordmark" href="index.php">ONLYFIVE</a>
    <span class="eyebrow">{$statusLabel}</span>
    <h1>{$safeTitle}</h1><p>{$safeMessage}</p><a class="button" href="barberia.php?b=barberia-demo#reserva">Volver a la página</a>
    </section></main></body></html>
    HTML;
    exit;
}
