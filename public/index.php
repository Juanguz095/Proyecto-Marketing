<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

$templates = require __DIR__ . '/../app/template_catalog.php';
$templatesUrl = 'plantillas.php';
$whatsAppMessage = rawurlencode('Hola Solisweb, quiero una página web para mi barbería.');
$contactNumber = preg_replace('/\D+/', '', (string) (getenv('SOLISWEB_WHATSAPP') ?: getenv('ONLYFIVE_WHATSAPP'))) ?? '';
$contactBaseUrl = strlen($contactNumber) >= 7 && strlen($contactNumber) <= 15
    ? 'https://wa.me/' . $contactNumber
    : 'https://wa.me/';
$packages = [
    [
        'name' => 'Presencia',
        'label' => 'Para mostrar tu negocio',
        'description' => 'Una página clara con la historia de tu barbería, servicios, horarios y formas de contacto.',
        'items' => ['Perfil de la barbería', 'Servicios y precios', 'Contacto y horarios'],
        'message' => 'Hola Solisweb, quiero cotizar una página para presentar mi barbería.',
    ],
    [
        'name' => 'Reservas',
        'label' => 'Para ordenar las citas',
        'description' => 'Incluye la información del negocio y un flujo de reservas conectado a la agenda del equipo.',
        'items' => ['Todo lo de Presencia', 'Disponibilidad por servicio', 'Panel para gestionar solicitudes'],
        'message' => 'Hola Solisweb, quiero cotizar una página con reservas para mi barbería.',
    ],
    [
        'name' => 'A medida',
        'label' => 'Para una marca propia',
        'description' => 'Partimos de una plantilla y ajustamos el estilo, las fotos y el contenido a la identidad de tu negocio.',
        'items' => ['Elección de estilo', 'Colores y contenido propios', 'Alcance acordado antes de empezar'],
        'message' => 'Hola Solisweb, quiero conversar sobre una página personalizada para mi barbería.',
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
  <meta name="description" content="Solisweb crea páginas web para barberías: servicios, perfiles, horarios y reservas en un solo lugar.">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_PE">
  <meta property="og:site_name" content="Solisweb">
  <meta property="og:title" content="Solisweb — Webs para barberías">
  <meta property="og:description" content="Una página para mostrar tus servicios, presentar a tus barberos y facilitar las reservas de tus clientes.">
  <meta name="twitter:card" content="summary">
  <meta name="theme-color" content="#f3eee5">
  <title>Solisweb — Webs para barberías</title>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="assets/app.js" defer></script>
</head>
<body class="home-page">
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <div class="scroll-progress" aria-hidden="true"></div>
  <header class="site-header" id="inicio">
    <nav class="nav wrap brand-nav" aria-label="Navegación principal">
      <a class="brand-lockup" href="#inicio" aria-label="Solisweb, inicio"><span class="brand-symbol" aria-hidden="true">✂</span><span><strong>SOLISWEB</strong><small>Web para barberías</small></span></a>
      <div class="nav-menu"><a class="nav-link" href="#inicio" aria-current="page">Inicio</a><a class="nav-link" href="plantillas.php">Plantillas</a><a class="nav-link" href="#beneficios">Beneficios</a><a class="nav-link" href="#paquetes">Opciones</a></div>
      <a class="button button-dark nav-cta" href="contacto.php">Solicitar información <span aria-hidden="true">↗</span></a>
    </nav>
  </header>
  <main id="contenido">
    <section class="home-hero wrap" aria-labelledby="home-title">
      <div class="home-hero-copy reveal">
        <p class="eyebrow">Soluciones web para barberías</p>
        <h1 id="home-title">Tu barbería<br>merece estar<br><em>en línea.</em></h1>
        <p class="home-hero-lead">Plantillas prácticas, modernas y adaptables para mostrar tus servicios, presentar a tu equipo y organizar las citas.</p>
        <div class="hero-actions"><a class="button button-dark" href="plantillas.php">Ver plantillas <span aria-hidden="true">→</span></a><a class="button button-outline" href="<?= e($contactBaseUrl) ?>?text=<?= e($whatsAppMessage) ?>" target="_blank" rel="noopener noreferrer">Escribir por WhatsApp <span aria-hidden="true">↗</span></a></div>
        <p class="home-hero-note"><span aria-hidden="true">✳</span> Hecha para tu negocio y para tus clientes desde el celular.</p>
      </div>
      <div class="home-hero-visual reveal">
        <picture><source type="image/webp" srcset="assets/barberia-720.webp 720w, assets/barberia-1440.webp 1440w" sizes="(max-width: 760px) calc(100vw - 36px), 48vw"><img src="assets/barberia.png" alt="Barbero realizando un corte en una barbería" fetchpriority="high" decoding="async"></picture>
        <span class="home-hero-stamp">Tu estilo.<br>Tu espacio.</span>
        <span class="home-hero-caption">Una presencia digital que se siente como tu barbería.</span>
      </div>
    </section>

    <section class="home-offerings section-band" aria-labelledby="offerings-title">
      <div class="wrap">
        <div class="compact-heading"><p class="eyebrow">Una página, todo claro</p><h2 id="offerings-title">¿Qué ofrecemos?</h2><p>Lo que tus clientes necesitan para conocerte y reservar.</p></div>
        <div class="offering-grid">
          <article class="offering-card"><span class="offering-icon" aria-hidden="true">▤</span><div><h3>Plantillas listas</h3><p>Diseños creados para mostrar los servicios y el carácter de tu barbería.</p></div></article>
          <article class="offering-card"><span class="offering-icon" aria-hidden="true">▣</span><div><h3>Diseño adaptable</h3><p>Se ve bien en celulares, tabletas y computadoras, sin perder claridad.</p></div></article>
          <article class="offering-card"><span class="offering-icon" aria-hidden="true">⚙</span><div><h3>Reservas y soporte</h3><p>Opciones para recibir citas y acompañamiento para personalizar tu página.</p></div></article>
        </div>
      </div>
    </section>

    <section class="home-benefits wrap" id="beneficios" aria-labelledby="benefits-title">
      <div class="compact-heading"><p class="eyebrow">Una mejor experiencia para todos</p><h2 id="benefits-title">Una web que trabaja<br><em>por tu barbería.</em></h2><p>La información correcta, a un toque de distancia.</p></div>
      <div class="benefit-grid">
        <article class="benefit-card"><span aria-hidden="true">◎</span><h3>Más visibilidad</h3><p>Comparte un enlace para que nuevos clientes encuentren tu negocio.</p></article>
        <article class="benefit-card"><span aria-hidden="true">✦</span><h3>Mejor imagen</h3><p>Presenta tu estilo, tus servicios y el cuidado que pones en cada corte.</p></article>
        <article class="benefit-card"><span aria-hidden="true">▦</span><h3>Reservas claras</h3><p>Permite consultar horarios y enviar una solicitud de cita en línea.</p></article>
        <article class="benefit-card"><span aria-hidden="true">↗</span><h3>Todo ordenado</h3><p>Reúne horarios, precios, equipo y contacto en un solo lugar.</p></article>
      </div>
    </section>

    <section class="home-templates section-band" id="plantillas" aria-labelledby="home-templates-title">
      <div class="wrap">
        <div class="section-row-heading"><div class="compact-heading"><p class="eyebrow">Diseños para elegir</p><h2 id="home-templates-title">Plantillas destacadas</h2><p>Elige el estilo que mejor representa tu negocio.</p></div><a class="button button-outline" href="plantillas.php">Ver las 3 plantillas <span aria-hidden="true">→</span></a></div>
        <div class="home-template-grid">
          <?php foreach ($templates as $templateOption): ?>
            <article class="home-template-card">
              <a class="home-template-visual <?= e($templateOption['style']) ?>" href="detalle-plantilla.php?plantilla=<?= e($templateOption['slug']) ?>" aria-label="Ver muestra de <?= e($templateOption['name']) ?>">
                <?php if ($templateOption['slug'] === 'clasica'): ?><img src="assets/barberia-720.webp" alt="" loading="lazy" decoding="async"><span class="home-template-kicker">TRADICIÓN RENOVADA</span><strong>Un corte<br>con carácter.</strong><span class="home-template-action">VER MUESTRA ↗</span>
                <?php elseif ($templateOption['slug'] === 'urbana'): ?><img src="assets/barberia-720.webp" alt="" loading="lazy" decoding="async"><span class="home-template-kicker">CONTRASTE Y ENERGÍA</span><strong>Marca tu<br>propio estilo.</strong><span class="home-template-action">VER MUESTRA ↗</span>
                <?php else: ?><img src="assets/barbero-perfil-720.webp" alt="" loading="lazy" decoding="async"><span class="home-template-kicker">SIMPLE Y NATURAL</span><strong>Lo esencial,<br>bien hecho.</strong><span class="home-template-action">VER MUESTRA ↗</span><?php endif; ?>
              </a>
              <div class="home-template-info"><span class="eyebrow"><?= e($templateOption['number']) ?> · <?= e($templateOption['label']) ?></span><h3><?= e($templateOption['name']) ?></h3><p><?= e($templateOption['description']) ?></p><a class="text-arrow" href="detalle-plantilla.php?plantilla=<?= e($templateOption['slug']) ?>">Ver detalles y demo <span aria-hidden="true">→</span></a></div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="home-process wrap" aria-labelledby="home-process-title">
      <div class="compact-heading"><p class="eyebrow">Empezar es sencillo</p><h2 id="home-process-title">¿Cómo lo hacemos?</h2><p>Te acompañamos desde la elección del estilo hasta la publicación.</p></div>
      <ol class="step-grid">
        <li><span>01</span><h3>Elige tu plantilla</h3><p>Revisa las opciones y encuentra el estilo que te gusta.</p></li>
        <li><span>02</span><h3>Cuéntanos de ti</h3><p>Comparte el nombre, los servicios, horarios y fotos de tu barbería.</p></li>
        <li><span>03</span><h3>La personalizamos</h3><p>Ajustamos el diseño y revisamos el contenido contigo.</p></li>
        <li><span>04</span><h3>La publicamos</h3><p>Dejamos la página lista para que puedas compartirla.</p></li>
      </ol>
    </section>

    <section class="packages wrap" id="paquetes" aria-labelledby="packages-title">
      <div class="packages-heading reveal"><p class="eyebrow">Opciones para empezar</p><h2 id="packages-title">Elige lo que<br>necesita tu negocio.</h2><p>La cotización depende del contenido y las funciones que quieras incluir. Te confirmamos el alcance antes de comenzar.</p></div>
      <div class="package-grid">
        <?php foreach ($packages as $package): ?>
          <article class="package-card reveal"><p class="eyebrow"><?= e($package['label']) ?></p><h3><?= e($package['name']) ?></h3><p><?= e($package['description']) ?></p><ul><?php foreach ($package['items'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul><span class="package-price">Cotización personalizada</span><a class="button button-dark" href="<?= e($contactBaseUrl) ?>?text=<?= e(rawurlencode($package['message'])) ?>" target="_blank" rel="noopener noreferrer">Consultar este paquete <span aria-hidden="true">↗</span></a></article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="faq wrap" id="preguntas" aria-labelledby="faq-title">
      <div class="faq-heading reveal"><p class="eyebrow">Antes de comenzar</p><h2 id="faq-title">Preguntas<br>frecuentes.</h2></div>
      <div class="faq-list"><?php foreach ($faqItems as $index => $item): ?><details class="faq-item reveal"<?= $index === 0 ? ' open' : '' ?>><summary><?= e($item[0]) ?><span aria-hidden="true">＋</span></summary><p><?= e($item[1]) ?></p></details><?php endforeach; ?></div>
    </section>

    <section class="home-contact wrap" id="contacto">
      <div class="home-contact-image"><picture><source type="image/webp" srcset="assets/barberia-720.webp 720w, assets/barberia-1440.webp 1440w" sizes="(max-width: 760px) 100vw, 48vw"><img src="assets/barberia.png" alt="Interior cálido de una barbería" loading="lazy" decoding="async"></picture></div>
      <div class="home-contact-copy"><p class="eyebrow">¿Listo para empezar?</p><h2>Tu próxima<br>gran idea<br><em>empieza aquí.</em></h2><p>Cuéntanos qué necesita tu barbería. Te ayudamos a elegir el estilo y las funciones.</p><a class="button button-light" href="contacto.php">Solicitar información <span aria-hidden="true">→</span></a></div>
    </section>
  </main>
  <footer class="site-footer"><div class="wrap site-footer-main"><a class="brand-lockup footer-lockup" href="#inicio"><span class="brand-symbol" aria-hidden="true">✂</span><span><strong>SOLISWEB</strong><small>Soluciones web para barberías</small></span></a><div><h2>Enlaces rápidos</h2><a href="#inicio">Inicio</a><a href="plantillas.php">Plantillas</a><a href="contacto.php">Contacto</a></div><div><h2>Hablemos</h2><a href="contacto.php">Solicita información</a><span>Lima, Perú</span></div><div><h2>Tu proyecto</h2><p>Una página clara, cercana y pensada para crecer con tu negocio.</p></div></div><div class="wrap site-footer-bottom"><span>© <?= date('Y') ?> Solisweb</span><a href="privacidad.php">Privacidad</a><span>Soluciones web para barberías</span></div></footer>
  <div class="checker" aria-hidden="true"></div>
</body>
</html>
