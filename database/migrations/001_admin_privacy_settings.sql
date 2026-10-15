USE onlyfive;

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
