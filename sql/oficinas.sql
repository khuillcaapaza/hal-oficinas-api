-- Esquema del portal de gestión de oficinas (hal-oficinas-api)
-- Ejecutar en MySQL/MariaDB sobre la BD haladminweb_oficinas_db.
--
-- Modelo: oficina (datos informativos) → autoridades, secciones (botones de
-- acción) y enlaces (documentos o URLs). Reemplaza el contenido Markdown
-- estático de hal-site (content/oficinas/*.md).

-- Oficina = ficha informativa (cabecera, contacto, ubicación, contenido).
CREATE TABLE IF NOT EXISTS oficinas (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid              CHAR(36)     NULL,
  slug              VARCHAR(160) NOT NULL,
  titulo            VARCHAR(200) NOT NULL,
  categoria         VARCHAR(120) NOT NULL DEFAULT 'Oficina',
  excerpt           VARCHAR(500) NOT NULL DEFAULT '',
  contenido         MEDIUMTEXT   NOT NULL,              -- cuerpo en Markdown
  contacto_telefono VARCHAR(60)  NOT NULL DEFAULT '',
  contacto_email    VARCHAR(160) NOT NULL DEFAULT '',
  contacto_horario  VARCHAR(200) NOT NULL DEFAULT '',
  ubic_edificio     VARCHAR(200) NOT NULL DEFAULT '',
  ubic_piso         VARCHAR(120) NOT NULL DEFAULT '',
  ubic_referencia   VARCHAR(300) NOT NULL DEFAULT '',
  ubic_map_url      VARCHAR(500) NOT NULL DEFAULT '',
  orden             INT          NOT NULL DEFAULT 0,
  publicado         TINYINT(1)   NOT NULL DEFAULT 1,
  creado_en         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_oficinas_uuid (uuid),
  UNIQUE KEY uq_oficinas_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Autoridad = persona a cargo mostrada en la ficha de la oficina.
CREATE TABLE IF NOT EXISTS oficina_autoridades (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  oficina_id INT UNSIGNED NOT NULL,
  cargo      VARCHAR(200) NOT NULL,
  nombre     VARCHAR(200) NOT NULL DEFAULT '',
  orden      INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_autoridades_oficina (oficina_id),
  CONSTRAINT fk_autoridades_oficina FOREIGN KEY (oficina_id)
    REFERENCES oficinas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sección = botón de acción al pie de la oficina (lleva a un grupo de enlaces).
CREATE TABLE IF NOT EXISTS oficina_secciones (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  oficina_id   INT UNSIGNED NOT NULL,
  uuid         CHAR(36)     NULL,
  slug         VARCHAR(160) NOT NULL,   -- también sirve como subcarpeta física por defecto
  titulo       VARCHAR(200) NOT NULL,
  descripcion  VARCHAR(500) NOT NULL DEFAULT '',
  icono        VARCHAR(40)  NOT NULL DEFAULT 'documents',
  url_externa  VARCHAR(500) NULL,       -- si está, la sección enlaza fuera
  orden        INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_secciones_uuid (uuid),
  KEY idx_secciones_oficina (oficina_id),
  CONSTRAINT fk_secciones_oficina FOREIGN KEY (oficina_id)
    REFERENCES oficinas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enlace = documento (archivo) o URL dentro de la oficina o de una sección.
CREATE TABLE IF NOT EXISTS oficina_enlaces (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  oficina_id     INT UNSIGNED NOT NULL,
  seccion_id     INT UNSIGNED NULL,     -- NULL = cuelga directamente de la oficina
  titulo         VARCHAR(255) NOT NULL,
  tipo           ENUM('archivo','enlace') NOT NULL DEFAULT 'archivo',
  url            VARCHAR(500) NOT NULL,          -- URL pública (archivo o enlace externo)
  subcarpeta     VARCHAR(160) NULL,              -- carpeta física en archivos-api (tipo=archivo)
  nombre_archivo VARCHAR(255) NULL,              -- nombre en disco (tipo=archivo, para relay delete)
  ext            VARCHAR(10)  NULL,
  tamano         INT UNSIGNED NOT NULL DEFAULT 0,
  fecha          DATE         NULL,
  orden          INT          NOT NULL DEFAULT 0,
  publicado      TINYINT(1)   NOT NULL DEFAULT 1,
  managed        TINYINT(1)   NOT NULL DEFAULT 1, -- 1=subido vía archivos-api (relay al borrar)
  creado_en      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_enlaces_oficina (oficina_id),
  KEY idx_enlaces_seccion (seccion_id),
  CONSTRAINT fk_enlaces_oficina FOREIGN KEY (oficina_id)
    REFERENCES oficinas(id) ON DELETE CASCADE,
  CONSTRAINT fk_enlaces_seccion FOREIGN KEY (seccion_id)
    REFERENCES oficina_secciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
