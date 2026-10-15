# OnlyFive · páginas y reservas para barberías

Aplicación PHP y MySQL para mostrar una barbería, recibir solicitudes de cita y administrar su agenda. La página conserva el estilo visual de OnlyFive y se adapta a celulares.

## Funciones

- Página pública de OnlyFive en `public/index.php`.
- Galería propia de tres plantillas en `public/plantillas.php`, con composiciones clásicas, urbanas y minimalistas diferentes.
- Secciones de paquetes con cotización personalizada y preguntas frecuentes; no se muestran precios inventados.
- Página de la barbería en `public/barberia.php?b=barberia-demo`.
- Horarios disponibles calculados según servicio, duración, horario del barbero y reservas pendientes o confirmadas.
- Reserva con validación de horario y conflictos en el servidor, token CSRF y consentimiento de privacidad registrado.
- Panel privado en `public/admin/` para confirmar, cancelar y marcar reservas como atendidas. Incluye enlaces de WhatsApp con mensajes preparados para que la persona encargada los revise y envíe.
- Configuración de datos de la barbería, fotos, servicios, precios, equipo, horarios semanales y aviso de privacidad.
- Protección de inicio de sesión, bloqueo temporal tras cinco intentos fallidos y cierre de sesión por inactividad.
- Metadatos para compartir enlaces y datos estructurados de cada barbería, generados con su información configurada.
- Imágenes de muestra optimizadas en WebP y carga diferida para fotos fuera de la primera pantalla.

Los mensajes de WhatsApp se preparan, pero no se envían solos. Para automatizar mensajes se necesitaría integrar la API oficial de WhatsApp Business.

## Requisitos

- PHP 8.0 o superior con PDO, PDO MySQL y Fileinfo habilitados.
- MySQL 5.7 o superior o MariaDB 10.3 o superior.
- Un servidor web que apunte a `public/` como raíz del sitio.

## Primera instalación

Desde la carpeta del proyecto, importa el esquema y los datos de demostración:

```bash
mysql -u root -p < database/schema.sql
```

Configura la conexión en el entorno del proceso de PHP. En Bash:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_DATABASE=onlyfive
export DB_USERNAME=root
export DB_PASSWORD='tu_clave_local'
```

Si usas XAMPP con la configuración local predeterminada, MySQL suele usar `root` sin contraseña. No uses esa cuenta en producción; crea un usuario limitado a la base `onlyfive`.

## Actualizar una instalación existente

Si ya tienes la base de datos `onlyfive`, ejecuta la migración para agregar las tablas del panel, las fotos y el registro del consentimiento:

```bash
mysql -u root -p < database/migrations/001_admin_privacy_settings.sql
```

No es necesario borrar la base de datos ni volver a importar los datos de ejemplo.

## Configurar el panel de administración

El panel no tiene una contraseña predeterminada. Genera una contraseña hash desde PHP:

```bash
php -r "echo password_hash('escribe-aqui-una-clave-larga', PASSWORD_DEFAULT), PHP_EOL;"
```

Guarda el hash y configura `ADMIN_USERNAME` y `ADMIN_PASSWORD_HASH` en el entorno del servidor. En Bash, antes de iniciar el servidor local:

```bash
export ADMIN_USERNAME=admin
export ADMIN_PASSWORD_HASH='pega_aqui_el_hash_generado'
export WHATSAPP_COUNTRY_CODE=51
```

No guardes el hash ni una contraseña en GitHub. En producción, configura estas variables en el panel seguro de tu hosting. El panel queda deshabilitado si faltan las credenciales.

Para configurar el teléfono de contacto del sitio comercial de OnlyFive, opcionalmente define `ONLYFIVE_WHATSAPP` con el número internacional, solo con dígitos. El WhatsApp de la barbería se edita desde **Panel → Configuración**. `WHATSAPP_COUNTRY_CODE` se usa para completar teléfonos locales de nueve dígitos en los enlaces de notificación; el valor predeterminado es `51` (Perú).

## Ejecutar localmente

Desde la carpeta del proyecto:

```bash
php -S 127.0.0.1:8000 -t public
```

Abre estas direcciones:

- `http://127.0.0.1:8000/` — página de OnlyFive.
- `http://127.0.0.1:8000/plantillas.php` — galería de diseños y vistas de ejemplo.
- `http://127.0.0.1:8000/barberia.php?b=barberia-demo` — página de reservas.
- `http://127.0.0.1:8000/admin/` — inicio de sesión del panel.
- `http://127.0.0.1:8000/privacidad.php?b=barberia-demo` — aviso de privacidad.

Con XAMPP, inicia MySQL y ejecuta el servidor PHP integrado desde la carpeta del proyecto, o configura Apache para usar `public/` como DocumentRoot. Para el servidor integrado en PowerShell, usa `& C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public` después de definir allí las variables de entorno.

## Configurar el negocio

Inicia sesión en `public/admin/` y abre **Configurar la página**. Ahí puedes cambiar el nombre, descripción, dirección, WhatsApp y aviso de privacidad; cargar fotos JPG, PNG o WebP de hasta 5 MB; editar o agregar servicios y barberos; y actualizar sus horarios. Las imágenes cargadas se guardan en `public/uploads/`, por lo que el usuario del servidor PHP necesita permiso de escritura en esa carpeta.

Los registros iniciales son ejemplos. Actualízalos antes de compartir la página con clientes. Si el aviso de privacidad queda vacío, se muestra un texto general que todavía debe revisarse con la persona responsable del negocio. Las cotizaciones, el dominio y el alojamiento se deben acordar con cada cliente antes de iniciar el trabajo.

## Privacidad y seguridad

- El formulario pide consentimiento y registra la versión del aviso aceptada.
- Solo el personal con credenciales configuradas puede acceder a datos de clientes.
- El inicio de sesión se bloquea por 15 minutos después de cinco intentos fallidos desde la misma dirección de red; se guarda una huella con hash y no la dirección IP en texto.
- Las reservas guardan nombre y teléfono para administrar la cita. Define y cumple un período de conservación apropiado para tu negocio; solicita asesoría local para adaptar el aviso a tus obligaciones legales.
- Activa HTTPS antes de publicar. Mantén PHP, MySQL y el hosting actualizados, limita los permisos de base de datos y configura copias de seguridad protegidas.

## Estructura

```text
app/                 sesiones, validación, conexión y autenticación
database/            esquema y migraciones
public/              páginas, API de horarios, panel y recursos
public/uploads/       imágenes cargadas desde el panel (no se suben a Git)
```
