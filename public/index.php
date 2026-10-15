<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

$templatesUrl = 'plantillas.php';
$whatsAppMessage = rawurlencode('Hola OnlyFive, quiero una página web para mi barbería.');
$contactNumber = preg_replace('/\D+/', '', (string) getenv('ONLYFIVE_WHATSAPP')) ?? '';
$contactBaseUrl = strlen($contactNumber) >= 7 && strlen($contactNumber) <= 15
    ? 'https://wa.me/' . $contactNumber
    : 'https://wa.me/';
$packages = [
    [
        'name' => 'Presencia',
        'label' => 'Para mostrar tu negocio',
        'description' => 'Una página clara con la historia de tu barbería, servicios, horarios y formas de contacto.',
        'items' => ['Perfil de la barbería', 'Servicios y precios', 'Contacto y horarios'],
        'message' => 'Hola OnlyFive, quiero cotizar una página para presentar mi barbería.',
    ],
    [
        'name' => 'Reservas',
        'label' => 'Para ordenar las citas',
        'description' => 'Incluye la información del negocio y un flujo de reservas conectado a la agenda del equipo.',
        'items' => ['Todo lo de Presencia', 'Disponibilidad por servicio', 'Panel para gestionar solicitudes'],
        'message' => 'Hola OnlyFive, quiero cotizar una página con reservas para mi barbería.',
    ],
    [
        'name' => 'A medida',
        'label' => 'Para una marca propia',
        'description' => 'Partimos de una plantilla y ajustamos el estilo, las fotos y el contenido a la identidad de tu negocio.',
        'items' => ['Elección de estilo', 'Colores y contenido propios', 'Alcance acordado antes de empezar'],
        'message' => 'Hola OnlyFive, quiero conversar sobre una página personalizada para mi barbería.',
    ],
];
$faqItems = [
    ['¿Qué información puede mostrar la página?', 'Puede incluir servicios, precios, duración de cada servicio, horarios, presentación del equipo, dirección y contacto. Tú defines qué datos se publican.'],
    ['¿La página funciona en celulares?', 'Sí. El diseño se adapta a pantallas pequeñas y los botones de contacto y reserva se pueden usar desde el celular.'],
    ['¿Cómo funcionan las reservas?', 'El cliente envía una solicitud con el servicio, el barbero y un horario disponible. La cita queda pendiente hasta que la barbería la confirme.'],
    ['¿Qué necesito para comenzar?', 'El nombre de la barbería, sus servicios y precios, horarios, formas de contacto y, si tienes, fotos o logotipo. Podemos acordar qué material falta antes de empezar.'],
    ['¿Cuánto cuesta y qué incluye el dominio?', 'El costo se cotiza según el contenido y las funciones que necesites. El dominio y el alojamiento se especifican en la cotización antes de iniciar.'],
];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="OnlyFive crea páginas web para barberías: servicios, perfiles, horarios y reservas en un solo lugar.">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_PE">
  <meta property="og:site_name" content="OnlyFive">
  <meta property="og:title" content="OnlyFive — Webs para barberías">
  <meta property="og:description" content="Una página para mostrar tus servicios, presentar a tus barberos y facilitar las reservas de tus clientes.">
  <meta name="twitter:card" content="summary">
  <meta name="theme-color" content="#f3eee5">
  <title>OnlyFive — Webs para barberías</title>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="assets/app.js" defer></script>
