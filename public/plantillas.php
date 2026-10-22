<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

$templates = require __DIR__ . '/../app/template_catalog.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Explora las plantillas de OnlyFive y mira cómo podría verse la página de tu barbería.">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_PE">
  <meta property="og:site_name" content="OnlyFive">
  <meta property="og:title" content="Plantillas para barberías · OnlyFive">
  <meta property="og:description" content="Compara estilos clásicos, urbanos y minimalistas para la página de tu barbería.">
  <meta name="twitter:card" content="summary">
  <meta name="theme-color" content="#f3eee5">
  <title>Plantillas para barberías · OnlyFive</title>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="assets/app.js" defer></script>
</head>
<body class="templates-page gallery-page">
  <a class="skip-link" href="#galeria">Saltar a las plantillas</a>
  <div class="scroll-progress" aria-hidden="true"></div>
  <header class="site-header" id="inicio">
    <nav class="nav wrap brand-nav" aria-label="Navegación principal">
      <a class="brand-lockup" href="index.php" aria-label="OnlyFive, inicio"><span class="brand-symbol" aria-hidden="true">✂</span><span><strong>ONLYFIVE</strong><small>Web para barberías</small></span></a>
      <div class="nav-menu"><a class="nav-link" href="index.php">Inicio</a><a class="nav-link" href="plantillas.php" aria-current="page">Plantillas</a><a class="nav-link" href="#incluye">Qué incluyen</a><a class="nav-link" href="contacto.php">Contacto</a></div>
      <a class="button button-dark nav-cta" href="contacto.php">Solicitar información <span aria-hidden="true">↗</span></a>
    </nav>
  </header>
  <main id="galeria">
    <section class="gallery-hero wrap">
      <div class="gallery-hero-copy reveal"><p class="eyebrow">Diseños listos para personalizar</p><h1>Nuestras<br><em>plantillas.</em></h1><p>Compara estilos pensados para barberías. Cada muestra abre dentro de su ficha para que puedas conocerla antes de elegir.</p><a class="button button-dark" href="#catalogo">Explorar diseños <span aria-hidden="true">↓</span></a></div>
      <div class="gallery-hero-visual reveal"><picture><source type="image/webp" srcset="assets/barberia-720.webp 720w, assets/barberia-1440.webp 1440w" sizes="(max-width: 760px) calc(100vw - 36px), 48vw"><img src="assets/barberia.png" alt="Interior de una barbería moderna" fetchpriority="high" decoding="async"></picture><span>Diseñadas para<br>tu negocio.</span></div>
    </section>

    <section class="gallery-catalog wrap" id="catalogo" aria-labelledby="gallery-title">
      <div class="section-row-heading"><div class="compact-heading"><p class="eyebrow">Encuentra tu estilo</p><h2 id="gallery-title">Elige una propuesta</h2><p>Usa los filtros para comparar cada dirección visual.</p></div><span class="gallery-total">3 propuestas · todas personalizables</span></div>
      <div class="gallery-filters" role="group" aria-label="Filtrar plantillas por estilo" data-template-filters>
        <button type="button" class="gallery-filter is-active" data-template-filter="all" aria-pressed="true">Todas <span>3</span></button>
        <?php foreach ($templates as $templateOption): ?><button type="button" class="gallery-filter" data-template-filter="<?= e($templateOption['slug']) ?>" aria-pressed="false"><?= e($templateOption['name']) ?></button><?php endforeach; ?>
      </div>
      <p class="gallery-filter-status" data-template-filter-status aria-live="polite">Se muestran las 3 plantillas.</p>
      <div class="templates-grid gallery-grid" aria-label="Galería de plantillas">
        <?php foreach ($templates as $templateOption): ?>
          <article class="template-card gallery-template-card reveal" data-template-card="<?= e($templateOption['slug']) ?>">
            <div class="template-card-preview <?= e($templateOption['style']) ?>" role="img" aria-label="Vista visual de la plantilla <?= e($templateOption['name']) ?>">
              <?php if ($templateOption['slug'] === 'clasica'): ?>
                <img src="assets/barberia-720.webp" alt="" loading="lazy" decoding="async"><div class="template-preview-top"><span>BARBERÍA DEMO</span><span>MENÚ&nbsp;&nbsp; RESERVAS</span></div><div class="template-preview-copy"><span><?= e($templateOption['label']) ?></span><strong><?= e($templateOption['headline']) ?></strong><i>RESERVAR&nbsp; ↗</i></div><span class="template-preview-number"><?= e($templateOption['number']) ?></span>
              <?php elseif ($templateOption['slug'] === 'urbana'): ?>
                <div class="urban-preview-header"><span>BF / 1988</span><span>MENÚ&nbsp; ☰</span></div><img src="assets/barberia-720.webp" alt="" loading="lazy" decoding="async"><div class="urban-preview-copy"><span><?= e($templateOption['label']) ?></span><strong><?= e($templateOption['headline']) ?></strong><i>RESERVA TU HORA&nbsp; ↗</i></div><span class="urban-preview-stamp">CORTE<br>CLÁSICO<br>01—25</span>
              <?php else: ?>
                <div class="minimal-preview-header"><span>ESTUDIO<br>BARBER</span><span>MENU</span></div><img src="assets/barbero-perfil-720.webp" alt="" loading="lazy" decoding="async"><div class="minimal-preview-copy"><span><?= e($templateOption['label']) ?></span><strong><?= e($templateOption['headline']) ?></strong><i>VER SERVICIOS&nbsp; ↗</i></div><span class="minimal-preview-note">Cuidado en cada detalle.</span>
              <?php endif; ?>
            </div>
            <div class="template-card-body"><div class="template-card-heading"><span><?= e($templateOption['number']) ?> / 03</span><h3><?= e($templateOption['name']) ?></h3></div><p><?= e($templateOption['description']) ?></p><ul class="template-feature-list"><?php foreach ($templateOption['features'] as $feature): ?><li><?= e($feature) ?></li><?php endforeach; ?></ul><div class="gallery-card-actions"><a class="button button-outline" href="detalle-plantilla.php?plantilla=<?= e($templateOption['slug']) ?>">Ver demo <span aria-hidden="true">→</span></a><a class="button button-dark" href="contacto.php?plantilla=<?= e($templateOption['slug']) ?>">Solicitar <span aria-hidden="true">↗</span></a></div></div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="gallery-includes section-band" id="incluye" aria-labelledby="includes-title">
      <div class="wrap"><div class="compact-heading"><p class="eyebrow">Lo esencial para empezar</p><h2 id="includes-title">¿Qué puede incluir tu web?</h2><p>Definimos el contenido según lo que necesite tu barbería.</p></div>
        <div class="include-grid">
          <article class="include-card"><span aria-hidden="true">▤</span><div><h3>Servicios y precios</h3><p>Describe cada servicio, su duración y el precio que quieras publicar.</p></div></article>
          <article class="include-card"><span aria-hidden="true">◉</span><div><h3>Tu equipo</h3><p>Presenta a tus barberos, sus especialidades y horarios de atención.</p></div></article>
          <article class="include-card"><span aria-hidden="true">▦</span><div><h3>Contacto y reservas</h3><p>Facilita el contacto y, si lo necesitas, la solicitud de citas en línea.</p></div></article>
        </div>
      </div>
    </section>

    <section class="gallery-process wrap" aria-labelledby="gallery-process-title">
      <div class="compact-heading"><p class="eyebrow">De la elección a tu web</p><h2 id="gallery-process-title">Personalizamos tu plantilla</h2><p>Un camino corto, con revisiones contigo antes de publicar.</p></div>
      <ol class="step-grid gallery-step-grid"><li><span>01</span><h3>Elige tu estilo</h3><p>Revisa las muestras y selecciona tu favorita.</p></li><li><span>02</span><h3>Envía tus datos</h3><p>Comparte textos, fotos, servicios y horarios.</p></li><li><span>03</span><h3>Revisamos juntos</h3><p>Ajustamos los detalles para representar tu marca.</p></li><li><span>04</span><h3>Publicamos</h3><p>Recibes una web lista para compartir.</p></li></ol>
    </section>

    <section class="templates-contact wrap"><div><p class="eyebrow">¿No sabes cuál elegir?</p><h2>Te ayudamos a encontrar<br><em>tu estilo.</em></h2><p>Cuéntanos sobre tu barbería y te recomendamos una propuesta para comenzar.</p></div><a class="button button-dark" href="contacto.php">Solicitar información <span aria-hidden="true">↗</span></a></section>
  </main>
  <footer class="site-footer"><div class="wrap site-footer-main"><a class="brand-lockup footer-lockup" href="index.php"><span class="brand-symbol" aria-hidden="true">✂</span><span><strong>ONLYFIVE</strong><small>Soluciones web para barberías</small></span></a><div><h2>Explora</h2><a href="index.php">Inicio</a><a href="#catalogo">Plantillas</a><a href="#incluye">Qué incluyen</a></div><div><h2>Contacto</h2><a href="contacto.php">Solicitar información</a><span>Lima, Perú</span></div><div><h2>Elige con calma</h2><p>Abre cada demo, compara los estilos y cuéntanos cuál te gusta.</p></div></div><div class="wrap site-footer-bottom"><span>© <?= date('Y') ?> OnlyFive</span><a href="privacidad.php">Privacidad</a><a href="index.php">Volver al inicio</a></div></footer>
  <div class="checker" aria-hidden="true"></div>
</body>
</html>
