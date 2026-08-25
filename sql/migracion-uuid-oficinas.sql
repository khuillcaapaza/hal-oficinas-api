-- Migración: UUID interno para oficinas y secciones.
-- Ejecutar una vez sobre una base existente de hal-oficinas-api.

ALTER TABLE oficinas
	ADD COLUMN uuid CHAR(36) NULL AFTER id;

ALTER TABLE oficinas
	ADD UNIQUE KEY uq_oficinas_uuid (uuid);

UPDATE oficinas
SET uuid = UUID()
WHERE uuid IS NULL OR uuid = '';

ALTER TABLE oficina_secciones
	ADD COLUMN uuid CHAR(36) NULL AFTER oficina_id;

ALTER TABLE oficina_secciones
	ADD UNIQUE KEY uq_secciones_uuid (uuid);

UPDATE oficina_secciones
SET uuid = UUID()
WHERE uuid IS NULL OR uuid = '';
