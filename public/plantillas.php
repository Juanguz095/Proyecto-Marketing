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
<body class="templates-page">
  <a class="skip-link" href="#galeria">Saltar a las plantillas</a>
  <div class="scroll-progress" aria-hidden="true"></div>
  <header class="site-header" id="inicio">
    <nav class="nav wrap" aria-label="Navegación principal">
      <a class="nav-link" href="index.php">Inicio</a>
      <a class="nav-link" href="plantillas.php" aria-current="page">Plantillas</a>
      <a class="nav-link" href="contacto.php">Contacto</a>
    </nav>
    <a class="wordmark" href="index.php" aria-label="OnlyFive, inicio">ONLYFIVE</a>
  </header>

  <main id="galeria">
    <section class="templates-intro wrap">
      <p class="eyebrow">Diseños para tu barbería</p>
      <h1>Encuentra tu<br><em>estilo.</em></h1>
      <p>Explora las propuestas y abre su ficha para ver la descripción y una muestra del sitio. Luego adaptamos colores, fotos y contenido a tu negocio.</p>
    </section>

    <section class="templates-grid wrap" aria-label="Galería de plantillas">
      <?php foreach ($templates as $templateOption): ?>
        <article class="template-card reveal">
          <div class="template-card-preview <?= e($templateOption['style']) ?>" role="img" aria-label="Vista visual de la plantilla <?= e($templateOption['name']) ?>">
            <?php if ($templateOption['slug'] === 'clasica'): ?>
              <img src="assets/barberia-720.webp" alt="" loading="lazy" decoding="async">
              <div class="template-preview-top"><span>BARBERÍA DEMO</span><span>MENÚ&nbsp;&nbsp; RESERVAS</span></div>
              <div class="template-preview-copy"><span><?= e($templateOption['label']) ?></span><strong><?= e($templateOption['headline']) ?></strong><i>RESERVAR&nbsp; ↗</i></div>
              <span class="template-preview-number"><?= e($templateOption['number']) ?></span>
            <?php elseif ($templateOption['slug'] === 'urbana'): ?>
              <div class="urban-preview-header"><span>BF / 1988</span><span>MENÚ&nbsp; ☰</span></div>
              <img src="assets/barberia-720.webp" alt="" loading="lazy" decoding="async">
              <div class="urban-preview-copy"><span><?= e($templateOption['label']) ?></span><strong><?= e($templateOption['headline']) ?></strong><i>RESERVA TU HORA&nbsp; ↗</i></div>
              <span class="urban-preview-stamp">CORTE<br>CLÁSICO<br>01—25</span>
            <?php else: ?>
              <div class="minimal-preview-header"><span>ESTUDIO<br>BARBER</span><span>MENU</span></div>
              <img src="assets/barbero-perfil-720.webp" alt="" loading="lazy" decoding="async">
              <div class="minimal-preview-copy"><span><?= e($templateOption['label']) ?></span><strong><?= e($templateOption['headline']) ?></strong><i>VER SERVICIOS&nbsp; ↗</i></div>
              <span class="minimal-preview-note">Cuidado en cada detalle.</span>
            <?php endif; ?>
          </div>
          <div class="template-card-body">
            <div class="template-card-heading"><span><?= e($templateOption['number']) ?> / 03</span><h2><?= e($templateOption['name']) ?></h2></div>
            <p><?= e($templateOption['description']) ?></p>
            <ul class="template-feature-list"><?php foreach ($templateOption['features'] as $feature): ?><li><?= e($feature) ?></li><?php endforeach; ?></ul>
            <a class="button button-dark" href="detalle-plantilla.php?plantilla=<?= e($templateOption['slug']) ?>">Ver detalles y muestra <span aria-hidden="true">↗</span></a>
          </div>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="templates-contact wrap">
      <p class="eyebrow">¿Quieres una página así?</p>
      <h2>La hacemos<br><em>a tu medida.</em></h2>
      <a class="button button-outline" href="contacto.php">Hablemos de tu barbería <span aria-hidden="true">↗</span></a>
    </section>
  </main>

  <footer class="footer wrap"><a class="footer-brand" href="index.php">ONLYFIVE</a><span>Soluciones web para barberías · Lima, Perú</span><a href="index.php">Volver al inicio ↗</a></footer>
  <div class="checker" aria-hidden="true"></div>
</body>
</html>
