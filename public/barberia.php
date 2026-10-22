<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/database.php';

$slug = $_GET['b'] ?? 'barberia-demo';
$template = $_GET['plantilla'] ?? 'clasica';
$previewMode = ($_GET['preview'] ?? '') === '1';
if (!is_string($template) || !in_array($template, ['clasica', 'urbana', 'minimal'], true)) {
    $template = 'clasica';
}
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
    $settingsQuery = $pdo->prepare('SELECT foto_portada, foto_barbero FROM ajustes_barberia WHERE barberia_id = :id LIMIT 1');
    $settingsQuery->execute(['id' => $shop['id']]);
    $settings = $settingsQuery->fetch() ?: ['foto_portada' => null, 'foto_barbero' => null];
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

$dateMin = (new DateTimeImmutable('today', new DateTimeZone('America/Lima')))->format('Y-m-d');
$dateMax = (new DateTimeImmutable('today', new DateTimeZone('America/Lima')))->modify('+60 days')->format('Y-m-d');
$whatsAppLink = !$previewMode && is_string($shop['whatsapp'] ?? null)
    ? whatsapp_url($shop['whatsapp'], 'Hola, quisiera consultar sobre una cita en ' . $shop['nombre'] . '.')
    : null;
$coverPhoto = $settings['foto_portada'] ?: 'assets/barberia.png';
$teamPhoto = $settings['foto_barbero'] ?: 'assets/barbero-perfil.png';
$coverIsDemoPhoto = $coverPhoto === 'assets/barberia.png';
$teamIsDemoPhoto = $teamPhoto === 'assets/barbero-perfil.png';
$templateNames = ['clasica' => 'Estilo clásico', 'urbana' => 'Estilo urbano', 'minimal' => 'Estilo minimal'];
$demoCopy = [
    'clasica' => ['headline' => "Un estilo clásico\nque nunca pasa de moda.", 'description' => 'Cortes clásicos, cuidado de barba y una atención profesional para quienes valoran la tradición.'],
    'urbana' => ['headline' => "Marca tu estilo\ncon cada corte.", 'description' => 'Cortes actuales, detalles precisos y un espacio pensado para expresar tu personalidad.'],
    'minimal' => ['headline' => "Cuidado sencillo,\nbien hecho.", 'description' => 'Un lugar tranquilo para renovar tu estilo, cuidar tu barba y disfrutar de una atención cercana.'],
];
$heroHeadline = $demoCopy[$template]['headline'];
$address = trim((string) ($shop['direccion'] ?? ''));
$genericAddress = strcasecmp($address, 'Lima, Perú') === 0 || strcasecmp($address, 'Lima, Peru') === 0;
$mapsUrl = $address !== '' && !$genericAddress
    ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address)
    : null;
