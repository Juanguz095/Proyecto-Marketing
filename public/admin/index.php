<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/database.php';
require_once __DIR__ . '/../../app/admin.php';

[$adminUsername, $adminPasswordHash, $adminConfigured] = admin_credentials();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Actualiza la página e inténtalo otra vez.';
    } elseif (!$adminConfigured) {
        http_response_code(503);
        $error = 'El acceso de administración todavía no está configurado en el servidor.';
    } else {
        try {
            $pdo = db();
            $fingerprint = admin_login_fingerprint($adminPasswordHash);
            if (admin_login_is_limited($pdo, $fingerprint)) {
                $error = 'Se alcanzó el límite de intentos. Espera 15 minutos antes de volver a probar.';
            } else {
                $username = trim((string) ($_POST['username'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                if (hash_equals($adminUsername, $username) && password_verify($password, $adminPasswordHash)) {
                    admin_clear_login_failures($pdo, $fingerprint);
                    session_regenerate_id(true);
                    $_SESSION['admin_user'] = $adminUsername;
                    $_SESSION['admin_last_activity'] = time();
                    header('Location: index.php');
                    exit;
                }
                admin_record_login_failure($pdo, $fingerprint);
                $error = 'El usuario o la contraseña no son correctos.';
            }
        } catch (PDOException $exception) {
            http_response_code(503);
            $error = 'No se pudo conectar con la base de datos. Revisa la configuración y las migraciones.';
        }
    }
}

if (admin_is_authenticated()) {
    header('Cache-Control: no-store, private');
    header('Pragma: no-cache');
    try {
        $pdo = db();
        $shopQuery = $pdo->query('SELECT id, nombre, slug FROM barberias WHERE activo = 1 ORDER BY id LIMIT 1');
        $shop = $shopQuery->fetch();
        if (!$shop) {
            throw new RuntimeException('No hay una barbería activa configurada.');
        }

        $statsQuery = $pdo->prepare(
            "SELECT estado, COUNT(*) AS total FROM reservas WHERE barberia_id = :shop GROUP BY estado"
        );
        $statsQuery->execute(['shop' => $shop['id']]);
        $stats = ['pendiente' => 0, 'confirmada' => 0, 'atendida' => 0, 'cancelada' => 0];
        foreach ($statsQuery->fetchAll() as $row) {
            $stats[$row['estado']] = (int) $row['total'];
        }

        $filter = $_GET['estado'] ?? '';
        $allowedFilters = ['', 'pendiente', 'confirmada', 'atendida', 'cancelada'];
        if (!is_string($filter) || !in_array($filter, $allowedFilters, true)) {
            $filter = '';
        }
        $sql = "SELECT r.*, b.nombre AS barbero, s.nombre AS servicio
                FROM reservas r
                INNER JOIN barberos b ON b.id = r.barbero_id
                INNER JOIN servicios s ON s.id = r.servicio_id
                WHERE r.barberia_id = :shop";
        $params = ['shop' => $shop['id']];
        if ($filter !== '') {
            $sql .= ' AND r.estado = :status';
            $params['status'] = $filter;
        }
        $sql .= " ORDER BY FIELD(r.estado, 'pendiente', 'confirmada', 'atendida', 'cancelada'), r.inicio ASC LIMIT 100";
        $reservationsQuery = $pdo->prepare($sql);
        $reservationsQuery->execute($params);
        $reservations = $reservationsQuery->fetchAll();
    } catch (Throwable $exception) {
        http_response_code(503);
        $dashboardError = 'No se pudo cargar el panel. Revisa que la base de datos y las migraciones estén configuradas.';
    }
    ?>
    <!doctype html>
    <html lang="es">
    <head>
      <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="theme-color" content="#f3eee5"><title>Reservas · Administración Solisweb</title>
      <link rel="stylesheet" href="../assets/styles.css">
    </head>
    <body class="admin-page">
      <a class="skip-link" href="#contenido">Saltar al contenido</a>
      <header class="admin-header"><div class="wrap admin-header-inner"><a class="wordmark admin-wordmark" href="index.php">SOLISWEB</a><div><span>Panel de <?= e($shop['nombre'] ?? 'barbería') ?></span><form action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="admin-link-button" type="submit">Cerrar sesión</button></form></div></div></header>
      <main class="wrap admin-main" id="contenido">
        <div class="admin-title-row"><div><p class="eyebrow">Agenda y atención</p><h1>Reservas</h1><p>Gestiona las solicitudes y avisa al cliente desde WhatsApp.</p></div><a class="button button-outline" href="configuracion.php">Configurar la página <span aria-hidden="true">↗</span></a></div>
        <?php if (!empty($dashboardError)): ?><div class="admin-alert" role="alert"><?= e($dashboardError) ?></div><?php else: ?>
          <?php if (isset($_GET['guardado'])): ?><p class="admin-success" role="status">El estado de la reserva se actualizó.</p><?php endif; ?>
          <?php if (isset($_GET['error'])): ?><p class="admin-alert" role="alert"><?= e(is_string($_GET['error']) ? $_GET['error'] : 'No se pudo actualizar la reserva.') ?></p><?php endif; ?>
          <section class="admin-stats" aria-label="Resumen de reservas">
            <article><span>Pendientes</span><strong><?= $stats['pendiente'] ?></strong></article>
            <article><span>Confirmadas</span><strong><?= $stats['confirmada'] ?></strong></article>
            <article><span>Atendidas</span><strong><?= $stats['atendida'] ?></strong></article>
            <article><span>Canceladas</span><strong><?= $stats['cancelada'] ?></strong></article>
          </section>
          <nav class="admin-filters" aria-label="Filtrar reservas">
            <?php foreach (['' => 'Todas', 'pendiente' => 'Pendientes', 'confirmada' => 'Confirmadas', 'atendida' => 'Atendidas', 'cancelada' => 'Canceladas'] as $value => $label): ?>
              <a class="<?= $filter === $value ? 'is-active' : '' ?>" href="?estado=<?= e($value) ?>" <?= $filter === $value ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
          </nav>
          <?php if (!$reservations): ?><section class="admin-empty"><h2>No hay reservas para mostrar</h2><p>Las nuevas solicitudes aparecerán aquí.</p></section><?php else: ?>
            <section class="admin-reservations" aria-label="Lista de reservas">
              <?php foreach ($reservations as $reservation):
                  $status = (string) $reservation['estado'];
                  $date = new DateTimeImmutable($reservation['inicio'], new DateTimeZone('America/Lima'));
                  $message = match ($status) {
                      'pendiente' => 'Hola ' . $reservation['cliente_nombre'] . ', recibimos tu solicitud para ' . $reservation['servicio'] . ' el ' . $date->format('d/m/Y') . ' a las ' . $date->format('H:i') . '. En breve te confirmaremos el horario.',
                      'confirmada' => 'Hola ' . $reservation['cliente_nombre'] . ', tu cita para ' . $reservation['servicio'] . ' el ' . $date->format('d/m/Y') . ' a las ' . $date->format('H:i') . ' quedó confirmada. ¡Te esperamos!',
                      'cancelada' => 'Hola ' . $reservation['cliente_nombre'] . ', no podremos atender tu solicitud de ' . $reservation['servicio'] . ' el ' . $date->format('d/m/Y') . '. Comunícate con nosotros para coordinar otro horario.',
                      default => 'Hola ' . $reservation['cliente_nombre'] . ', gracias por tu visita. Esperamos que hayas disfrutado tu servicio en ' . $shop['nombre'] . '.',
                  };
                  $contactLink = whatsapp_url((string) $reservation['cliente_telefono'], $message);
              ?>
                <article class="admin-reservation">
                  <div class="admin-reservation-top"><span class="status-pill status-<?= e($status) ?>"><?= e(reservation_status_label($status)) ?></span><span class="admin-reservation-code">Reserva #<?= e((string) $reservation['id']) ?></span></div>
                  <h2><?= e($reservation['cliente_nombre']) ?></h2>
                  <p class="admin-reservation-time"><strong><?= e($date->format('d/m/Y')) ?></strong> · <?= e($date->format('H:i')) ?></p>
                  <dl><div><dt>Servicio</dt><dd><?= e($reservation['servicio']) ?></dd></div><div><dt>Barbero</dt><dd><?= e($reservation['barbero']) ?></dd></div><div><dt>Celular</dt><dd><?= e($reservation['cliente_telefono']) ?></dd></div></dl>
                  <div class="admin-reservation-actions">
                    <?php if ($status === 'pendiente'): ?>
                      <?php foreach (['confirmada' => 'Confirmar cita', 'cancelada' => 'Cancelar'] as $nextStatus => $label): ?>
                        <form action="reservation-action.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e((string) $reservation['id']) ?>"><input type="hidden" name="estado" value="<?= e($nextStatus) ?>"><button class="button <?= $nextStatus === 'confirmada' ? 'button-dark' : 'button-outline' ?>" type="submit"><?= e($label) ?></button></form>
                      <?php endforeach; ?>
                    <?php elseif ($status === 'confirmada'): ?>
                      <form action="reservation-action.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e((string) $reservation['id']) ?>"><input type="hidden" name="estado" value="atendida"><button class="button button-dark" type="submit">Marcar atendida</button></form>
                      <form action="reservation-action.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e((string) $reservation['id']) ?>"><input type="hidden" name="estado" value="cancelada"><button class="button button-outline" type="submit">Cancelar</button></form>
                    <?php endif; ?>
                    <?php if ($contactLink): ?><a class="admin-whatsapp" href="<?= e($contactLink) ?>" target="_blank" rel="noopener noreferrer">Avisar por WhatsApp ↗</a><?php endif; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            </section>
            <p class="admin-footnote">Se muestran hasta 100 reservas. Las notificaciones abren WhatsApp con un mensaje listo para que lo revises y envíes.</p>
          <?php endif; ?>
        <?php endif; ?>
      </main>
      <footer class="admin-footer wrap"><a href="../index.php">Volver a Solisweb</a><a href="../privacidad.php?b=<?= e($shop['slug'] ?? 'barberia-demo') ?>">Aviso de privacidad</a></footer>
    </body>
    </html>
    <?php
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f3eee5"><title>Acceso · Solisweb</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="admin-page">
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <main class="admin-login-shell" id="contenido">
    <a class="wordmark admin-login-wordmark" href="../index.php">SOLISWEB</a>
    <section class="admin-login-card">
      <p class="eyebrow">Acceso privado</p><h1>Administrar<br>reservas.</h1>
      <p>Ingresa con la cuenta de administración configurada para esta instalación.</p>
      <?php if ($error !== ''): ?><p class="admin-alert" role="alert"><?= e($error) ?></p><?php endif; ?>
      <?php if (!$adminConfigured): ?><p class="admin-setup-note">El panel está protegido y solo se activa cuando el servidor tiene configurados <code>ADMIN_USERNAME</code> y <code>ADMIN_PASSWORD_HASH</code>. Consulta el README.</p><?php else: ?>
        <form method="post" class="admin-login-form">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <label for="username">Usuario</label><input id="username" name="username" autocomplete="username" maxlength="80" required>
          <label for="password">Contraseña</label><input id="password" name="password" type="password" autocomplete="current-password" required>
          <button class="button button-dark" type="submit" name="login" value="1">Entrar <span aria-hidden="true">↗</span></button>
        </form>
      <?php endif; ?>
      <a class="admin-back-link" href="../index.php">Volver a Solisweb</a>
    </section>
  </main>
</body>
</html>
