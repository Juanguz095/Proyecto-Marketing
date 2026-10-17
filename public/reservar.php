<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

$csrfToken = $_POST['csrf_token'] ?? null;
if (!is_string($csrfToken) || !verify_csrf($csrfToken)) {
    page_message('No se pudo validar el formulario', 'Vuelve a la página de la barbería e intenta enviar la solicitud otra vez.');
}

$shopId = filter_var($_POST['barberia_id'] ?? null, FILTER_VALIDATE_INT);
$serviceId = filter_var($_POST['servicio_id'] ?? null, FILTER_VALIDATE_INT);
$barberId = filter_var($_POST['barbero_id'] ?? null, FILTER_VALIDATE_INT);
$name = trim((string) ($_POST['cliente_nombre'] ?? ''));
$phoneInput = trim((string) ($_POST['cliente_telefono'] ?? ''));
$phone = preg_replace('/[^0-9+() .-]/', '', $phoneInput) ?? '';
$date = trim((string) ($_POST['fecha'] ?? ''));
$time = trim((string) ($_POST['hora'] ?? ''));

if (!$shopId || !$serviceId || !$barberId || $name === '' || $phone === '') {
    page_message('Completa los datos', 'Revisa el formulario y completa cada campo antes de enviar la solicitud.');
}
if (strlen($name) > 120 || strlen($phone) > 30 || strlen(preg_replace('/\D/', '', $phone) ?? '') < 7) {
    page_message('Revisa tus datos', 'Escribe un nombre y un número celular válidos.');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
    page_message('Fecha u hora no válida', 'Selecciona una fecha y una hora válidas en el formulario.');
}
if (($_POST['acepta_privacidad'] ?? null) !== '1') {
    page_message('Falta aceptar el aviso de privacidad', 'Lee el aviso de privacidad y marca la casilla antes de enviar la solicitud.');
}

$timezone = new DateTimeZone('America/Lima');
$selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timezone);
$today = new DateTimeImmutable('today', $timezone);
if (!$selectedDate || $selectedDate < $today || $selectedDate > $today->modify('+60 days')) {
    page_message('Elige una fecha cercana', 'Puedes solicitar citas desde hoy hasta los próximos 60 días.');
}
$start = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date . ' ' . $time, $timezone);
$dateErrors = DateTimeImmutable::getLastErrors();
if (!$start || ($dateErrors && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
    || $start->format('Y-m-d H:i') !== $date . ' ' . $time
    || $start <= new DateTimeImmutable('now', $timezone)) {
    page_message('Elige otro horario', 'La fecha debe ser futura y tener un formato válido.');
}
if (!allow_session_action('booking', 5, 3600)) {
    page_message('Espera antes de enviar otra solicitud', 'Alcanzaste el límite de solicitudes de esta sesión. Inténtalo de nuevo más tarde.');
}

try {
    $pdo = db();
    $shopQuery = $pdo->prepare('SELECT id, nombre FROM barberias WHERE id = :id AND activo = 1 LIMIT 1');
    $shopQuery->execute(['id' => $shopId]);
    $shop = $shopQuery->fetch();

    $serviceQuery = $pdo->prepare('SELECT id, duracion_min FROM servicios WHERE id = :id AND barberia_id = :shop AND activo = 1 LIMIT 1');
    $serviceQuery->execute(['id' => $serviceId, 'shop' => $shopId]);
    $service = $serviceQuery->fetch();

    $barberQuery = $pdo->prepare('SELECT id FROM barberos WHERE id = :id AND barberia_id = :shop AND activo = 1 LIMIT 1');
    $barberQuery->execute(['id' => $barberId, 'shop' => $shopId]);
    $barber = $barberQuery->fetch();

    if (!$shop || !$service || !$barber) {
        page_message('No se encontró el servicio o barbero', 'Actualiza la página y vuelve a seleccionar el servicio y el barbero.');
    }

    $end = $start->modify('+' . (int) $service['duracion_min'] . ' minutes');
    $dayOfWeek = (int) $start->format('w');
    $startSql = $start->format('Y-m-d H:i:s');
    $endSql = $end->format('Y-m-d H:i:s');

    $pdo->beginTransaction();
    $scheduleQuery = $pdo->prepare('SELECT abre, cierra FROM horarios WHERE barbero_id = :barber AND dia_semana = :day FOR UPDATE');
    $scheduleQuery->execute(['barber' => $barberId, 'day' => $dayOfWeek]);
    $schedule = $scheduleQuery->fetch();

    if (!$schedule) {
        $pdo->rollBack();
        page_message('Ese día no hay atención', 'Elige otra fecha para solicitar tu reserva.');
    }

    $opens = new DateTimeImmutable($date . ' ' . $schedule['abre'], $timezone);
    $closes = new DateTimeImmutable($date . ' ' . $schedule['cierra'], $timezone);
    $minutesFromOpening = (int) (($start->getTimestamp() - $opens->getTimestamp()) / 60);
    if ($start < $opens || $end > $closes || $minutesFromOpening % 15 !== 0) {
        $pdo->rollBack();
        page_message('Horario no disponible', 'Elige uno de los horarios disponibles que muestra el formulario.');
    }

    $overlapQuery = $pdo->prepare(
        "SELECT COUNT(*) FROM reservas
         WHERE barbero_id = :barber AND estado IN ('pendiente','confirmada')
           AND inicio < :new_end AND fin > :new_start"
    );
    $overlapQuery->execute(['barber' => $barberId, 'new_end' => $endSql, 'new_start' => $startSql]);
    if ((int) $overlapQuery->fetchColumn() > 0) {
        $pdo->rollBack();
        page_message('Ese horario ya está ocupado', 'Elige otra hora o comunícate con la barbería para consultar disponibilidad.');
    }

    $insert = $pdo->prepare(
        "INSERT INTO reservas
         (barberia_id, barbero_id, servicio_id, cliente_nombre, cliente_telefono, inicio, fin, estado)
         VALUES (:shop, :barber, :service, :name, :phone, :start, :end, 'pendiente')"
    );
    $insert->execute([
        'shop' => $shopId,
        'barber' => $barberId,
        'service' => $serviceId,
        'name' => $name,
        'phone' => $phone,
        'start' => $startSql,
        'end' => $endSql,
    ]);
    $reservationId = (string) $pdo->lastInsertId();
    $policyQuery = $pdo->prepare('SELECT politica_privacidad FROM ajustes_barberia WHERE barberia_id = :shop');
    $policyQuery->execute(['shop' => $shopId]);
    $policyText = $policyQuery->fetchColumn();
    $consent = $pdo->prepare(
        'INSERT INTO consentimientos_reserva (reserva_id, version_politica) VALUES (:reservation, :version)'
    );
    $consent->execute([
        'reservation' => $reservationId,
        'version' => privacy_policy_version(is_string($policyText) ? $policyText : null),
    ]);
    $pdo->commit();

    unset($_SESSION['csrf_token']);
    $formattedDate = $start->format('d/m/Y') . ' a las ' . $start->format('H:i');
    page_message(
        'Solicitud enviada',
        'Tu solicitud para ' . $shop['nombre'] . ' quedó pendiente para el ' . $formattedDate . '. Código de solicitud: ' . $reservationId . '. La barbería debe confirmar el horario.',
        true
    );
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    page_message('No se pudo guardar la solicitud', 'Comprueba la conexión con MySQL e inténtalo de nuevo.', false, 503);
}