$shopDescription = trim(strip_tags((string) ($shop['descripcion'] ?: 'Servicios, barberos y reservas de ' . $shop['nombre'] . '.')));
$heroDescription = $shop['slug'] === 'barberia-demo' ? $demoCopy[$template]['description'] : $shopDescription;
$shopMetaDescription = preg_replace('/^(.{0,155}).*/us', '$1', $shopDescription) ?? $shopDescription;
$businessStructuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'BarberShop',
    'name' => $shop['nombre'],
    'description' => $shop['descripcion'] ?: 'Servicios de barbería y reservas.',
];
if (is_string($shop['whatsapp']) && trim($shop['whatsapp']) !== '') {
    $businessStructuredData['telephone'] = $shop['whatsapp'];
}
if ($address !== '' && !$genericAddress) {
    $businessStructuredData['address'] = $address;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= e($shopMetaDescription) ?>">
  <meta name="robots" content="index,follow">
  <meta property="og:type" content="business.business">
  <meta property="og:locale" content="es_PE">
  <meta property="og:site_name" content="OnlyFive">
  <meta property="og:title" content="<?= e($shop['nombre']) ?> · Barbería">
  <meta property="og:description" content="Servicios, equipo y reservas de <?= e($shop['nombre']) ?>.">
  <meta name="twitter:card" content="summary">
  <meta name="theme-color" content="#f3eee5">
  <title><?= e($shop['nombre']) ?> · Barbería</title>
  <script type="application/ld+json"><?= json_encode($businessStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
  <link rel="stylesheet" href="assets/styles.css"><script src="assets/app.js" defer></script>
</head>
<body class="shop-page template-<?= e($template) ?>">
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <div class="scroll-progress" aria-hidden="true"></div>
  <?php if ($previewMode): ?><aside class="preview-ribbon"><div><strong>Vista demo: <?= e($templateNames[$template]) ?></strong><span>Contenido de muestra · se adapta a los datos de tu barbería</span></div><a href="detalle-plantilla.php?plantilla=<?= e($template) ?>" target="_top">Volver a plantillas <span aria-hidden="true">↗</span></a></aside><?php endif; ?>
  <header class="shop-header wrap" id="inicio">
    <a class="shop-logo" href="#inicio"><?= e($shop['nombre']) ?><small>BARBERÍA</small></a>
    <nav class="shop-nav" aria-label="Navegación de la barbería"><a href="#servicios">Servicios</a><a href="#equipo">El equipo</a><a href="#reserva">Reservar</a></nav>
  </header>
  <main id="contenido">
    <section class="shop-hero wrap">
      <div class="shop-hero-photo image-frame"><?php if ($coverIsDemoPhoto): ?><picture><source type="image/webp" srcset="assets/barberia-720.webp 720w, assets/barberia-1440.webp 1440w" sizes="(max-width: 760px) calc(100vw - 36px), 70vw"><img src="assets/barberia.png" alt="Interior de <?= e($shop['nombre']) ?>" fetchpriority="high" decoding="async"></picture><?php else: ?><img src="<?= e($coverPhoto) ?>" alt="Interior de <?= e($shop['nombre']) ?>" fetchpriority="high" decoding="async"><?php endif; ?></div>
      <div class="shop-hero-content reveal"><span class="eyebrow">Corte · Estilo · Comunidad</span><h1><?= nl2br(e($heroHeadline)) ?></h1><p><?= e($heroDescription ?: 'Un buen corte empieza con una buena conversación.') ?></p><a class="button button-dark" href="#reserva">Reserva tu cita <span aria-hidden="true">↓</span></a></div>
      <?php if ($shop['slug'] === 'barberia-demo'): ?><span class="demo-label">PLANTILLA DE EJEMPLO · REEMPLAZAR DATOS</span><?php endif; ?>
    </section>
    <section class="shop-highlights wrap" aria-label="Lo que puedes encontrar en esta página">
      <article><span aria-hidden="true">01</span><div><h2>Servicios</h2><p>Tarifas y duración en un solo lugar.</p></div></article>
      <article><span aria-hidden="true">02</span><div><h2>Barberos</h2><p>Conoce al equipo y consulta sus horarios.</p></div></article>
      <article><span aria-hidden="true">03</span><div><h2>Reservas</h2><p>Envía una solicitud para tu próxima cita.</p></div></article>
    </section>
    <section class="shop-services wrap" id="servicios">
      <div class="section-heading reveal"><p class="eyebrow">Nuestros servicios</p><h2>Elige tu<br>próximo corte.</h2><p class="section-note">Consulta las tarifas y el tiempo estimado de cada servicio.</p></div>
      <div class="service-list">
        <?php foreach ($services as $serviceIndex => $service): ?>
          <article class="service-row reveal"><span class="service-card-number"><?= str_pad((string) ($serviceIndex + 1), 2, '0', STR_PAD_LEFT) ?></span><div><span><?= (int) $service['duracion_min'] ?> MIN</span><h3><?= e($service['nombre']) ?></h3><p><?= e($service['descripcion']) ?></p></div><strong><?= e(money($service['precio'])) ?></strong><a href="#reserva"<?= $previewMode ? '' : ' data-select-service="' . (int) $service['id'] . '"' ?> aria-label="Reservar <?= e($service['nombre']) ?>">Reservar este servicio <span aria-hidden="true">→</span></a></article>
        <?php endforeach; ?>
        <?php if (!$services): ?><p>Aún no hay servicios publicados.</p><?php endif; ?>
      </div>
    </section>
    <section class="barber-showcase wrap" id="equipo">
      <div class="barber-photo reveal"><?php if ($teamIsDemoPhoto): ?><picture><source type="image/webp" srcset="assets/barbero-perfil-720.webp 480w, assets/barbero-perfil-1440.webp 960w" sizes="(max-width: 760px) 100vw, 50vw"><img src="assets/barbero-perfil.png" alt="Equipo de <?= e($shop['nombre']) ?>" loading="lazy" decoding="async"></picture><?php else: ?><img src="<?= e($teamPhoto) ?>" alt="Equipo de <?= e($shop['nombre']) ?>" loading="lazy" decoding="async"><?php endif; ?></div>
      <div class="barber-copy reveal"><p class="eyebrow">Las manos detrás del corte</p><h2>Conoce<br>al equipo.</h2>
        <div class="barber-name-list"><?php foreach ($barbers as $index => $barber): ?>
          <div class="barber-name"><strong><?= e($barber['nombre']) ?></strong><span><?= e($barber['especialidad'] ?: 'Barbero') ?></span>
            <small>Barbero <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?> · <?= e($shop['nombre']) ?></small>
            <?php foreach ($hoursByBarber[(int) $barber['id']] ?? [] as $hour): ?>
              <small><?= e($dayNames[(int) $hour['dia_semana']] ?? 'Día') ?> · <?= e(substr($hour['abre'], 0, 5)) ?>–<?= e(substr($hour['cierra'], 0, 5)) ?></small>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?><?php if (!$barbers): ?><p>El equipo se mostrará aquí.</p><?php endif; ?></div>
      </div>
    </section>
    <section class="booking wrap" id="reserva">
      <div class="booking-intro reveal"><p class="eyebrow">Tu próximo corte empieza aquí</p><h2>Reserva<br>tu cita.</h2><p><?= $previewMode ? 'Así se verá el área de reservas de esta propuesta.' : 'Elige el servicio, el barbero y un horario. La solicitud quedará pendiente hasta que la barbería la confirme.' ?></p></div>
      <?php if ($previewMode): ?>
        <div class="booking-preview-note"><p class="eyebrow">Solo una muestra</p><h3>Reserva de ejemplo</h3><p>Este formulario se activa cuando la página se configura para una barbería.</p></div>
      <?php else: ?>
      <form class="booking-form reveal" action="reservar.php" method="post" data-availability-endpoint="api/horarios-disponibles.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="barberia_id" value="<?= (int) $shop['id'] ?>">
        <label for="servicio-id">Servicio<select id="servicio-id" name="servicio_id" required><option value="">Selecciona un servicio</option><?php foreach ($services as $service): ?><option value="<?= (int) $service['id'] ?>" data-duration="<?= (int) $service['duracion_min'] ?>"><?= e($service['nombre']) ?> · <?= e(money($service['precio'])) ?></option><?php endforeach; ?></select></label>
        <label for="barbero-id">Barbero<select id="barbero-id" name="barbero_id" required><option value="">Selecciona un barbero</option><?php foreach ($barbers as $barber): ?><option value="<?= (int) $barber['id'] ?>"><?= e($barber['nombre']) ?></option><?php endforeach; ?></select></label>
        <div class="form-pair"><label for="fecha-cita">Fecha<input id="fecha-cita" type="date" name="fecha" min="<?= e($dateMin) ?>" max="<?= e($dateMax) ?>" required></label><label for="hora-cita">Hora<select id="hora-cita" name="hora" required disabled><option value="">Primero elige fecha y servicio</option></select></label></div>
        <p class="availability-status" data-availability-status role="status" aria-live="polite">Elige un servicio, un barbero y una fecha para consultar los horarios disponibles.</p>
        <label>Tu nombre<input name="cliente_nombre" type="text" maxlength="120" autocomplete="name" placeholder="Nombre y apellido" required></label>
        <label>Celular<input name="cliente_telefono" type="tel" maxlength="30" autocomplete="tel" placeholder="Ej. 999 123 456" required></label>
        <label class="privacy-consent"><input name="acepta_privacidad" type="checkbox" value="1" required><span>Autorizo el uso de mis datos para gestionar esta cita y he leído el <a href="privacidad.php?b=<?= e($shop['slug']) ?>" target="_blank" rel="noopener">aviso de privacidad</a>.</span></label>
        <noscript><small>Activa JavaScript en tu navegador para consultar los horarios disponibles.</small></noscript>
        <button class="button button-dark" type="submit" disabled>Enviar solicitud <span aria-hidden="true">↗</span></button>
        <small>La solicitud queda pendiente hasta que la barbería confirme el horario. Se muestran fechas de los próximos 60 días.</small>
      </form>
      <?php if ($whatsAppLink): ?><a class="booking-contact-link" href="<?= e($whatsAppLink) ?>" target="_blank" rel="noopener noreferrer">¿Tienes una consulta? Escríbenos por WhatsApp ↗</a><?php endif; ?>
      <?php endif; ?>
    </section>
    <section class="shop-contact wrap" aria-label="Información de contacto">
      <article class="shop-contact-card"><span class="shop-contact-icon" aria-hidden="true">⌖</span><div><p class="eyebrow">Encuéntranos</p><h2><?= $address !== '' ? e($address) : 'Consulta la ubicación al reservar.' ?></h2><?php if ($mapsUrl): ?><a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener noreferrer">Ver ubicación <span aria-hidden="true">↗</span></a><?php elseif ($address === ''): ?><a href="#reserva">Consultar disponibilidad <span aria-hidden="true">→</span></a><?php endif; ?></div></article>
      <article class="shop-contact-card"><span class="shop-contact-icon" aria-hidden="true">◷</span><div><p class="eyebrow">Horarios</p><h2>Consulta la disponibilidad de cada barbero.</h2><a href="#equipo">Ver horarios del equipo <span aria-hidden="true">→</span></a></div></article>
      <article class="shop-contact-card shop-contact-cta"><span class="shop-contact-icon" aria-hidden="true">✳</span><div><p class="eyebrow">¿Tienes una consulta?</p><h2><?= $whatsAppLink ? 'Escríbenos y te ayudamos.' : 'Reserva tu siguiente cita.' ?></h2><?php if ($whatsAppLink): ?><a href="<?= e($whatsAppLink) ?>" target="_blank" rel="noopener noreferrer">Abrir WhatsApp <span aria-hidden="true">↗</span></a><?php else: ?><a href="#reserva">Ir a reservas <span aria-hidden="true">→</span></a><?php endif; ?></div></article>
    </section>
  </main>
  <footer class="shop-footer"><div class="wrap"><a class="shop-logo" href="#inicio"><?= e($shop['nombre']) ?><small>BARBERÍA</small></a><span><?= e($shop['direccion'] ?: 'Lima, Perú') ?></span><?php if ($mapsUrl): ?><a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener noreferrer">Cómo llegar ↗</a><?php endif; ?><a href="privacidad.php?b=<?= e($shop['slug']) ?>">Privacidad ↗</a><a href="plantillas.php">Ver más plantillas ↗</a></div><div class="checker" aria-hidden="true"></div></footer>
</body>
</html>
