<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/database.php';
require_once __DIR__ . '/../../app/admin.php';
require_admin();

function save_uploaded_image(string $field): ?string
{
    $file = $_FILES[$field] ?? null;
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new RuntimeException('No se pudo recibir la imagen. Vuelve a intentarlo.');
    }
    if (($file['size'] ?? 0) < 1 || $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Cada imagen debe pesar como máximo 5 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $imageInfo = @getimagesize($file['tmp_name']);
    if (!isset($extensions[$mime]) || !$imageInfo || $imageInfo['mime'] !== $mime
        || $imageInfo[0] > 8000 || $imageInfo[1] > 8000) {
        throw new RuntimeException('Usa una imagen JPG, PNG o WebP válida.');
    }

    $directory = __DIR__ . '/../uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('No se pudo guardar la imagen. Revisa permisos de escritura.');
    }
    return 'uploads/' . $filename;
}

function valid_admin_phone(string $value): bool
{
    return $value === '' || (preg_match('/^[0-9+().\- ]{7,30}$/', $value) === 1
        && strlen(preg_replace('/\D/', '', $value) ?? '') >= 7);
}

$error = '';
$settings = ['foto_portada' => null, 'foto_barbero' => null, 'politica_privacidad' => null];
$services = [];
$barbers = [];
$hours = [];
try {
    $pdo = db();
    $shopQuery = $pdo->query('SELECT id, nombre, slug, descripcion, direccion, whatsapp FROM barberias WHERE activo = 1 ORDER BY id LIMIT 1');
    $shop = $shopQuery->fetch() ?: null;
    if (!$shop) {
        throw new RuntimeException('No hay una barbería activa configurada.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('La sesión del formulario venció. Actualiza la página e inténtalo otra vez.');
        }
        $operation = $_POST['op'] ?? '';
        if ($operation === 'perfil') {
            $name = trim((string) ($_POST['nombre'] ?? ''));
            $description = trim((string) ($_POST['descripcion'] ?? ''));
            $address = trim((string) ($_POST['direccion'] ?? ''));
            $whatsapp = trim((string) ($_POST['whatsapp'] ?? ''));
            $privacy = trim((string) ($_POST['politica_privacidad'] ?? ''));
            if ($name === '' || strlen($name) > 120 || strlen($description) > 4000
                || strlen($address) > 180 || strlen($privacy) > 5000 || !valid_admin_phone($whatsapp)) {
                throw new RuntimeException('Revisa el nombre, dirección, WhatsApp o la longitud de los textos.');
            }

            $settingsQuery = $pdo->prepare('SELECT foto_portada, foto_barbero FROM ajustes_barberia WHERE barberia_id = :shop');
            $settingsQuery->execute(['shop' => $shop['id']]);
            $oldSettings = $settingsQuery->fetch() ?: ['foto_portada' => null, 'foto_barbero' => null];
            $cover = save_uploaded_image('foto_portada') ?? $oldSettings['foto_portada'];
            $barberPhoto = save_uploaded_image('foto_barbero') ?? $oldSettings['foto_barbero'];

            $pdo->beginTransaction();
            $update = $pdo->prepare(
                'UPDATE barberias SET nombre = :name, descripcion = :description, direccion = :address, whatsapp = :whatsapp WHERE id = :id'
            );
            $update->execute([
                'name' => $name,
                'description' => $description !== '' ? $description : null,
                'address' => $address !== '' ? $address : null,
                'whatsapp' => $whatsapp !== '' ? $whatsapp : null,
                'id' => $shop['id'],
            ]);
            $upsert = $pdo->prepare(
                'INSERT INTO ajustes_barberia (barberia_id, foto_portada, foto_barbero, politica_privacidad)
                 VALUES (:shop, :cover, :barber, :privacy)
                 ON DUPLICATE KEY UPDATE foto_portada = VALUES(foto_portada), foto_barbero = VALUES(foto_barbero), politica_privacidad = VALUES(politica_privacidad)'
            );
            $upsert->execute([
                'shop' => $shop['id'],
                'cover' => $cover,
                'barber' => $barberPhoto,
                'privacy' => $privacy !== '' ? $privacy : null,
            ]);
            $pdo->commit();
            header('Location: configuracion.php?guardado=perfil', true, 303);
            exit;
        }

        if ($operation === 'servicio') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $name = trim((string) ($_POST['nombre'] ?? ''));
            $description = trim((string) ($_POST['descripcion'] ?? ''));
            $duration = filter_var($_POST['duracion_min'] ?? null, FILTER_VALIDATE_INT);
            $price = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
            $active = isset($_POST['activo']) ? 1 : 0;
            if (!$id || $name === '' || strlen($name) > 120 || strlen($description) > 255
                || !$duration || $duration < 5 || $duration > 240 || $duration % 5 !== 0
                || $price === false || !is_finite((float) $price) || $price < 0 || $price > 999999) {
                throw new RuntimeException('Revisa el servicio, la duración y el precio.');
            }
            $update = $pdo->prepare(
                'UPDATE servicios SET nombre = :name, descripcion = :description, duracion_min = :duration, precio = :price, activo = :active
                 WHERE id = :id AND barberia_id = :shop'
            );
            $update->execute([
                'name' => $name, 'description' => $description !== '' ? $description : null,
                'duration' => $duration, 'price' => $price, 'active' => $active,
                'id' => $id, 'shop' => $shop['id'],
            ]);
            if ($update->rowCount() === 0) {
                $check = $pdo->prepare('SELECT id FROM servicios WHERE id = :id AND barberia_id = :shop');
                $check->execute(['id' => $id, 'shop' => $shop['id']]);
                if (!$check->fetch()) {
                    throw new RuntimeException('No se encontró el servicio.');
                }
            }
            header('Location: configuracion.php?guardado=servicio', true, 303);
            exit;
        }

        if ($operation === 'nuevo-servicio') {
            $name = trim((string) ($_POST['nombre'] ?? ''));
            $description = trim((string) ($_POST['descripcion'] ?? ''));
            $duration = filter_var($_POST['duracion_min'] ?? null, FILTER_VALIDATE_INT);
            $price = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($name === '' || strlen($name) > 120 || strlen($description) > 255
                || !$duration || $duration < 5 || $duration > 240 || $duration % 5 !== 0
                || $price === false || !is_finite((float) $price) || $price < 0 || $price > 999999) {
                throw new RuntimeException('Completa el nombre, una duración válida y un precio igual o mayor a cero.');
            }
            $insert = $pdo->prepare(
                'INSERT INTO servicios (barberia_id, nombre, descripcion, duracion_min, precio) VALUES (:shop, :name, :description, :duration, :price)'
            );
            $insert->execute([
                'shop' => $shop['id'], 'name' => $name, 'description' => $description !== '' ? $description : null,
                'duration' => $duration, 'price' => $price,
            ]);
            header('Location: configuracion.php?guardado=servicio', true, 303);
            exit;
        }

        if ($operation === 'barbero') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $name = trim((string) ($_POST['nombre'] ?? ''));
            $specialty = trim((string) ($_POST['especialidad'] ?? ''));
            $active = isset($_POST['activo']) ? 1 : 0;
            if (!$id || $name === '' || strlen($name) > 120 || strlen($specialty) > 160) {
                throw new RuntimeException('Revisa el nombre y la especialidad del barbero.');
            }
            $update = $pdo->prepare(
                'UPDATE barberos SET nombre = :name, especialidad = :specialty, activo = :active WHERE id = :id AND barberia_id = :shop'
            );
            $update->execute([
                'name' => $name, 'specialty' => $specialty !== '' ? $specialty : null,
                'active' => $active, 'id' => $id, 'shop' => $shop['id'],
            ]);
            if ($update->rowCount() === 0) {
                $check = $pdo->prepare('SELECT id FROM barberos WHERE id = :id AND barberia_id = :shop');
                $check->execute(['id' => $id, 'shop' => $shop['id']]);
                if (!$check->fetch()) {
                    throw new RuntimeException('No se encontró el barbero.');
                }
            }
            header('Location: configuracion.php?guardado=barbero', true, 303);
            exit;
        }

        if ($operation === 'nuevo-barbero') {
            $name = trim((string) ($_POST['nombre'] ?? ''));
            $specialty = trim((string) ($_POST['especialidad'] ?? ''));
            if ($name === '' || strlen($name) > 120 || strlen($specialty) > 160) {
                throw new RuntimeException('Escribe un nombre y una especialidad válida.');
            }
            $insert = $pdo->prepare(
                "INSERT INTO barberos (barberia_id, nombre, especialidad, foto) VALUES (:shop, :name, :specialty, 'assets/barbero-perfil.png')"
            );
            $insert->execute(['shop' => $shop['id'], 'name' => $name, 'specialty' => $specialty !== '' ? $specialty : null]);
            header('Location: configuracion.php?guardado=barbero', true, 303);
            exit;
        }

        if ($operation === 'horarios') {
            $barberId = filter_var($_POST['barbero_id'] ?? null, FILTER_VALIDATE_INT);
            $barberCheck = $pdo->prepare('SELECT id FROM barberos WHERE id = :id AND barberia_id = :shop');
            $barberCheck->execute(['id' => $barberId, 'shop' => $shop['id']]);
            if (!$barberId || !$barberCheck->fetch()) {
                throw new RuntimeException('No se encontró el barbero para actualizar sus horarios.');
            }

            $pdo->beginTransaction();
            $delete = $pdo->prepare('DELETE FROM horarios WHERE barbero_id = :barber AND dia_semana = :day');
            $upsert = $pdo->prepare(
                'INSERT INTO horarios (barbero_id, dia_semana, abre, cierra) VALUES (:barber, :day, :opens, :closes)
                 ON DUPLICATE KEY UPDATE abre = VALUES(abre), cierra = VALUES(cierra)'
            );
            for ($day = 0; $day <= 6; $day++) {
                $opens = trim((string) ($_POST['abre'][$day] ?? ''));
                $closes = trim((string) ($_POST['cierra'][$day] ?? ''));
                if ($opens === '' && $closes === '') {
                    $delete->execute(['barber' => $barberId, 'day' => $day]);
                    continue;
                }
                if (!preg_match('/^\d{2}:\d{2}$/', $opens) || !preg_match('/^\d{2}:\d{2}$/', $closes)
                    || $opens >= $closes || (int) substr($opens, 0, 2) > 23 || (int) substr($closes, 0, 2) > 23
                    || (int) substr($opens, 3, 2) > 59 || (int) substr($closes, 3, 2) > 59) {
                    throw new RuntimeException('Cada día debe tener una hora de apertura y otra de cierre válidas. Deja ambas vacías para marcarlo como cerrado.');
                }
                $upsert->execute(['barber' => $barberId, 'day' => $day, 'opens' => $opens, 'closes' => $closes]);
            }
            $pdo->commit();
            header('Location: configuracion.php?guardado=horarios', true, 303);
            exit;
        }

        throw new RuntimeException('La acción solicitada no es válida.');
    }

    $settingsQuery = $pdo->prepare('SELECT * FROM ajustes_barberia WHERE barberia_id = :shop');
    $settingsQuery->execute(['shop' => $shop['id']]);
    $settings = $settingsQuery->fetch() ?: ['foto_portada' => null, 'foto_barbero' => null, 'politica_privacidad' => null];
    $serviceQuery = $pdo->prepare('SELECT * FROM servicios WHERE barberia_id = :shop ORDER BY activo DESC, nombre');
    $serviceQuery->execute(['shop' => $shop['id']]);
    $services = $serviceQuery->fetchAll();
    $barberQuery = $pdo->prepare('SELECT * FROM barberos WHERE barberia_id = :shop ORDER BY activo DESC, nombre');
    $barberQuery->execute(['shop' => $shop['id']]);
    $barbers = $barberQuery->fetchAll();
    $hoursQuery = $pdo->prepare(
        'SELECT h.* FROM horarios h INNER JOIN barberos b ON b.id = h.barbero_id WHERE b.barberia_id = :shop'
    );
    $hoursQuery->execute(['shop' => $shop['id']]);
    $hours = [];
    foreach ($hoursQuery->fetchAll() as $hour) {
        $hours[(int) $hour['barbero_id']][(int) $hour['dia_semana']] = $hour;
    }
} catch (RuntimeException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = $exception->getMessage();
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(503);
    $error = 'No se pudo guardar o cargar la configuración. Revisa la base de datos e inténtalo de nuevo.';
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = 'No se pudo completar la acción. Revisa los datos e inténtalo de nuevo.';
}

$dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f3eee5"><title>Configuración · OnlyFive</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="admin-page">
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <header class="admin-header"><div class="wrap admin-header-inner"><a class="wordmark admin-wordmark" href="index.php">ONLYFIVE</a><div><span>Panel de <?= e($shop['nombre'] ?? 'barbería') ?></span><a class="admin-link-button" href="index.php">Volver a reservas</a></div></div></header>
  <main class="wrap admin-main" id="contenido">
    <div class="admin-title-row"><div><p class="eyebrow">Tu negocio, tu información</p><h1>Configuración</h1><p>Actualiza lo que tus clientes ven en la página.</p></div><a class="button button-outline" href="../barberia.php?b=<?= e($shop['slug'] ?? 'barberia-demo') ?>" target="_blank" rel="noopener">Ver página pública ↗</a></div>
    <?php if ($error !== ''): ?><p class="admin-alert" role="alert"><?= e($error) ?></p><?php endif; ?>
    <?php if (isset($_GET['guardado'])): ?><p class="admin-success" role="status">Los cambios se guardaron.</p><?php endif; ?>
    <?php if (!isset($shop)): ?><section class="admin-empty"><h2>Configura la base de datos</h2><p>Importa el esquema y la migración siguiendo el README.</p></section><?php else: ?>
    <section class="admin-settings-card">
      <div class="admin-section-heading"><p class="eyebrow">Perfil público</p><h2>Datos de la barbería</h2><p>Estos datos aparecen en la página de reservas y en el aviso de privacidad.</p></div>
      <form class="admin-settings-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="op" value="perfil">
        <label>Nombre de la barbería<input name="nombre" maxlength="120" value="<?= e($shop['nombre']) ?>" required></label>
        <label>Descripción<textarea name="descripcion" rows="3" maxlength="4000"><?= e($shop['descripcion'] ?? '') ?></textarea></label>
        <label>Dirección o zona<input name="direccion" maxlength="180" value="<?= e($shop['direccion'] ?? '') ?>"></label>
        <label>WhatsApp de la barbería<input name="whatsapp" type="tel" maxlength="30" value="<?= e($shop['whatsapp'] ?? '') ?>" placeholder="+51 999 123 456"><small>Escribe el número con código de país para que el enlace abra la conversación correcta.</small></label>
        <div class="admin-upload-grid">
          <label>Foto de portada <?php if (!empty($settings['foto_portada'])): ?><img class="admin-image-preview" src="../<?= e($settings['foto_portada']) ?>" alt="Portada actual"><?php endif; ?><input type="file" name="foto_portada" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG o WebP · máximo 5 MB.</small></label>
          <label>Foto del barbero <?php if (!empty($settings['foto_barbero'])): ?><img class="admin-image-preview" src="../<?= e($settings['foto_barbero']) ?>" alt="Foto del barbero actual"><?php endif; ?><input type="file" name="foto_barbero" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG o WebP · máximo 5 MB.</small></label>
        </div>
        <label>Aviso de privacidad personalizado<textarea name="politica_privacidad" rows="5" maxlength="5000" placeholder="Quién recibe los datos, para qué los usa, cuánto tiempo los conserva y cómo solicitar su eliminación."><?= e($settings['politica_privacidad'] ?? '') ?></textarea><small>Si lo dejas vacío, se mostrará el aviso general del proyecto. Revísalo y ajústalo a tu negocio antes de publicarlo.</small></label>
        <button class="button button-dark" type="submit">Guardar datos <span aria-hidden="true">↗</span></button>
      </form>
    </section>

    <section class="admin-settings-card">
      <div class="admin-section-heading"><p class="eyebrow">Catálogo</p><h2>Servicios y precios</h2><p>Desactiva un servicio para ocultarlo sin borrar el historial de reservas.</p></div>
      <?php foreach ($services as $service): ?>
        <form class="admin-record-form" method="post">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="op" value="servicio"><input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
          <label>Servicio<input name="nombre" maxlength="120" value="<?= e($service['nombre']) ?>" required></label>
          <label>Descripción<input name="descripcion" maxlength="255" value="<?= e($service['descripcion'] ?? '') ?>"></label>
          <label>Duración (minutos)<input name="duracion_min" type="number" min="5" max="240" step="5" value="<?= (int) $service['duracion_min'] ?>" required></label>
          <label>Precio (S/)<input name="precio" type="number" min="0" max="999999" step="0.01" value="<?= e((string) $service['precio']) ?>" required></label>
          <label class="admin-checkbox"><input name="activo" type="checkbox" value="1" <?= (int) $service['activo'] === 1 ? 'checked' : '' ?>> Publicado</label>
          <button class="button button-outline" type="submit">Guardar servicio</button>
        </form>
      <?php endforeach; ?>
      <details class="admin-add-details"><summary>Agregar un servicio</summary>
        <form class="admin-record-form" method="post">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="op" value="nuevo-servicio">
          <label>Nombre<input name="nombre" maxlength="120" required></label><label>Descripción<input name="descripcion" maxlength="255"></label>
          <label>Duración (minutos)<input name="duracion_min" type="number" min="5" max="240" step="5" value="30" required></label>
          <label>Precio (S/)<input name="precio" type="number" min="0" max="999999" step="0.01" value="0" required></label><button class="button button-dark" type="submit">Agregar servicio</button>
        </form>
      </details>
    </section>

    <section class="admin-settings-card">
      <div class="admin-section-heading"><p class="eyebrow">Equipo y agenda</p><h2>Barberos y horarios</h2><p>La disponibilidad pública se calcula con estos horarios y las reservas pendientes o confirmadas.</p></div>
      <?php foreach ($barbers as $barber): ?>
        <div class="admin-barber-settings">
          <form class="admin-record-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="op" value="barbero"><input type="hidden" name="id" value="<?= (int) $barber['id'] ?>">
            <label>Nombre<input name="nombre" maxlength="120" value="<?= e($barber['nombre']) ?>" required></label>
            <label>Especialidad<input name="especialidad" maxlength="160" value="<?= e($barber['especialidad'] ?? '') ?>"></label>
            <label class="admin-checkbox"><input name="activo" type="checkbox" value="1" <?= (int) $barber['activo'] === 1 ? 'checked' : '' ?>> Publicado</label>
            <button class="button button-outline" type="submit">Guardar barbero</button>
          </form>
          <form class="admin-hours-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="op" value="horarios"><input type="hidden" name="barbero_id" value="<?= (int) $barber['id'] ?>">
            <h3>Horario semanal · <?= e($barber['nombre']) ?></h3>
            <?php for ($day = 0; $day <= 6; $day++): $entry = $hours[(int) $barber['id']][$day] ?? null; ?>
              <div class="admin-hour-row"><strong><?= e($dayNames[$day]) ?></strong><label><span>Apertura</span><input type="time" name="abre[<?= $day ?>]" value="<?= e($entry ? substr($entry['abre'], 0, 5) : '') ?>"></label><label><span>Cierre</span><input type="time" name="cierra[<?= $day ?>]" value="<?= e($entry ? substr($entry['cierra'], 0, 5) : '') ?>"></label></div>
            <?php endfor; ?>
            <small>Deja las dos horas vacías para marcar el día como cerrado.</small><button class="button button-dark" type="submit">Guardar horarios</button>
          </form>
        </div>
      <?php endforeach; ?>
      <details class="admin-add-details"><summary>Agregar un barbero</summary>
        <form class="admin-record-form" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="op" value="nuevo-barbero">
          <label>Nombre<input name="nombre" maxlength="120" required></label><label>Especialidad<input name="especialidad" maxlength="160"></label><button class="button button-dark" type="submit">Agregar barbero</button>
        </form>
      </details>
    </section>
    <p class="admin-footnote">Puedes reemplazar las fotos aquí; las nuevas imágenes se guardan en `public/uploads`. Protege esta cuenta y usa HTTPS al publicar.</p>
    <?php endif; ?>
  </main>
  <footer class="admin-footer wrap"><a href="index.php">Volver a reservas</a><a href="../privacidad.php?b=<?= e($shop['slug'] ?? 'barberia-demo') ?>">Aviso de privacidad</a></footer>
</body>
</html>
