# OnlyFive — primera versión PHP + MySQL

Aplicación web para mostrar una barbería de ejemplo y recibir solicitudes de reserva. Está separada del prototipo estático publicado: un servidor estático no ejecuta PHP.

## Qué incluye

- Landing pública de OnlyFive en `public/index.php`.
- Página de ejemplo que carga servicios, precios, barberos y horarios desde MySQL: `public/barberia.php?b=barberia-demo`.
- Formulario que guarda solicitudes como `pendiente` y comprueba horario y cruces de citas.
- Consultas PDO preparadas, validación del lado servidor y token CSRF.
- Datos de muestra claramente identificados para reemplazar antes de usarla con una barbería real.

Esta primera versión no incluye un panel de administración. Las solicitudes quedan guardadas en `reservas`; todavía no hay pantalla para que la barbería confirme, edite o cancele una cita.

## Requisitos

- PHP 8 o superior con PDO y el controlador PDO MySQL habilitados.
- MySQL o MariaDB.
- La extensión `mbstring` no es necesaria.

## Preparar la base de datos

Desde la carpeta `php-app`, importa el esquema y los datos de muestra:

```bash
mysql -u root -p < database/schema.sql
```

Los precios, nombres y horario insertados son de demostración. Modifícalos antes de ofrecer la página a una barbería.

## Configurar la conexión

La aplicación lee la conexión desde variables de entorno. En una terminal Bash local:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_DATABASE=onlyfive
export DB_USERNAME=root
export DB_PASSWORD='tu_clave_local'
```

Para producción, crea un usuario de MySQL limitado a la base `onlyfive`; evita usar la cuenta `root` y no guardes contraseñas en el código.

## Ejecutar localmente

```bash
php -S 127.0.0.1:8000 -t public
```

Abre `http://127.0.0.1:8000` para la landing de OnlyFive o `http://127.0.0.1:8000/barberia.php?b=barberia-demo` para probar la plantilla con catálogo y reservas.

## Estructura

```text
app/                 conexión PDO, sesión y funciones comunes
database/schema.sql  tablas y contenido de muestra
public/              páginas PHP y recursos estáticos
```

## Antes de publicar

1. Sustituye los datos de demostración, fotos, teléfono, precios y horarios.
2. Crea un usuario de base de datos con permisos mínimos y activa HTTPS.
3. Añade un panel privado para revisar y confirmar solicitudes.
4. Configura copias de seguridad de MySQL y una política de privacidad para los datos de clientes.

La versión publicada en Sites es el prototipo estático. Para ejecutar esta aplicación se necesita un hosting que soporte PHP y MySQL/MariaDB.
