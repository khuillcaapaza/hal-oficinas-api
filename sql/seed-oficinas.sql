-- Seed de oficinas generado desde hal-site/content/oficinas/*.md
-- y las subsecciones de gestión de calidad (hal-archivos-api).
-- Idempotente: limpia las tablas antes de insertar.
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE oficina_enlaces;
TRUNCATE TABLE oficina_secciones;
TRUNCATE TABLE oficina_autoridades;
TRUNCATE TABLE oficinas;
SET FOREIGN_KEY_CHECKS = 1;

-- ── administracion ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('administracion', 'Oficina de Administración', 'Órgano de Apoyo', 'Gestión integral de recursos, presupuesto y trámites administrativos del hospital.', 'La **Oficina de Administración** es responsable de la gestión integral de los recursos administrativos y financieros del hospital.

## Funciones principales

- Administrar recursos financieros y presupuestarios.
- Coordinar trámites administrativos y de personal.
- Gestionar documentación oficial del hospital.
- Supervisar la aplicación de normas administrativas.
- Coordinar con oficinas internas y entidades externas.

## Servicios disponibles

- Atención de solicitudes y trámites administrativos
- Información sobre procesos internos
- Asesoramiento en asuntos administrativos

## Recomendaciones

Para acelerar tus trámites, presenta toda la documentación requerida en una sola visita y solicita cita previa si es necesario.', '084 224841', 'administracion@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón Administrativo', '2.° piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 2, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'administracion'), 'Jefe(a) de la Oficina de Administración', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'administracion'), NULL, 'Portal de Transparencia', 'enlace', 'https://www.transparencia.gob.pe/', 1, 0, 0);

-- ── comunicaciones-imagen ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('comunicaciones-imagen', 'Unidad de Comunicaciones e Imagen Institucional', 'Órgano de Asesoramiento', 'Gestión de comunicaciones internas y externas, imagen institucional y relaciones públicas.', 'La **Unidad de Comunicaciones e Imagen Institucional** es responsable de gestionar la comunicación interna, externa y la imagen pública del hospital.

## Funciones principales

- Diseñar y ejecutar estrategias de comunicación institucional.
- Mantener y actualizar canales de comunicación interna y externa.
- Gestionar relaciones con medios de comunicación.
- Elaborar comunicados y notas de prensa.
- Supervisar la identidad visual del hospital.

## Canales de comunicación

- Sitio web institucional: hospitalantoniolorena.gob.pe
- Redes sociales: Facebook, Twitter, Instagram
- Comunicados internos y externos
- Boletines informativos

## Contacto

Para consultas de prensa o información institucional, contacta a nuestra unidad.', '084 224841', 'comunicaciones@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón Administrativo', '3.er piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 10, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'comunicaciones-imagen'), 'Jefe(a) de la Unidad de Comunicaciones', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'comunicaciones-imagen'), NULL, 'Redes sociales', 'enlace', 'https://www.facebook.com/hospitalantoniolorena', 1, 0, 0);

-- ── direccion-ejecutiva ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('direccion-ejecutiva', 'Dirección Ejecutiva', 'Órgano de Dirección', 'Máxima autoridad ejecutiva responsable de la conducción estratégica y operativa del hospital.', 'La **Dirección Ejecutiva** es el órgano responsable de la conducción estratégica, administrativa y operativa del Hospital Antonio Lorena del Cusco.

## Funciones principales

- Formular y ejecutar la política institucional del hospital.
- Supervisar la implementación de planes estratégicos y operativos.
- Aprobar presupuestos y políticas institucionales.
- Representar legalmente al hospital ante entidades externas.
- Garantizar la calidad y eficiencia de los servicios de salud.
- Coordinar con autoridades sanitarias y locales.

## Misión

Conducir el Hospital Antonio Lorena hacia el logro de sus objetivos institucionales, garantizando la prestación de servicios de salud de calidad con eficiencia y responsabilidad social.

## Horario de atención

La dirección atiende consultas y solicitudes administrativas en el horario establecido. Se recomienda solicitar cita previa para abordar temas específicos.', '084 224841', 'direccion@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 5:00 p.m.', 'Pabellón Administrativo', '3.er piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 1, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'direccion-ejecutiva'), 'Director(a) Ejecutivo(a)', 'Nombre por asignar', 0);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'direccion-ejecutiva'), 'Subdirector(a)', 'Nombre por asignar', 1);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'direccion-ejecutiva'), NULL, 'Estatuto Institucional', 'enlace', 'https://www.transparencia.gob.pe/', 1, 0, 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'direccion-ejecutiva'), NULL, 'Plan Estratégico', 'enlace', 'https://www.transparencia.gob.pe/', 1, 0, 1);

-- ── epidemiologia-ambiental ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('epidemiologia-ambiental', 'Unidad de Epidemiología, Salud Ambiental y Ocupacional', 'Órgano de Asesoramiento', 'Vigilancia epidemiológica, salud ambiental, ocupacional y prevención de riesgos laborales.', 'La **Unidad de Epidemiología, Salud Ambiental y Ocupacional** es responsable de la vigilancia epidemiológica, gestión de riesgos de salud ambiental y ocupacional del hospital.

## Funciones principales

- Ejercer vigilancia epidemiológica de enfermedades transmisibles.
- Promover prácticas de salud ambiental y ocupacional.
- Prevenir riesgos de accidentes y enfermedades ocupacionales.
- Coordinar programas de vacunación y prevención.
- Asesorar en bioseguridad y manejo de residuos.

## Áreas de trabajo

- Vigilancia epidemiológica
- Salud ocupacional del personal
- Gestión ambiental y residuos
- Bioseguridad y control de infecciones
- Salud mental ocupacional

## Prevención

Promovemos cultura de prevención y autocuidado entre el personal y la comunidad.', '084 224841', 'epidemiologia@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón Administrativo', '2.° piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 14, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'epidemiologia-ambiental'), 'Jefe(a) de la Unidad de Epidemiología y Salud Ambiental', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'epidemiologia-ambiental'), NULL, 'Protección de la salud ocupacional', 'enlace', 'https://www.sunafil.gob.pe/', 1, 0, 0);

-- ── estadistica-ti ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('estadistica-ti', 'Unidad de Estadística, Tecnología Informática y Telecomunicaciones', 'Órgano de Apoyo', 'Gestión de información estadística, sistemas informáticos, telecomunicaciones y seguridad digital.', 'La **Unidad de Estadística, Tecnología Informática y Telecomunicaciones** es responsable de la gestión de información estadística, administración de sistemas informáticos y telecomunicaciones del hospital.

## Funciones principales

- Administrar sistemas de información del hospital.
- Gestionar bases de datos clínicas y administrativas.
- Elaborar informes y análisis estadísticos.
- Mantener infraestructura de telecomunicaciones.
- Garantizar seguridad y protección de datos.

## Servicios TI

- Soporte técnico a equipos y software
- Administración de sistemas y redes
- Gestión de telecomunicaciones
- Seguridad informática y protección de datos

## Estadística

- Análisis de datos clínicos y administrativos
- Reportes para gestión directiva
- Indicadores de desempeño
- Vigilancia epidemiológica por estadística

## Soporte

Disponemos de soporte técnico durante el horario laboral y emergencias de infraestructura informática 24/7.', '084 224841', 'estadistica@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m. (24/7 soporte técnico)', 'Pabellón Administrativo', '3.er piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 15, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'estadistica-ti'), 'Jefe(a) de la Unidad de Estadística y TI', 'Nombre por asignar', 0);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'estadistica-ti'), 'Responsable de Tecnología Informática', 'Nombre por asignar', 1);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'estadistica-ti'), NULL, 'Portal de servicios', 'enlace', 'https://hospitalantoniolorena.gob.pe', 1, 0, 0);

-- ── gestion-calidad ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('gestion-calidad', 'Oficina de Gestión de la Calidad', 'Órgano de Asesoramiento', 'Gestión de calidad, acreditación, auditorías internas y mejora continua de procesos.', 'La **Oficina de Gestión de la Calidad** es responsable de implementar sistemas de gestión de calidad, acreditación y mejora continua de procesos en el hospital.

## Funciones principales

- Implementar y supervisar sistemas de gestión de calidad.
- Coordinar procesos de acreditación y certificación.
- Realizar auditorías internas de calidad.
- Identificar oportunidades de mejora continua.
- Capacitar al personal en estándares de calidad.

## Estándares

Nos comprometemos con los estándares internacionales de calidad en atención de salud.

## Mejora continua

La oficina trabaja constantemente en identificar y solucionar desviaciones de calidad, asegurando la satisfacción del usuario.

## Sugerencias

Valoramos sugerencias del personal y usuarios para mejorar continuamente nuestros procesos.', '084 224841', 'calidad@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón Administrativo', '2.° piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 11, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), 'Jefe(a) de la Oficina de Gestión de la Calidad', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), NULL, 'Estándares de acreditación', 'enlace', 'https://www.mimp.gob.pe/', 1, 0, 0);

-- ── inteligencia-sanitaria ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('inteligencia-sanitaria', 'Oficina de Inteligencia Sanitaria', 'Órgano de Asesoramiento', 'Vigilancia epidemiológica, análisis de información sanitaria y gestión de datos de salud.', 'La **Oficina de Inteligencia Sanitaria** es responsable de la vigilancia epidemiológica, análisis de información sanitaria y gestión de datos de salud para la toma de decisiones.

## Funciones principales

- Ejercer vigilancia epidemiológica sobre enfermedades transmisibles.
- Analizar e interpretar información sanitaria del hospital.
- Reportar eventos de salud pública a autoridades sanitarias.
- Elaborar informes y estadísticas de salud.
- Coordinar acciones de prevención y control de epidemias.

## Áreas de vigilancia

- Enfermedades transmisibles
- Eventos de salud pública
- Brotes y epidemias
- Análisis de tendencias de morbilidad

## Información y reportes

La oficina proporciona información sanitaria para el análisis y gestión del hospital, en cumplimiento de normas de privacidad.', '084 224841', 'inteligencia.sanitaria@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón Administrativo', '2.° piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 12, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'inteligencia-sanitaria'), 'Jefe(a) de la Oficina de Inteligencia Sanitaria', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'inteligencia-sanitaria'), NULL, 'Vigilancia epidemiológica', 'enlace', 'https://www.dge.gob.pe/', 1, 0, 0);

-- ── investigacion-docencia ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('investigacion-docencia', 'Oficina de Investigación, Docencia y Capacitación', 'Órgano de Asesoramiento', 'Promoción de investigación, docencia, capacitación y educación continuada del personal.', 'La **Oficina de Investigación, Docencia y Capacitación** es responsable de promover la investigación científica, docencia de pre y postgrado, y capacitación continua del personal.

## Funciones principales

- Impulsar proyectos de investigación e innovación en salud.
- Facilitar convenios con universidades e institutos de educación.
- Organizar programas de capacitación y educación continuada.
- Supervisar residencias y prácticas estudiantiles.
- Difundir conocimiento científico generado en el hospital.

## Programas disponibles

- Residencias médicas en diversas especialidades
- Programas de educación continuada
- Cursos de actualización profesional
- Seminarios y talleres

## Investigación

Promovemos la investigación con enfoque en soluciones de problemas de salud locales y regionales.', '084 224841', 'investigacion@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón de Docencia', '2.° piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 8, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'investigacion-docencia'), 'Jefe(a) de la Oficina de Investigación y Docencia', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'investigacion-docencia'), NULL, 'Publicaciones científicas', 'enlace', 'https://hospitalantoniolorena.gob.pe', 1, 0, 0);

-- ── planeamiento-presupuesto ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('planeamiento-presupuesto', 'Oficina de Planeamiento y Presupuesto', 'Órgano de Asesoramiento', 'Planificación estratégica, formulación de presupuestos y seguimiento de planes institucionales.', 'La **Oficina de Planeamiento y Presupuesto** es responsable de la planificación estratégica, formulación de presupuestos y seguimiento del desempeño institucional.

## Funciones principales

- Formular el Plan Estratégico y Operativo del hospital.
- Elaborar presupuestos anuales y presupuestos modificados.
- Supervisar la ejecución presupuestal.
- Analizar indicadores de desempeño.
- Coordinar con dependencias para lograr objetivos.

## Planes institucionales

- Plan Estratégico Institucional (PEI)
- Plan Operativo Anual (POA)
- Presupuesto Anual
- Indicadores de Desempeño (CEPLAN)

## Seguimiento

La oficina realiza seguimiento permanente del cumplimiento de planes y objetivos institucionales.

## Acceso a información

Para información sobre planes y presupuestos, contacta a la oficina.', '084 224841', 'planeamiento@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón Administrativo', '3.er piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 13, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'planeamiento-presupuesto'), 'Jefe(a) de la Oficina de Planeamiento y Presupuesto', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'planeamiento-presupuesto'), NULL, 'Plan Estratégico', 'enlace', 'https://www.transparencia.gob.pe/', 1, 0, 0);

-- ── recursos-humanos ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('recursos-humanos', 'Oficina de Recursos Humanos', 'Órgano de Apoyo', 'Gestión del personal, procesos de convocatoria, legajos y bienestar del trabajador.', 'La **Oficina de Recursos Humanos** es responsable de administrar el potencial humano del Hospital Antonio Lorena, asegurando procesos transparentes de selección, contratación, control de asistencia y desarrollo del personal.

## Funciones principales

- Conducir los procesos de convocatoria y selección de personal (CAS, terceros y nombramiento).
- Administrar los legajos y la planilla del personal.
- Gestionar licencias, permisos y control de asistencia.
- Promover el bienestar y la capacitación del trabajador.

## Atención al personal

El personal puede acercarse a la oficina en el horario de atención o escribir al correo institucional para realizar sus consultas y trámites.', '084 224841', 'rrhh@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón administrativo', '2.° piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 1, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'recursos-humanos'), 'Jefe(a) de la Oficina de Recursos Humanos', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'recursos-humanos'), NULL, 'Directiva interna de personal 2026', 'archivo', '/oficinas/recursos-humanos/directiva-2026.pdf', 'pdf', 1, 0, 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'recursos-humanos'), NULL, 'Convocatorias vigentes', 'enlace', '/convocatorias', 1, 0, 1);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'recursos-humanos'), NULL, 'Portal de Transparencia del Estado', 'enlace', 'https://www.transparencia.gob.pe/', 1, 0, 2);

-- ── seguros-convenios ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('seguros-convenios', 'Oficina de Seguros Convenios, Referencias y Contraferencias', 'Órgano de Asesoramiento', 'Gestión de convenios de seguros, referencias clínicas y contraferencias entre instituciones.', 'La **Oficina de Seguros Convenios, Referencias y Contraferencias** es responsable de gestionar convenios con empresas de seguros, referencias de pacientes y coordinación entre instituciones de salud.

## Funciones principales

- Negociar y supervisar convenios con seguros de salud y empresas.
- Coordinar referencias de pacientes a instituciones especializadas.
- Gestionar contraferencias de pacientes derivados.
- Mantener registros de convenios vigentes.
- Facilitar coordinación interinstitucional en salud.

## Servicios disponibles

- Atención a pacientes con seguros privados
- Coordinación de referencias médicas
- Información sobre convenios
- Seguimiento de casos derivados

## Convenios

Contamos con convenios con las principales instituciones de salud de la región para garantizar atención integral.', '084 224841', 'convenios@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón Administrativo', '2.° piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 9, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'seguros-convenios'), 'Jefe(a) de la Oficina de Seguros y Convenios', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'seguros-convenios'), NULL, 'Convenios vigentes', 'enlace', 'https://www.transparencia.gob.pe/', 1, 0, 0);

-- ── unidad-control-patrimonial ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('unidad-control-patrimonial', 'Unidad de Control Patrimonial', 'Órgano de Apoyo', 'Registro, custodia, control y evaluación del patrimonio e infraestructura del hospital.', 'La **Unidad de Control Patrimonial** es responsable del registro, custodia, mantenimiento y control del patrimonio e infraestructura del hospital.

## Funciones principales

- Registrar y catalogar bienes patrimoniales del hospital.
- Supervisar la custodia y preservación del patrimonio.
- Efectuar inventarios periódicos de activos.
- Registrar donaciones, adquisiciones y bajas.
- Coordinar el mantenimiento preventivo de infraestructura.

## Responsabilidades

- Garantizar la disponibilidad de equipos e infraestructura.
- Prevenir pérdidas y deterioro de bienes.
- Mantener registros actualizados de activos.
- Informar sobre condiciones de infraestructura.

## Contacto

Para reportes sobre daños en infraestructura o solicitudes de mantenimiento, contacta a nuestra unidad.', '084 224841', 'patrimonio@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón Administrativo', '2.° piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 6, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-control-patrimonial'), 'Jefe(a) de la Unidad de Control Patrimonial', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-control-patrimonial'), NULL, 'Portal de Transparencia', 'enlace', 'https://www.transparencia.gob.pe/', 1, 0, 0);

-- ── unidad-economia ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('unidad-economia', 'Unidad de Economía', 'Órgano de Apoyo', 'Gestión de recursos financieros, presupuesto, contabilidad y control económico del hospital.', 'La **Unidad de Economía** es responsable de la gestión integral de los recursos financieros y el control económico del hospital.

## Funciones principales

- Formular y ejecutar presupuestos anuales.
- Registrar y controlar ingresos y egresos.
- Administrar fondos y recursos financieros.
- Elaborar reportes financieros y estados de cuenta.
- Coordinar con entidades de control financiero.

## Servicios disponibles

- Información sobre estados financieros
- Consultas sobre presupuestos
- Asesoramiento en asuntos económicos

## Principios

La unidad actúa bajo principios de transparencia, eficiencia y responsabilidad en la gestión de recursos públicos.', '084 224841', 'economia@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón Administrativo', '2.° piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 4, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-economia'), 'Jefe(a) de la Unidad de Economía', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-economia'), NULL, 'Portal de Transparencia', 'enlace', 'https://www.transparencia.gob.pe/', 1, 0, 0);

-- ── unidad-logistica ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('unidad-logistica', 'Unidad de Logística', 'Órgano de Apoyo', 'Adquisiciones, suministros, almacén y gestión de inventario del hospital.', 'La **Unidad de Logística** es responsable de la adquisición, almacenamiento, distribución y control de suministros e insumos del hospital.

## Funciones principales

- Planificar y ejecutar adquisiciones de bienes y servicios.
- Administrar almacenes y controlar inventarios.
- Distribuir suministros a áreas del hospital.
- Cumplir normativas de compras públicas.
- Optimizar costos y garantizar disponibilidad de insumos.

## Tipos de suministros gestionados

- Medicamentos e insumos médicos
- Materiales de oficina
- Repuestos y equipos
- Alimentos y servicios generales

## Procesos

Todos nuestros procesos de adquisición se realizan conforme a normativas de compras públicas y transparencia.', '084 224841', 'logistica@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Zona de Almacén', '1.er piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 5, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-logistica'), 'Jefe(a) de la Unidad de Logística', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-logistica'), NULL, 'Procesos de compra pública', 'enlace', 'https://www.perucompras.gob.pe/', 1, 0, 0);

-- ── unidad-mantenimiento ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('unidad-mantenimiento', 'Unidad de Mantenimiento y Servicios Generales', 'Órgano de Apoyo', 'Mantenimiento de infraestructura, limpieza, servicios de seguridad y vigilancia del hospital.', 'La **Unidad de Mantenimiento y Servicios Generales** es responsable del mantenimiento preventivo y correctivo de la infraestructura, servicios de limpieza, seguridad y vigilancia del hospital.

## Funciones principales

- Realizar mantenimiento preventivo y correctivo de infraestructura.
- Gestionar servicios de limpieza y desinfección.
- Coordinar servicios de seguridad y vigilancia.
- Mantener áreas comunes en óptimas condiciones.
- Atender emergencias de infraestructura.

## Servicios disponibles

- Mantenimiento de instalaciones eléctricas y sanitarias
- Limpieza y desinfección de ambientes
- Reparación de equipos de infraestructura
- Vigilancia 24/7

## Emergencias

Disponemos de atención 24/7 para emergencias de infraestructura y servicios básicos.', '084 224841', 'mantenimiento@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m. (24/7 emergencias)', 'Área de Servicios Generales', 'Planta baja', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 7, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-mantenimiento'), 'Jefe(a) de la Unidad de Mantenimiento', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-mantenimiento'), NULL, 'Reportar fallas', 'enlace', 'https://hospitalantoniolorena.gob.pe', 1, 0, 0);

-- ── unidad-recursos-humanos ──
INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, contacto_telefono, contacto_email, contacto_horario, ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES ('unidad-recursos-humanos', 'Unidad de Recursos Humanos', 'Órgano de Apoyo', 'Gestión del potencial humano, procesos de convocatoria, legajos y desarrollo del personal.', 'La **Unidad de Recursos Humanos** es responsable de administrar el potencial humano del Hospital Antonio Lorena, asegurando procesos transparentes de selección, contratación, desarrollo y bienestar del personal.

## Funciones principales

- Conducir procesos de convocatoria y selección de personal (CAS, terceros, nombramiento).
- Administrar legajos y planilla del personal.
- Gestionar licencias, permisos y control de asistencia.
- Promover el bienestar, capacitación y desarrollo del trabajador.
- Aplicar normas de protección social.

## Atención al personal

El personal puede acercarse durante el horario de atención o escribir al correo institucional para consultas y trámites.

## Procesos principales

- Procesos selectivos y de ingreso
- Gestión de expedientes personales
- Licencias, permisos y beneficios
- Capacitación y desarrollo profesional', '084 224841', 'rrhh@hospitalantoniolorena.gob.pe', 'Lunes a viernes, 8:00 a.m. – 4:00 p.m.', 'Pabellón Administrativo', '2.° piso', 'Urb. Primavera S/N - Santiago, Cusco', 'https://www.google.com/maps?q=Hospital+Antonio+Lorena+del+Cusco', 3, 1);
INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-recursos-humanos'), 'Jefe(a) de la Unidad de Recursos Humanos', 'Nombre por asignar', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-recursos-humanos'), NULL, 'Convocatorias vigentes', 'enlace', '/convocatorias', 1, 0, 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'unidad-recursos-humanos'), NULL, 'Portal de Transparencia del Estado', 'enlace', 'https://www.transparencia.gob.pe/', 1, 0, 1);

-- ── Secciones de gestión de calidad (botones de acción) ──
INSERT INTO oficina_secciones (oficina_id, slug, titulo, descripcion, icono, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), 'seguridad-paciente', 'Seguridad del Paciente', 'Políticas y protocolos para garantizar la seguridad del paciente', 'documents', 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'seguridad-paciente' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'RD-No-003-Cronograma-Rondas-de-Seguridad-del-Paciente-HAL-2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/SEGUIDAD%20DEL%20PACIENTE/RD-No-003-Cronograma-Rondas-de-Seguridad-del-Paciente-HAL-2026.pdf', 'SEGURIDAD DEL PACIENTE', 'RD-No-003-Cronograma-Rondas-de-Seguridad-del-Paciente-HAL-2026.pdf', 'pdf', 1, 1, 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'seguridad-paciente' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'RD-No-004-Equipo-de-Rondas-de-Seguridad-del-Paciente-HAL-2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/SEGUIDAD%20DEL%20PACIENTE/RD-No-004-Equipo-de-Rondas-de-Seguridad-del-Paciente-HAL-2026.pdf', 'SEGURIDAD DEL PACIENTE', 'RD-No-004-Equipo-de-Rondas-de-Seguridad-del-Paciente-HAL-2026.pdf', 'pdf', 1, 1, 1);
-- seguridad-paciente: 2 documento(s)
INSERT INTO oficina_secciones (oficina_id, slug, titulo, descripcion, icono, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), 'informacion-calidad', 'Información para la Calidad', 'Reportes e indicadores para la gestión de la calidad', 'documents', 1);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'informacion-calidad' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'REGLAMENTO-DEL-PROCESO-ELECTORAL-DEL-COMITE-DE-SEGURIDAD-Y-SALUD-EN-EL-TRABAJO-HAL-1.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/INFORMACION%20PARA%20LA%20CALIDAD/REGLAMENTO-DEL-PROCESO-ELECTORAL-DEL-COMITE-DE-SEGURIDAD-Y-SALUD-EN-EL-TRABAJO-HAL-1.pdf', 'INFORMACION PARA LA CALIDAD', 'REGLAMENTO-DEL-PROCESO-ELECTORAL-DEL-COMITE-DE-SEGURIDAD-Y-SALUD-EN-EL-TRABAJO-HAL-1.pdf', 'pdf', 1, 1, 0);
-- informacion-calidad: 1 documento(s)
INSERT INTO oficina_secciones (oficina_id, slug, titulo, descripcion, icono, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), 'autoevaluacion-acreditacion', 'Autoevaluación y Acreditación', 'Documentos de autoevaluación y procesos de acreditación', 'documents', 2);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'Cronograma-Autoevaluacion-HAL-2025.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/Cronograma-Autoevaluacion-HAL-2025.pdf', 'AUTOEVALUACION Y ACREDITACION', 'Cronograma-Autoevaluacion-HAL-2025.pdf', 'pdf', 1, 1, 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'Equipo-Acreditacion-y-Equipo-Evaluadores-Internos-Autoevaluacion-2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/Equipo-Acreditacion-y-Equipo-Evaluadores-Internos-Autoevaluacion-2026.pdf', 'AUTOEVALUACION Y ACREDITACION', 'Equipo-Acreditacion-y-Equipo-Evaluadores-Internos-Autoevaluacion-2026.pdf', 'pdf', 1, 1, 1);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'Equipo-Acreditacion-y-Equipo-Evaluadores-Internos.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/Equipo-Acreditacion-y-Equipo-Evaluadores-Internos.pdf', 'AUTOEVALUACION Y ACREDITACION', 'Equipo-Acreditacion-y-Equipo-Evaluadores-Internos.pdf', 'pdf', 1, 1, 2);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'INFORME-FINAL-AUTOEVALUACION-HAL-2025.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/INFORME-FINAL-AUTOEVALUACION-HAL-2025.pdf', 'AUTOEVALUACION Y ACREDITACION', 'INFORME-FINAL-AUTOEVALUACION-HAL-2025.pdf', 'pdf', 1, 1, 3);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'Informe-No-137-Inicio-Autoevaluacion-HAL-2025.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/Informe-No-137-Inicio-Autoevaluacion-HAL-2025.pdf', 'AUTOEVALUACION Y ACREDITACION', 'Informe-No-137-Inicio-Autoevaluacion-HAL-2025.pdf', 'pdf', 1, 1, 4);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'Inicio Autoevaluacion y cronograma HAL-GERESA 2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/Inicio%20Autoevaluacion%20y%20cronograma%20HAL-GERESA%202026.pdf', 'AUTOEVALUACION Y ACREDITACION', 'Inicio Autoevaluacion y cronograma HAL-GERESA 2026.pdf', 'pdf', 1, 1, 5);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'PLAN AUTOEVALUACION HAL 2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/PLAN%20AUTOEVALUACION%20HAL%202026.pdf', 'AUTOEVALUACION Y ACREDITACION', 'PLAN AUTOEVALUACION HAL 2026.pdf', 'pdf', 1, 1, 6);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'PLAN-DE-DESARROLLO-DE-INVESTIGACION-INSTITUCIONAL-HAL-2024.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/PLAN-DE-DESARROLLO-DE-INVESTIGACION-INSTITUCIONAL-HAL-2024.pdf', 'AUTOEVALUACION Y ACREDITACION', 'PLAN-DE-DESARROLLO-DE-INVESTIGACION-INSTITUCIONAL-HAL-2024.pdf', 'pdf', 1, 1, 7);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'Plan de Autoevaluacion HAL- GERESA 2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/Plan%20de%20Autoevaluacion%20HAL-%20GERESA%202026.pdf', 'AUTOEVALUACION Y ACREDITACION', 'Plan de Autoevaluacion HAL- GERESA 2026.pdf', 'pdf', 1, 1, 8);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'RD Plan Autoevaluacion 2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/RD%20Plan%20Autoevaluacion%202026.pdf', 'AUTOEVALUACION Y ACREDITACION', 'RD Plan Autoevaluacion 2026.pdf', 'pdf', 1, 1, 9);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'RD equipo acreditarores y evaluadores internos.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/RD%20equipo%20acreditarores%20y%20evaluadores%20internos.pdf', 'AUTOEVALUACION Y ACREDITACION', 'RD equipo acreditarores y evaluadores internos.pdf', 'pdf', 1, 1, 10);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'RD-093-PLAN-DE-TRABAJO-AUTOEVALUACION-HAL-2025.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/RD-093-PLAN-DE-TRABAJO-AUTOEVALUACION-HAL-2025.pdf', 'AUTOEVALUACION Y ACREDITACION', 'RD-093-PLAN-DE-TRABAJO-AUTOEVALUACION-HAL-2025.pdf', 'pdf', 1, 1, 11);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'RD-155-Equipo-Acreditacion-evaluadores-HAL.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/RD-155-Equipo-Acreditacion-evaluadores-HAL.pdf', 'AUTOEVALUACION Y ACREDITACION', 'RD-155-Equipo-Acreditacion-evaluadores-HAL.pdf', 'pdf', 1, 1, 12);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'RD-N°037-2024-HAL-UGRH.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/RD-N%C2%B0037-2024-HAL-UGRH.pdf', 'AUTOEVALUACION Y ACREDITACION', 'RD-N°037-2024-HAL-UGRH.pdf', 'pdf', 1, 1, 13);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'autoevaluacion-acreditacion' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), 'RD-PLAN-AUTOEVALUACION-HAL-2024.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/AUTOEVALUACION%20Y%20ACREDITACION/RD-PLAN-AUTOEVALUACION-HAL-2024.pdf', 'AUTOEVALUACION Y ACREDITACION', 'RD-PLAN-AUTOEVALUACION-HAL-2024.pdf', 'pdf', 1, 1, 14);
-- autoevaluacion-acreditacion: 15 documento(s)
INSERT INTO oficina_secciones (oficina_id, slug, titulo, descripcion, icono, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), 'auditoria-guias-practica-clinica', 'Auditoría y Guías de Práctica Clínica', 'Auditorías y guías de práctica clínica del hospital', 'documents', 3);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'auditoria-guias-practica-clinica' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), '01 RD 2026-114 Comite y Equipo Auditoría HAL 2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/GUIAS-PRACTICA-CLINICA/01%20RD%202026-114%20Comite%20y%20Equipo%20Auditori%CC%81a%20HAL%202026.pdf', 'GUIAS-PRACTICA-CLINICA', '01 RD 2026-114 Comite y Equipo Auditoría HAL 2026.pdf', 'pdf', 1, 1, 0);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'auditoria-guias-practica-clinica' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), '02 RD 2026-113 Plan Anual de Auditoría HAL 2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/GUIAS-PRACTICA-CLINICA/02%20RD%202026-113%20Plan%20Anual%20de%20Auditori%CC%81a%20HAL%202026.pdf', 'GUIAS-PRACTICA-CLINICA', '02 RD 2026-113 Plan Anual de Auditoría HAL 2026.pdf', 'pdf', 1, 1, 1);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'auditoria-guias-practica-clinica' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), '03 RD 2026-61 Comite Gestión Adherencia Guia Práctica Clínica HAL 2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/GUIAS-PRACTICA-CLINICA/03%20RD%202026-61%20Comite%20Gestio%CC%81n%20Adherencia%20Guia%20Pra%CC%81ctica%20Cli%CC%81nica%20HAL%202026.pdf', 'GUIAS-PRACTICA-CLINICA', '03 RD 2026-61 Comite Gestión Adherencia Guia Práctica Clínica HAL 2026.pdf', 'pdf', 1, 1, 2);
INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), (SELECT id FROM oficina_secciones WHERE slug = 'auditoria-guias-practica-clinica' AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), '04 RD 2026-120 Grupo Elaboradores Guia Práctica Clínica HAL 2026.pdf', 'archivo', 'https://archivos.hospitalantoniolorena.gob.pe/oficinas/GUIAS-PRACTICA-CLINICA/04%20RD%202026-120%20Grupo%20Elaboradores%20Guia%20Pra%CC%81ctica%20Cli%CC%81nica%20HAL%202026.pdf', 'GUIAS-PRACTICA-CLINICA', '04 RD 2026-120 Grupo Elaboradores Guia Práctica Clínica HAL 2026.pdf', 'pdf', 1, 1, 3);
-- auditoria-guias-practica-clinica: 4 documento(s)
INSERT INTO oficina_secciones (oficina_id, slug, titulo, descripcion, icono, orden) VALUES ((SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), 'mejora-continua-calidad', 'Mejora Continua de la Calidad', 'Procesos y planes de mejora continua de la calidad', 'documents', 4);
-- mejora-continua-calidad: 0 documento(s)
