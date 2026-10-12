<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/database.php';

$slug = $_GET['b'] ?? 'barberia-demo';
if (!is_string($slug) || !preg_match('/^[a-z0-9-]{1,120}$/', $slug)) {
    http_response_code(404);
    exit('Barbería no encontrada.');
}

try {
    $pdo = db();
    $shopQuery = $pdo->prepare('SELECT * FROM barberias WHERE slug = :slug AND activo = 1 LIMIT 1');
    $shopQuery->execute(['slug' => $slug]);
    $shop = $shopQuery->fetch();
    if (!$shop) {
        http_response_code(404);
        exit('Barbería no encontrada.');
    }
    $serviceQuery = $pdo->prepare('SELECT id, nombre, descripcion, duracion_min, precio FROM servicios WHERE barberia_id = :id AND activo = 1 ORDER BY nombre');
    $serviceQuery->execute(['id' => $shop['id']]);
    $services = $serviceQuery->fetchAll();
    $barberQuery = $pdo->prepare('SELECT id, nombre, especialidad, foto FROM barberos WHERE barberia_id = :id AND activo = 1 ORDER BY nombre');
    $barberQuery->execute(['id' => $shop['id']]);
    $barbers = $barberQuery->fetchAll();
    $hoursQuery = $pdo->prepare(
        'SELECT h.barbero_id, h.dia_semana, h.abre, h.cierra
         FROM horarios h INNER JOIN barberos b ON b.id = h.barbero_id
         WHERE b.barberia_id = :id AND b.activo = 1 ORDER BY h.dia_semana'
    );
    $hoursQuery->execute(['id' => $shop['id']]);
    $hoursByBarber = [];
    foreach ($hoursQuery->fetchAll() as $hour) {
        $hoursByBarber[(int) $hour['barbero_id']][] = $hour;
    }
    $dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
} catch (PDOException $exception) {
    http_response_code(503);
    $dbError = true;
}

if (!empty($dbError)):
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Configura la base de datos · OnlyFive</title><link rel="stylesheet" href="assets/styles.css"></head>
<body><main class="message-page"><section class="message-card"><a class="wordmark" href="index.php">ONLYFIVE</a><p class="eyebrow">Falta un paso de configuración</p><h1>Conecta MySQL</h1><p>La página ya está lista. Sigue el README para crear la base de datos y cargar los datos de muestra.</p><a class="button button-dark" href="index.php">Volver al inicio</a></section></main></body></html>
<?php
exit;
endif;

$dateMin = (new DateTimeImmutable('today'))->format('Y-m-d');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Servicios, barberos y reservas de <?= e($shop['nombre']) ?>.">
  <meta name="theme-color" content="#f3eee5">
  <title><?= e($shop['nombre']) ?> · Barbería</title>
  <link rel="stylesheet" href="assets/styles.css"><script src="assets/app.js" defer></script>
