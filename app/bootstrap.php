<?php
declare(strict_types=1);

date_default_timezone_set('America/Lima');

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

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

function verify_csrf(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function allow_session_action(string $key, int $limit, int $windowSeconds): bool
{
    $now = time();
    $recent = array_values(array_filter(
        $_SESSION['action_limits'][$key] ?? [],
        static fn ($timestamp): bool => is_int($timestamp) && $timestamp > $now - $windowSeconds
    ));
    if (count($recent) >= $limit) {
        $_SESSION['action_limits'][$key] = $recent;
        return false;
    }
    $recent[] = $now;
    $_SESSION['action_limits'][$key] = $recent;
    return true;
}

function whatsapp_url(string $phone, string $message): ?string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (strlen($digits) === 9) {
        $countryCode = preg_replace('/\D+/', '', (string) (getenv('WHATSAPP_COUNTRY_CODE') ?: '51')) ?? '51';
        $digits = $countryCode . $digits;
    }
    if (strlen($digits) < 7 || strlen($digits) > 15) {
        return null;
    }

    return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
}

function default_privacy_policy(): string
{
    return "La barbería usa el nombre y el número de teléfono que ingresas para gestionar tu solicitud de cita, comunicarse contigo sobre el horario y prestar el servicio. No ingreses información sensible en el formulario.\n\nLa barbería debe conservar los datos solo durante el tiempo necesario para gestionar la cita y cumplir las obligaciones aplicables, y protegerlos contra accesos no autorizados.\n\nPara consultar, corregir o solicitar la eliminación de tus datos, comunícate con la barbería usando sus datos de contacto. Revisa esta política con la persona responsable del negocio y ajústala a sus prácticas antes de recibir reservas reales.";
}

function privacy_policy_version(?string $policy): string
{
    $effectivePolicy = is_string($policy) && trim($policy) !== '' ? trim($policy) : default_privacy_policy();
    return 'sha256:' . substr(hash('sha256', $effectivePolicy), 0, 32);
}

function money(float|string $amount): string
{
    return 'S/ ' . number_format((float) $amount, 2);
}

function page_message(string $title, string $message, bool $success = false, ?int $statusCode = null): void
{
    http_response_code($statusCode ?? ($success ? 200 : 422));
    $safeTitle = e($title);
    $safeMessage = e($message);
    $statusLabel = $success ? 'Solicitud recibida' : 'Revisa tus datos';
    echo <<<HTML
    <!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{$safeTitle} · Solisweb</title><link rel="stylesheet" href="assets/styles.css"></head>
    <body><main class="message-page"><section class="message-card"><a class="wordmark" href="index.php">SOLISWEB</a>
    <span class="eyebrow">{$statusLabel}</span>
    <h1>{$safeTitle}</h1><p>{$safeMessage}</p><a class="button" href="barberia.php?b=barberia-demo#reserva">Volver a la página</a>
    </section></main></body></html>
    HTML;
    exit;
}
