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
    $shopQuery = $pdo->prepare('SELECT id, nombre, slug, direccion, whatsapp FROM barberias WHERE slug = :slug AND activo = 1 LIMIT 1');
    $shopQuery->execute(['slug' => $slug]);
    $shop = $shopQuery->fetch();
    if (!$shop) {
        http_response_code(404);
        exit('Barbería no encontrada.');
    }
    $policyQuery = $pdo->prepare('SELECT politica_privacidad FROM ajustes_barberia WHERE barberia_id = :shop');
    $policyQuery->execute(['shop' => $shop['id']]);
    $customPolicy = $policyQuery->fetchColumn();
} catch (PDOException $exception) {
    http_response_code(503);
    exit('No se pudo cargar el aviso de privacidad. Revisa la conexión con MySQL.');
}

$policy = is_string($customPolicy) && trim($customPolicy) !== '' ? $customPolicy : default_privacy_policy();
$contact = is_string($shop['whatsapp'] ?? null) ? whatsapp_url($shop['whatsapp'], 'Hola, quisiera hacer una consulta sobre mis datos personales.') : null;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f3eee5"><title>Aviso de privacidad · <?= e($shop['nombre']) ?></title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="privacy-page">
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <header class="shop-header wrap"><a class="shop-logo" href="barberia.php?b=<?= e($shop['slug']) ?>"><?= e($shop['nombre']) ?><small>BARBERÍA</small></a><a class="shop-nav-link" href="barberia.php?b=<?= e($shop['slug']) ?>#reserva">Volver a reservar ↗</a></header>
  <main class="privacy-main wrap" id="contenido">
    <p class="eyebrow">Tus datos personales</p><h1>Aviso de privacidad.</h1>
    <p class="privacy-intro">Información sobre los datos que compartes al solicitar una cita en <?= e($shop['nombre']) ?>.</p>
    <section class="privacy-copy"><h2>Cómo se usan tus datos</h2><p><?= nl2br(e($policy)) ?></p><h2>Responsable del negocio</h2><p><?= e($shop['nombre']) ?> · <?= e($shop['direccion'] ?: 'Lima, Perú') ?></p>
      <?php if ($contact): ?><a class="button button-dark" href="<?= e($contact) ?>" target="_blank" rel="noopener noreferrer">Consultar por WhatsApp <span aria-hidden="true">↗</span></a><?php else: ?><p>Comunícate con la barbería para hacer una consulta sobre tus datos.</p><?php endif; ?>
    </section>
    <?php if (!is_string($customPolicy) || trim($customPolicy) === ''): ?><p class="privacy-reminder">El negocio aún no ha personalizado este aviso. Antes de recibir reservas reales, revisa y completa esta información desde el panel de administración.</p><?php endif; ?>
  </main>
  <footer class="admin-footer wrap"><a href="barberia.php?b=<?= e($shop['slug']) ?>">Volver a la página de la barbería</a><a href="index.php">OnlyFive</a></footer>
</body>
</html>
