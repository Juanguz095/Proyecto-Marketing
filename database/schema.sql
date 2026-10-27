CREATE DATABASE IF NOT EXISTS onlyfive
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE onlyfive;

CREATE TABLE IF NOT EXISTS barberias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  descripcion TEXT NULL,
  direccion VARCHAR(180) NULL,
  whatsapp VARCHAR(20) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS barberos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  barberia_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  especialidad VARCHAR(160) NULL,
  foto VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_barberos_barberia FOREIGN KEY (barberia_id)
    REFERENCES barberias(id) ON DELETE CASCADE,
  INDEX idx_barberos_barberia_activo (barberia_id, activo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS servicios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  barberia_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NULL,
  duracion_min SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  precio DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT fk_servicios_barberia FOREIGN KEY (barberia_id)
    REFERENCES barberias(id) ON DELETE CASCADE,
  INDEX idx_servicios_barberia_activo (barberia_id, activo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS horarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  barbero_id INT UNSIGNED NOT NULL,
  dia_semana TINYINT UNSIGNED NOT NULL COMMENT '0 domingo; 6 sábado',
  abre TIME NOT NULL,
  cierra TIME NOT NULL,
  UNIQUE KEY uq_horario_barbero_dia (barbero_id, dia_semana),
  CONSTRAINT fk_horarios_barbero FOREIGN KEY (barbero_id)
    REFERENCES barberos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  barberia_id INT UNSIGNED NOT NULL,
  barbero_id INT UNSIGNED NOT NULL,
  servicio_id INT UNSIGNED NOT NULL,
  cliente_nombre VARCHAR(120) NOT NULL,
  cliente_telefono VARCHAR(30) NOT NULL,
  inicio DATETIME NOT NULL,
  fin DATETIME NOT NULL,
  estado ENUM('pendiente','confirmada','cancelada','atendida') NOT NULL DEFAULT 'pendiente',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reservas_barberia FOREIGN KEY (barberia_id)
    REFERENCES barberias(id) ON DELETE CASCADE,
  CONSTRAINT fk_reservas_barbero FOREIGN KEY (barbero_id)
    REFERENCES barberos(id),
  CONSTRAINT fk_reservas_servicio FOREIGN KEY (servicio_id)
    REFERENCES servicios(id),
  INDEX idx_reservas_agenda (barbero_id, inicio, fin, estado),
  INDEX idx_reservas_barberia (barberia_id, creado_en)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ajustes_barberia (
  barberia_id INT UNSIGNED PRIMARY KEY,
  foto_portada VARCHAR(255) NULL,
  foto_barbero VARCHAR(255) NULL,
  politica_privacidad TEXT NULL,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ajustes_barberia FOREIGN KEY (barberia_id)
    REFERENCES barberias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consentimientos_reserva (
  reserva_id BIGINT UNSIGNED PRIMARY KEY,
  version_politica VARCHAR(40) NOT NULL,
  aceptado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_consentimiento_reserva FOREIGN KEY (reserva_id)
    REFERENCES reservas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fingerprint CHAR(64) NOT NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admin_login_fingerprint_time (fingerprint, attempted_at)
) ENGINE=InnoDB;

-- Datos de muestra: reemplázalos por la información real antes de usar la web.
INSERT INTO barberias (id, nombre, slug, descripcion, direccion, whatsapp)
VALUES (1, 'Barbería Demo', 'barberia-demo',
        'Contenido de ejemplo para probar la plantilla de Solisweb.',
        'Lima, Perú', NULL)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO barberos (id, barberia_id, nombre, especialidad, foto)
VALUES (1, 1, 'Barbero de muestra', 'Cortes clásicos y degradados', 'assets/barbero-perfil.png')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO servicios (id, barberia_id, nombre, descripcion, duracion_min, precio)
VALUES
  (1, 1, 'Corte clásico', 'Un corte a tu estilo.', 30, 25.00),
  (2, 1, 'Corte y barba', 'Corte más perfilado de barba.', 45, 38.00),
  (3, 1, 'Perfilado de barba', 'Definición y acabado de barba.', 20, 18.00)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO horarios (barbero_id, dia_semana, abre, cierra)
VALUES
  (1, 1, '09:00:00', '19:00:00'),
  (1, 2, '09:00:00', '19:00:00'),
  (1, 3, '09:00:00', '19:00:00'),
  (1, 4, '09:00:00', '19:00:00'),
  (1, 5, '09:00:00', '19:00:00'),
  (1, 6, '09:00:00', '17:00:00')
ON DUPLICATE KEY UPDATE abre = VALUES(abre), cierra = VALUES(cierra);