</head>
<body class="shop-page">
  <div class="scroll-progress" aria-hidden="true"></div>
  <header class="shop-header wrap" id="inicio">
    <a class="shop-logo" href="#inicio"><?= e($shop['nombre']) ?><small>BARBERÍA</small></a>
    <nav class="shop-nav" aria-label="Navegación de la barbería"><a href="#servicios">Servicios</a><a href="#equipo">El equipo</a><a href="#reserva">Reservar</a></nav>
  </header>
  <main>
    <section class="shop-hero wrap">
      <div class="shop-hero-photo image-frame"><img src="assets/barberia.png" alt="Barbero trabajando en un corte"></div>
      <div class="shop-hero-content reveal"><span class="eyebrow">Corte · Estilo · Comunidad</span><h1>Más que<br>un corte.</h1><p><?= e($shop['descripcion'] ?: 'Un buen corte empieza con una buena conversación.') ?></p><a class="button button-dark" href="#reserva">Reserva tu cita <span aria-hidden="true">↓</span></a></div>
      <span class="demo-label">PLANTILLA DE EJEMPLO · REEMPLAZAR DATOS</span>
    </section>
    <section class="shop-services wrap" id="servicios">
      <div class="section-heading reveal"><p class="eyebrow">Hecho a tu medida</p><h2>Servicios<br>y precios.</h2><p class="section-note">Precios de muestra: actualízalos con los de la barbería.</p></div>
      <div class="service-list">
        <?php foreach ($services as $service): ?>
          <article class="service-row reveal"><div><span><?= (int) $service['duracion_min'] ?> MIN</span><h3><?= e($service['nombre']) ?></h3><p><?= e($service['descripcion']) ?></p></div><strong><?= e(money($service['precio'])) ?></strong></article>
        <?php endforeach; ?>
        <?php if (!$services): ?><p>Aún no hay servicios publicados.</p><?php endif; ?>
      </div>
    </section>
    <section class="barber-showcase wrap" id="equipo">
      <div class="barber-photo reveal"><img src="assets/barbero-perfil.png" alt="Barbero de la plantilla de ejemplo"></div>
      <div class="barber-copy reveal"><p class="eyebrow">Las manos detrás del corte</p><h2>Conoce<br>a tu barbero.</h2>
        <?php foreach ($barbers as $barber): ?>
          <div class="barber-name"><strong><?= e($barber['nombre']) ?></strong><span><?= e($barber['especialidad'] ?: 'Barbero') ?></span>
            <?php foreach ($hoursByBarber[(int) $barber['id']] ?? [] as $hour): ?>
              <small><?= e($dayNames[(int) $hour['dia_semana']] ?? 'Día') ?> · <?= e(substr($hour['abre'], 0, 5)) ?>–<?= e(substr($hour['cierra'], 0, 5)) ?></small>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
        <?php if (!$barbers): ?><p>El equipo se mostrará aquí.</p><?php endif; ?>
      </div>
    </section>
    <section class="booking wrap" id="reserva">
      <div class="booking-intro reveal"><p class="eyebrow">Tu próximo corte empieza aquí</p><h2>Reserva<br>tu cita.</h2><p>Elige el servicio, el barbero y un horario. La solicitud quedará pendiente hasta que la barbería la confirme.</p></div>
      <form class="booking-form reveal" action="reservar.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="barberia_id" value="<?= (int) $shop['id'] ?>">
        <label>Servicio<select name="servicio_id" required><option value="">Selecciona un servicio</option><?php foreach ($services as $service): ?><option value="<?= (int) $service['id'] ?>"><?= e($service['nombre']) ?> · <?= e(money($service['precio'])) ?></option><?php endforeach; ?></select></label>
        <label>Barbero<select name="barbero_id" required><option value="">Selecciona un barbero</option><?php foreach ($barbers as $barber): ?><option value="<?= (int) $barber['id'] ?>"><?= e($barber['nombre']) ?></option><?php endforeach; ?></select></label>
        <div class="form-pair"><label>Fecha<input type="date" name="fecha" min="<?= e($dateMin) ?>" required></label><label>Hora<input type="time" name="hora" required></label></div>
        <label>Tu nombre<input name="cliente_nombre" type="text" maxlength="120" autocomplete="name" placeholder="Nombre y apellido" required></label>
        <label>Celular<input name="cliente_telefono" type="tel" maxlength="30" autocomplete="tel" placeholder="Ej. 999 123 456" required></label>
        <button class="button button-dark" type="submit">Enviar solicitud <span aria-hidden="true">↗</span></button>
        <small>La reserva se registra como pendiente. La barbería debe confirmar el horario.</small>
      </form>
    </section>
  </main>
  <footer class="shop-footer"><div class="wrap"><a class="shop-logo" href="#inicio"><?= e($shop['nombre']) ?><small>BARBERÍA</small></a><span><?= e($shop['direccion'] ?: 'Lima, Perú') ?></span><a href="index.php">Una plantilla de OnlyFive ↗</a></div><div class="checker" aria-hidden="true"></div></footer>
</body>
</html>
