<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function availability_response(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$shopId = filter_var($_GET['barberia_id'] ?? null, FILTER_VALIDATE_INT);
$serviceId = filter_var($_GET['servicio_id'] ?? null, FILTER_VALIDATE_INT);
$barberId = filter_var($_GET['barbero_id'] ?? null, FILTER_VALIDATE_INT);
$date = $_GET['fecha'] ?? null;

if (!$shopId || $shopId < 1 || !$serviceId || $serviceId < 1 || !$barberId || $barberId < 1
    || !is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    availability_response(400, ['error' => 'Selecciona el servicio, el barbero y una fecha válida.']);
}

$timezone = new DateTimeZone('America/Lima');
$selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timezone);
$dateErrors = DateTimeImmutable::getLastErrors();
$today = new DateTimeImmutable('today', $timezone);
if (!$selectedDate
    || ($dateErrors && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
    || $selectedDate->format('Y-m-d') !== $date
    || $selectedDate < $today
    || $selectedDate > $today->modify('+60 days')) {
    availability_response(400, ['error' => 'La fecha debe estar entre hoy y los próximos 60 días.']);
}

try {
    $pdo = db();
    $dayOfWeek = (int) $selectedDate->format('w');
    $scheduleQuery = $pdo->prepare(
        'SELECT s.duracion_min, h.abre, h.cierra
         FROM servicios s
         INNER JOIN barberos b ON b.id = :barber AND b.barberia_id = s.barberia_id AND b.activo = 1
         INNER JOIN horarios h ON h.barbero_id = b.id AND h.dia_semana = :day
         WHERE s.id = :service AND s.barberia_id = :shop AND s.activo = 1
         LIMIT 1'
    );
    $scheduleQuery->execute([
        'barber' => $barberId,
        'day' => $dayOfWeek,
        'service' => $serviceId,
        'shop' => $shopId,
    ]);
    $schedule = $scheduleQuery->fetch();
    if (!$schedule) {
        availability_response(200, ['slots' => []]);
    }

    $opens = new DateTimeImmutable($date . ' ' . $schedule['abre'], $timezone);
    $closes = new DateTimeImmutable($date . ' ' . $schedule['cierra'], $timezone);
    $duration = (int) $schedule['duracion_min'];
    $dayStart = $selectedDate->setTime(0, 0)->format('Y-m-d H:i:s');
    $dayEnd = $selectedDate->modify('+1 day')->setTime(0, 0)->format('Y-m-d H:i:s');
    $reservationsQuery = $pdo->prepare(
        "SELECT inicio, fin FROM reservas
         WHERE barbero_id = :barber AND inicio < :day_end AND fin > :day_start
           AND estado IN ('pendiente', 'confirmada')"
    );
    $reservationsQuery->execute(['barber' => $barberId, 'day_end' => $dayEnd, 'day_start' => $dayStart]);
    $reservations = $reservationsQuery->fetchAll();

    $now = new DateTimeImmutable('now', $timezone);
    $slots = [];
    for ($slot = $opens; $slot->modify('+' . $duration . ' minutes') <= $closes; $slot = $slot->modify('+15 minutes')) {
        $slotEnd = $slot->modify('+' . $duration . ' minutes');
        if ($slot <= $now) {
            continue;
        }

        $occupied = false;
        foreach ($reservations as $reservation) {
            $reservedStart = new DateTimeImmutable($reservation['inicio'], $timezone);
            $reservedEnd = new DateTimeImmutable($reservation['fin'], $timezone);
            if ($slot < $reservedEnd && $slotEnd > $reservedStart) {
                $occupied = true;
                break;
            }
        }
        if (!$occupied) {
            $slots[] = $slot->format('H:i');
        }
    }

    availability_response(200, ['slots' => $slots]);
} catch (PDOException $exception) {
    availability_response(503, ['error' => 'No se pudo consultar la disponibilidad. Inténtalo de nuevo.']);
}
