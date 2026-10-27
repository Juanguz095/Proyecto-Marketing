<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

$templates = require __DIR__ . '/../app/template_catalog.php';
$selectedTemplate = null;
$requestedTemplate = $_GET['plantilla'] ?? null;
if (is_string($requestedTemplate)) {
    foreach ($templates as $templateOption) {
        if ($templateOption['slug'] === $requestedTemplate) {
            $selectedTemplate = $templateOption;
            break;
        }
    }
}
$contactNumber = preg_replace('/\D+/', '', (string) (getenv('SOLISWEB_WHATSAPP') ?: getenv('ONLYFIVE_WHATSAPP'))) ?? '';
$contactBaseUrl = strlen($contactNumber) >= 7 && strlen($contactNumber) <= 15
    ? 'https://wa.me/' . $contactNumber
    : 'https://wa.me/';
$message = 'Hola Solisweb, quiero cotizar una página web para mi barbería.';
if ($selectedTemplate !== null) {
    $message .= ' Me interesa la plantilla ' . $selectedTemplate['name'] . '.';
}
$message .= ' El nombre de mi barbería es: ____. Necesito: ____.';
$whatsAppMessage = rawurlencode($message);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Conversa con Solisweb sobre una página web para tu barbería. Cuéntanos qué necesitas y solicita una cotización.">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_PE">
  <meta property="og:site_name" content="Solisweb">
  <meta property="og:title" content="Contacto · Solisweb">
  <meta property="og:description" content="Hablemos de una página para tu barbería y preparemos una cotización según lo que necesitas.">
  <meta name="twitter:card" content="summary">
  <meta name="theme-color" content="#f3eee5">
  <title>Contacto · Solisweb</title>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="assets/app.js" defer></script>
</head>
<body class="contact-page">
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <div class="scroll-progress" aria-hidden="true"></div>
  <header class="site-header" id="inicio">
    <nav class="nav wrap brand-nav" aria-label="Navegación principal">
      <a class="brand-lockup" href="index.php" aria-label="Solisweb, inicio"><span class="brand-symbol" aria-hidden="true">✂</span><span><strong>SOLISWEB</strong><small>Web para barberías</small></span></a>
      <div class="nav-menu"><a class="nav-link" href="index.php">Inicio</a><a class="nav-link" href="plantillas.php">Plantillas</a><a class="nav-link" href="contacto.php" aria-current="page">Contacto</a></div>
      <a class="button button-dark nav-cta" href="#contenido">Solicitar información <span aria-hidden="true">↘</span></a>
    </nav>
  </header>
  <main id="contenido">
    <section class="contact-page-intro wrap">
      <p class="eyebrow">Solisweb · Lima</p>
      <h1>Hablemos de tu<br><em>barbería.</em></h1>
      <p>Cuéntanos qué quieres mostrar o mejorar. Te ayudamos a definir una página con el estilo y las funciones que necesita tu negocio.</p>
    </section>
    <section class="contact-page-grid wrap" aria-label="Cómo solicitar una cotización">
      <div class="contact-page-card contact-page-card-dark">
        <p class="eyebrow"><?= $selectedTemplate !== null ? 'Plantilla elegida · ' . e($selectedTemplate['name']) : 'Empecemos la conversación' ?></p>
        <h2>Escríbenos<br>por WhatsApp.</h2>
        <p>El mensaje incluirá unas líneas para que agregues el nombre de tu barbería y lo que necesitas.</p>
        <a class="button button-light" href="<?= e($contactBaseUrl) ?>?text=<?= e($whatsAppMessage) ?>" target="_blank" rel="noopener noreferrer">Solicitar cotización <span aria-hidden="true">↗</span></a>
      </div>
      <div class="contact-page-card">
        <p class="eyebrow">Para preparar la propuesta</p>
        <h2>Cuéntanos un poco.</h2>
        <ul>
          <li>El nombre y la zona de tu barbería.</li>
          <li>Los servicios y las funciones que quieres incluir, como reservas o perfiles del equipo.</li>
          <li>Si ya tienes logotipo, fotos, colores o una fecha para tener la página lista.</li>
        </ul>
      </div>
    </section>
    <section class="contact-page-back wrap">
      <p>¿Aún quieres elegir el estilo?</p>
      <a class="button button-outline" href="plantillas.php">Explorar las plantillas <span aria-hidden="true">↗</span></a>
    </section>
  </main>
  <footer class="footer wrap"><a class="footer-brand" href="index.php">SOLISWEB</a><span>Soluciones web para barberías · Lima, Perú</span><a href="index.php">Volver al inicio ↗</a></footer>
  <div class="checker" aria-hidden="true"></div>
</body>
</html>
