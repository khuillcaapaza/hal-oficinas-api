// Genera sql/seed-oficinas.sql a partir del contenido Markdown de hal-site
// (content/oficinas/*.md) y de las subsecciones de gestión de calidad que hoy
// viven en hal-archivos-api. Uso: node scripts/generar-seed.mjs
import fs from "node:fs";
import path from "node:path";
import { createRequire } from "node:module";

const require = createRequire(import.meta.url);
const HAL_SITE = "/Users/rubenpaz/personal/hal/hal-site";
const matter = require(path.join(HAL_SITE, "node_modules", "gray-matter"));

const OFICINAS_DIR = path.join(HAL_SITE, "content", "oficinas");
const OUT = path.join(process.cwd(), "sql", "seed-oficinas.sql");
const FILES_API = "https://archivos.hospitalantoniolorena.gob.pe/files-api";

// Secciones (botones de acción) de la Oficina de Gestión de la Calidad,
// migradas desde CalidadOptions.tsx + PDFList (carpetas en la colección oficinas).
const GESTION_CALIDAD_SECCIONES = [
  { slug: "seguridad-paciente", titulo: "Seguridad del Paciente", descripcion: "Políticas y protocolos para garantizar la seguridad del paciente", folder: "SEGURIDAD DEL PACIENTE" },
  { slug: "informacion-calidad", titulo: "Información para la Calidad", descripcion: "Reportes e indicadores para la gestión de la calidad", folder: "INFORMACION PARA LA CALIDAD" },
  { slug: "autoevaluacion-acreditacion", titulo: "Autoevaluación y Acreditación", descripcion: "Documentos de autoevaluación y procesos de acreditación", folder: "AUTOEVALUACION Y ACREDITACION" },
  { slug: "auditoria-guias-practica-clinica", titulo: "Auditoría y Guías de Práctica Clínica", descripcion: "Auditorías y guías de práctica clínica del hospital", folder: "GUIAS-PRACTICA-CLINICA" },
  { slug: "mejora-continua-calidad", titulo: "Mejora Continua de la Calidad", descripcion: "Procesos y planes de mejora continua de la calidad", folder: "MEJORA CONTINUA DE LA CALIDAD" },
];

const q = (v) => "'" + String(v ?? "").replace(/\\/g, "\\\\").replace(/'/g, "\\'") + "'";
const s = (v) => q(v);

async function listarPdfs(folder) {
  try {
    const url = `${FILES_API}/oficinas/list?folder=${encodeURIComponent(folder)}`;
    const res = await fetch(url);
    if (!res.ok) return [];
    const data = await res.json();
    return Array.isArray(data.files) ? data.files : [];
  } catch {
    return [];
  }
}

async function main() {
  const files = fs.readdirSync(OFICINAS_DIR).filter((f) => f.endsWith(".md")).sort();
  const out = [];
  out.push("-- Seed de oficinas generado desde hal-site/content/oficinas/*.md");
  out.push("-- y las subsecciones de gestión de calidad (hal-archivos-api).");
  out.push("-- Idempotente: limpia las tablas antes de insertar.");
  out.push("SET FOREIGN_KEY_CHECKS = 0;");
  out.push("TRUNCATE TABLE oficina_enlaces;");
  out.push("TRUNCATE TABLE oficina_secciones;");
  out.push("TRUNCATE TABLE oficina_autoridades;");
  out.push("TRUNCATE TABLE oficinas;");
  out.push("SET FOREIGN_KEY_CHECKS = 1;");
  out.push("");

  for (const file of files) {
    const slug = file.replace(/\.md$/, "");
    const raw = fs.readFileSync(path.join(OFICINAS_DIR, file), "utf8");
    const { data, content } = matter(raw);
    const contact = data.contact ?? {};
    const location = data.location ?? {};

    out.push(`-- ── ${slug} ──`);
    out.push(
      "INSERT INTO oficinas (slug, titulo, categoria, excerpt, contenido, " +
        "contacto_telefono, contacto_email, contacto_horario, " +
        "ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url, orden, publicado) VALUES (" +
        [
          s(slug),
          s(data.title ?? slug),
          s(data.category ?? "Oficina"),
          s(data.excerpt ?? ""),
          s(content.trim()),
          s(contact.phone ?? ""),
          s(contact.email ?? ""),
          s(contact.schedule ?? ""),
          s(location.building ?? ""),
          s(location.floor ?? ""),
          s(location.reference ?? ""),
          s(location.mapUrl ?? ""),
          typeof data.order === "number" ? data.order : 999,
          1,
        ].join(", ") +
        ");"
    );

    const autoridades = Array.isArray(data.authorities) ? data.authorities : [];
    autoridades.forEach((a, i) => {
      out.push(
        "INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden) VALUES (" +
          `(SELECT id FROM oficinas WHERE slug = ${s(slug)}), ${s(a.role ?? "Cargo")}, ${s(a.name ?? "")}, ${i});`
      );
    });

    const resources = Array.isArray(data.resources) ? data.resources : [];
    let orden = 0;
    for (const r of resources) {
      if (!r.file) continue;
      out.push(
        "INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, ext, publicado, managed, orden) VALUES (" +
          `(SELECT id FROM oficinas WHERE slug = ${s(slug)}), NULL, ${s(r.title ?? "Documento")}, 'archivo', ${s(r.file)}, 'pdf', 1, 0, ${orden++});`
      );
    }

    const links = Array.isArray(data.links) ? data.links : [];
    for (const l of links) {
      if (!l.url) continue;
      out.push(
        "INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, publicado, managed, orden) VALUES (" +
          `(SELECT id FROM oficinas WHERE slug = ${s(slug)}), NULL, ${s(l.title ?? "Enlace")}, 'enlace', ${s(l.url)}, 1, 0, ${orden++});`
      );
    }
    out.push("");
  }

  // Secciones (botones de acción) de la Oficina de Gestión de la Calidad.
  out.push("-- ── Secciones de gestión de calidad (botones de acción) ──");
  let secOrden = 0;
  for (const sec of GESTION_CALIDAD_SECCIONES) {
    out.push(
      "INSERT INTO oficina_secciones (oficina_id, slug, titulo, descripcion, icono, orden) VALUES (" +
        `(SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), ${s(sec.slug)}, ${s(sec.titulo)}, ${s(sec.descripcion)}, 'documents', ${secOrden++});`
    );
    const pdfs = await listarPdfs(sec.folder);
    let eo = 0;
    for (const pdf of pdfs) {
      out.push(
        "INSERT INTO oficina_enlaces (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo, ext, publicado, managed, orden) VALUES (" +
          `(SELECT id FROM oficinas WHERE slug = 'gestion-calidad'), ` +
          `(SELECT id FROM oficina_secciones WHERE slug = ${s(sec.slug)} AND oficina_id = (SELECT id FROM oficinas WHERE slug = 'gestion-calidad')), ` +
          `${s(pdf.name)}, 'archivo', ${s(pdf.url)}, ${s(sec.folder)}, ${s(pdf.name)}, 'pdf', 1, 1, ${eo++});`
      );
    }
    out.push(`-- ${sec.slug}: ${pdfs.length} documento(s)`);
  }

  fs.writeFileSync(OUT, out.join("\n") + "\n", "utf8");
  console.log(`Seed generado en ${OUT} (${files.length} oficinas)`);
}

main();