</head>
<body>
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <div class="scroll-progress" aria-hidden="true"></div>
  <header class="site-header" id="inicio">
    <nav class="nav wrap" aria-label="Navegación principal">
      <a class="nav-link" href="#solucion">Qué incluye</a>
      <a class="nav-link" href="<?= e($templatesUrl) ?>">Plantillas</a>
      <a class="nav-link" href="#paquetes">Paquetes</a>
      <a class="nav-link" href="#preguntas">Preguntas</a>
      <a class="nav-link" href="#contacto">Contacto</a>
    </nav>
    <a class="wordmark" href="#inicio" aria-label="OnlyFive, inicio">ONLYFIVE</a>
  </header>
  <main id="contenido">
    <section class="hero wrap">
      <div class="hero-image image-frame"><picture><source type="image/webp" srcset="assets/barberia-720.webp 720w, assets/barberia-1440.webp 1440w" sizes="(max-width: 760px) calc(100vw - 36px), 90vw"><img src="assets/barberia.png" alt="Barbero realizando un corte en una barbería de barrio" fetchpriority="high" decoding="async"></picture></div>
      <div class="burst">Web para<br>tu barbería</div>
      <div class="hero-copy reveal">
        <p class="eyebrow">Soluciones web para barberías de barrio</p>
        <h1>Tu barbería,<br>más visible<br><em>y organizada.</em></h1>
        <p class="hero-lead">Una página para mostrar tus servicios, presentar a tus barberos y facilitar las reservas de tus clientes.</p>
        <div class="hero-actions">
          <a class="button button-dark" href="<?= e($contactBaseUrl) ?>?text=<?= e($whatsAppMessage) ?>" target="_blank" rel="noopener noreferrer">Cotiza por WhatsApp <span aria-hidden="true">↗</span></a>
          <a class="text-link" href="<?= e($templatesUrl) ?>">Explorar plantillas <span aria-hidden="true">↓</span></a>
        </div>
      </div>
      <div class="hero-index" aria-hidden="true">01 <span>—</span> LIMA, PERÚ</div>
    </section>

    <div class="ticker" aria-label="Servicios, barberos y reservas"><div class="ticker-track" aria-hidden="true">
      <span>ESTILO <b>✳</b> SERVICIOS <b>✳</b> BARBEROS <b>✳</b> RESERVAS <b>✳</b> ESTILO <b>✳</b> SERVICIOS <b>✳</b> BARBEROS <b>✳</b> RESERVAS <b>✳</b></span>
      <span>ESTILO <b>✳</b> SERVICIOS <b>✳</b> BARBEROS <b>✳</b> RESERVAS <b>✳</b> ESTILO <b>✳</b> SERVICIOS <b>✳</b> BARBEROS <b>✳</b> RESERVAS <b>✳</b></span>
    </div></div>

    <section class="intro wrap" id="solucion">
      <div class="intro-heading reveal"><span class="eyebrow">Una web hecha para tu negocio</span><h2>Lo que tus clientes necesitan, <em>en un solo lugar.</em></h2></div>
      <div class="intro-visual reveal"><picture><source type="image/webp" srcset="assets/barbero-perfil-720.webp 480w, assets/barbero-perfil-1440.webp 960w" sizes="(max-width: 760px) 90vw, 40vw"><img src="assets/barbero-perfil.png" alt="Barbero en su local, listo para atender" loading="lazy" decoding="async"></picture><div class="visual-note">Tu equipo<br>también cuenta.</div></div>
      <div class="intro-copy reveal"><p>Presenta tu barbería como es: con tus precios, tus horarios y las personas que hacen cada corte.</p><a class="button button-outline" href="<?= e($templatesUrl) ?>">Ver todas las plantillas <span aria-hidden="true">↗</span></a></div>
    </section>

    <section class="features wrap" aria-labelledby="features-title">
      <div class="section-heading reveal"><p class="eyebrow">Una página clara y práctica</p><h2 id="features-title">Haz visible<br>tu barbería.</h2><p class="section-note">Diseñada para verse bien en el celular y facilitar que tus clientes encuentren lo que buscan.</p></div>
      <div class="feature-grid">
        <article class="feature-card reveal"><span class="feature-num">01</span><div class="icon-cut" aria-hidden="true">✂</div><h3>Servicios<br>y precios</h3><p>Tu catálogo ordenado para que el cliente elija con claridad.</p></article>
        <article class="feature-card feature-card-dark reveal"><span class="feature-num">02</span><div class="icon-person" aria-hidden="true">◉</div><h3>Perfiles<br>de barberos</h3><p>Presenta al equipo, sus especialidades y su trabajo.</p></article>
        <article class="feature-card reveal"><span class="feature-num">03</span><div class="icon-clock" aria-hidden="true">◷</div><h3>Horarios<br>y reservas</h3><p>Ayuda a organizar la agenda y recibir solicitudes de cita.</p></article>
      </div>
    </section>

    <section class="process wrap" aria-labelledby="process-title">
      <div class="process-top reveal"><p class="eyebrow">De la idea a tu página</p><h2 id="process-title">Un proceso<br>sin vueltas.</h2><p>Nos cuentas lo que necesitas. Personalizamos la web contigo y la dejamos lista para compartir.</p></div>
      <div class="process-list">
        <div class="process-row reveal"><span>01</span><h3>Conversamos</h3><p>Conocemos tu barbería y cómo trabajas.</p><b aria-hidden="true">↗</b></div>
        <div class="process-row reveal"><span>02</span><h3>Personalizamos</h3><p>Adaptamos nombre, colores, servicios y equipo.</p><b aria-hidden="true">↗</b></div>
        <div class="process-row reveal"><span>03</span><h3>La ponemos en marcha</h3><p>Revisamos juntos el contenido antes de compartirla.</p><b aria-hidden="true">↗</b></div>
      </div>
    </section>

    <section class="packages wrap" id="paquetes" aria-labelledby="packages-title">
      <div class="packages-heading reveal"><p class="eyebrow">Opciones para empezar</p><h2 id="packages-title">Elige lo que<br>necesita tu negocio.</h2><p>La cotización depende del contenido y las funciones que quieras incluir. Te confirmamos el alcance antes de comenzar.</p></div>
      <div class="package-grid">
        <?php foreach ($packages as $package): ?>
          <article class="package-card reveal">
            <p class="eyebrow"><?= e($package['label']) ?></p>
            <h3><?= e($package['name']) ?></h3>
            <p><?= e($package['description']) ?></p>
            <ul><?php foreach ($package['items'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul>
            <span class="package-price">Cotización personalizada</span>
            <a class="button button-dark" href="<?= e($contactBaseUrl) ?>?text=<?= e(rawurlencode($package['message'])) ?>" target="_blank" rel="noopener noreferrer">Consultar este paquete <span aria-hidden="true">↗</span></a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="faq wrap" id="preguntas" aria-labelledby="faq-title">
      <div class="faq-heading reveal"><p class="eyebrow">Antes de comenzar</p><h2 id="faq-title">Preguntas<br>frecuentes.</h2></div>
      <div class="faq-list">
        <?php foreach ($faqItems as $index => $item): ?>
          <details class="faq-item reveal"<?= $index === 0 ? ' open' : '' ?>><summary><?= e($item[0]) ?><span aria-hidden="true">＋</span></summary><p><?= e($item[1]) ?></p></details>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="contact wrap" id="contacto">
      <div class="contact-image reveal"><picture><source type="image/webp" srcset="assets/barberia-720.webp 720w, assets/barberia-1440.webp 1440w" sizes="(max-width: 760px) 100vw, 50vw"><img src="assets/barberia.png" alt="Interior cálido de una barbería" loading="lazy" decoding="async"></picture></div>
      <div class="contact-card reveal"><p class="eyebrow">OnlyFive · Lima</p><h2>Cuéntanos<br>cómo es<br><em>tu barbería.</em></h2><p>Hablemos de los servicios, el equipo y la forma de organizar las citas.</p><a class="button button-light" href="<?= e($contactBaseUrl) ?>?text=<?= e($whatsAppMessage) ?>" target="_blank" rel="noopener noreferrer">Hablemos por WhatsApp <span aria-hidden="true">↗</span></a></div>
    </section>
  </main>
  <footer class="footer wrap"><a class="footer-brand" href="#inicio">ONLYFIVE</a><span>Soluciones web para barberías · Lima, Perú</span><a href="<?= e($templatesUrl) ?>">Explorar plantillas ↗</a></footer>
  <div class="checker" aria-hidden="true"></div>
</body>
</html>
