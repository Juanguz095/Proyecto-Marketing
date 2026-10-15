<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/database.php';
require_once __DIR__ . '/../../app/admin.php';
require_admin();

function reservation_action_error(string $message): void
{
    header('Location: index.php?error=' . rawurlencode($message), true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}
if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    reservation_action_error('La sesión del formulario venció. Actualiza la página.');
}

$reservationId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$nextStatus = $_POST['estado'] ?? null;
$transitions = [
    'pendiente' => ['confirmada', 'cancelada'],
    'confirmada' => ['atendida', 'cancelada'],
];
if (!$reservationId || !is_string($nextStatus) || !in_array($nextStatus, ['confirmada', 'cancelada', 'atendida'], true)) {
    reservation_action_error('La acción solicitada no es válida.');
}

try {
    $pdo = db();
    $pdo->beginTransaction();
    $reservationQuery = $pdo->prepare(
        'SELECT id, barberia_id, barbero_id, inicio, fin, estado FROM reservas WHERE id = :id FOR UPDATE'
    );
    $reservationQuery->execute(['id' => $reservationId]);
    $reservation = $reservationQuery->fetch();
    if (!$reservation || !isset($transitions[$reservation['estado']])
        || !in_array($nextStatus, $transitions[$reservation['estado']], true)) {
        $pdo->rollBack();
        reservation_action_error('La reserva ya cambió de estado o no existe.');
    }

    if ($nextStatus === 'confirmada') {
        $overlapQuery = $pdo->prepare(
            "SELECT COUNT(*) FROM reservas
             WHERE barberia_id = :shop AND barbero_id = :barber AND id <> :id
               AND estado IN ('pendiente', 'confirmada')
               AND inicio < :end AND fin > :start"
        );
        $overlapQuery->execute([
            'shop' => $reservation['barberia_id'],
            'barber' => $reservation['barbero_id'],
            'id' => $reservationId,
            'end' => $reservation['fin'],
            'start' => $reservation['inicio'],
        ]);
        if ((int) $overlapQuery->fetchColumn() > 0) {
            $pdo->rollBack();
            reservation_action_error('No se puede confirmar: ese horario tiene otra reserva pendiente o confirmada.');
        }
    }

    $update = $pdo->prepare('UPDATE reservas SET estado = :status WHERE id = :id');
    $update->execute(['status' => $nextStatus, 'id' => $reservationId]);
    $pdo->commit();
    header('Location: index.php?guardado=1', true, 303);
    exit;
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    reservation_action_error('No se pudo actualizar la reserva. Inténtalo otra vez.');
}
