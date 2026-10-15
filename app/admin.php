<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function admin_credentials(): array
{
    $username = trim((string) getenv('ADMIN_USERNAME'));
    $passwordHash = trim((string) getenv('ADMIN_PASSWORD_HASH'));
    $hashInfo = password_get_info($passwordHash);
    $configured = $username !== ''
        && strlen($username) <= 80
        && $passwordHash !== ''
        && ($hashInfo['algoName'] ?? 'unknown') !== 'unknown';

    return [$username, $passwordHash, $configured];
}

function admin_is_authenticated(): bool
{
    $lastActivity = $_SESSION['admin_last_activity'] ?? null;
    if (!is_int($lastActivity) || $lastActivity < time() - 1800) {
        unset($_SESSION['admin_user'], $_SESSION['admin_last_activity']);
        return false;
    }

    $_SESSION['admin_last_activity'] = time();
    return isset($_SESSION['admin_user']) && is_string($_SESSION['admin_user']);
}

function require_admin(): void
{
    header('Cache-Control: no-store, private');
    header('Pragma: no-cache');
    if (!admin_is_authenticated()) {
        header('Location: index.php');
        exit;
    }
}

function admin_login_fingerprint(string $passwordHash): string
{
    $ip = is_string($_SERVER['REMOTE_ADDR'] ?? null) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    return hash_hmac('sha256', $ip, $passwordHash);
}

function admin_login_is_limited(PDO $pdo, string $fingerprint): bool
{
    $query = $pdo->prepare(
        'SELECT COUNT(*) FROM admin_login_attempts
         WHERE fingerprint = :fingerprint AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
    );
    $query->execute(['fingerprint' => $fingerprint]);
    return (int) $query->fetchColumn() >= 5;
}

function admin_record_login_failure(PDO $pdo, string $fingerprint): void
{
    $pdo->prepare('DELETE FROM admin_login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)')->execute();
    $insert = $pdo->prepare('INSERT INTO admin_login_attempts (fingerprint) VALUES (:fingerprint)');
    $insert->execute(['fingerprint' => $fingerprint]);
}

function admin_clear_login_failures(PDO $pdo, string $fingerprint): void
{
    $delete = $pdo->prepare('DELETE FROM admin_login_attempts WHERE fingerprint = :fingerprint');
    $delete->execute(['fingerprint' => $fingerprint]);
}

function reservation_status_label(string $status): string
{
    return match ($status) {
        'pendiente' => 'Pendiente',
        'confirmada' => 'Confirmada',
        'cancelada' => 'Cancelada',
        'atendida' => 'Atendida',
        default => 'Desconocida',
    };
}
