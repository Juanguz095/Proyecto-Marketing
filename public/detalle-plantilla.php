<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

$templates = require __DIR__ . '/../app/template_catalog.php';
$requestedSlug = $_GET['plantilla'] ?? 'clasica';
$template = null;
if (is_string($requestedSlug)) {
    foreach ($templates as $templateOption) {
        if ($templateOption['slug'] === $requestedSlug) {
            $template = $templateOption;
            break;
        }
    }
}
if ($template === null) {
    http_response_code(404);
    $template = $templates[0];
}
$previewUrl = 'barberia.php?b=barberia-demo&plantilla=' . rawurlencode($template['slug']) . '&preview=1';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= e($template['description']) ?>">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_PE">
  <meta property="og:site_name" content="OnlyFive">
  <meta property="og:title" content="<?= e($template['name']) ?> · Plantillas OnlyFive">
  <meta property="og:description" content="<?= e($template['description']) ?>">
  <meta name="twitter:card" content="summary">
  <meta name="theme-color" content="#f3eee5">
  <title><?= e($template['name']) ?> · Plantillas OnlyFive</title>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="assets/app.js" defer></script>
  <script src="assets/template-preview.js" defer></script>
</head>
<body class="templates-page template-detail-page">
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <div class="scroll-progress" aria-hidden="true"></div>
  <header class="site-header" id="inicio">
    <nav class="nav wrap brand-nav" aria-label="Navegación principal">
      <a class="brand-lockup" href="index.php" aria-label="OnlyFive, inicio"><span class="brand-symbol" aria-hidden="true">✂</span><span><strong>ONLYFIVE</strong><small>Web para barberías</small></span></a>
      <div class="nav-menu"><a class="nav-link" href="index.php">Inicio</a><a class="nav-link" href="plantillas.php" aria-current="page">Plantillas</a><a class="nav-link" href="contacto.php">Contacto</a></div>
      <a class="button button-dark nav-cta" href="contacto.php">Solicitar información <span aria-hidden="true">↗</span></a>
    </nav>
  </header>
  <main id="contenido" data-template-detail>
    <section class="template-detail-intro wrap">
      <a class="template-back-link" href="plantillas.php">← Volver a las plantillas</a>
      <div class="template-detail-grid">
        <div class="template-detail-copy">
          <p class="eyebrow">Diseño <?= e($template['number']) ?> / 03 · <?= e($template['label']) ?></p>
          <h1><?= e($template['name']) ?></h1>
          <p class="template-detail-lead"><?= e($template['description']) ?></p>
        </div>
        <aside class="template-detail-summary">
          <p class="eyebrow">Ideal para</p>
          <h2><?= e($template['audience']) ?></h2>
          <ul class="template-feature-list"><?php foreach ($template['features'] as $feature): ?><li><?= e($feature) ?></li><?php endforeach; ?></ul>
        </aside>
      </div>
    </section>
    <section class="template-demo wrap" id="muestra" aria-labelledby="template-demo-title">
      <div class="template-demo-heading">
        <div><p class="eyebrow">Vista de ejemplo</p><h2 id="template-demo-title">Así puede verse<br>tu barbería.</h2></div>
        <p>La muestra está dentro de esta página. Desplázate para recorrer el diseño; los datos son de demostración.</p>
      </div>
      <div class="template-demo-shell">
        <div class="template-demo-label"><span>ONLYFIVE · MUESTRA INTERACTIVA</span><span><?= e($template['number']) ?> / 03</span></div>
        <iframe class="template-demo-frame" data-template-frame src="<?= e($previewUrl) ?>" title="Vista previa de <?= e($template['name']) ?>" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
      </div>
    </section>
    <section class="template-detail-contact wrap">
      <div><p class="eyebrow">¿Te gusta este estilo?</p><h2>Lo adaptamos<br><em>a tu barbería.</em></h2></div>
      <a class="button button-dark" href="contacto.php">Hablemos de tu proyecto <span aria-hidden="true">↗</span></a>
    </section>
  </main>
  <footer class="footer wrap"><a class="footer-brand" href="index.php">ONLYFIVE</a><span>Soluciones web para barberías · Lima, Perú</span><a href="plantillas.php">Ver todas las plantillas ↗</a></footer>
  <div class="checker" aria-hidden="true"></div>
</body>
</html>
