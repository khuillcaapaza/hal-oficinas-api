<?php

declare(strict_types=1);

namespace App\Model;

use App\Support\Database;
use PDO;

/**
 * Acceso a datos de los enlaces de una oficina (tabla oficina_enlaces).
 *
 * Un enlace es un documento (tipo=archivo, subido a hal-archivos-api) o una URL
 * externa (tipo=enlace). Cuelga directamente de la oficina (seccion_id NULL) o
 * de una sección (botón de acción). Solo metadatos: el binario físico vive en
 * hal-archivos-api.
 */
class EnlaceModel
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    // ── Lectura pública ───────────────────────────────────────────────

    /** Enlaces publicados que cuelgan directamente de la oficina. */
    public function publicadosSueltos(int $oficinaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM oficina_enlaces
              WHERE oficina_id = ? AND seccion_id IS NULL AND publicado = 1
              ORDER BY orden ASC, id ASC'
        );
        $stmt->execute([$oficinaId]);

        return array_map([$this, 'mapPublico'], $stmt->fetchAll());
    }

    /** Enlaces publicados de una sección. */
    public function publicadosDeSeccion(int $seccionId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM oficina_enlaces
              WHERE seccion_id = ? AND publicado = 1
              ORDER BY orden ASC, id ASC'
        );
        $stmt->execute([$seccionId]);

        return array_map([$this, 'mapPublico'], $stmt->fetchAll());
    }

    // ── Lectura admin ─────────────────────────────────────────────────

    /** Todos los enlaces de una oficina (incluye no publicados). */
    public function deOficina(int $oficinaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM oficina_enlaces WHERE oficina_id = ? ORDER BY orden ASC, id ASC'
        );
        $stmt->execute([$oficinaId]);

        return array_map([$this, 'map'], $stmt->fetchAll());
    }

    /** Un enlace por id dentro de una oficina, o null. */
    public function porId(int $id, int $oficinaId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM oficina_enlaces WHERE id = ? AND oficina_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $oficinaId]);
        $row = $stmt->fetch();

        return $row === false ? null : $this->map($row);
    }

    /**
     * Archivos "managed" (subidos vía archivos-api) de una oficina, con la info
     * mínima para reenviar su borrado físico: [subcarpeta, nombre_archivo].
     */
    public function archivosManagedDeOficina(int $oficinaId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT subcarpeta, nombre_archivo FROM oficina_enlaces
              WHERE oficina_id = ? AND tipo = 'archivo' AND managed = 1
                AND subcarpeta IS NOT NULL AND nombre_archivo IS NOT NULL"
        );
        $stmt->execute([$oficinaId]);

        return $stmt->fetchAll();
    }

    /** Ídem para una sección. */
    public function archivosManagedDeSeccion(int $seccionId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT subcarpeta, nombre_archivo FROM oficina_enlaces
              WHERE seccion_id = ? AND tipo = 'archivo' AND managed = 1
                AND subcarpeta IS NOT NULL AND nombre_archivo IS NOT NULL"
        );
        $stmt->execute([$seccionId]);

        return $stmt->fetchAll();
    }

    /** Siguiente número de orden disponible dentro de la oficina o la sección. */
    public function siguienteOrden(int $oficinaId, ?int $seccionId): int
    {
        if ($seccionId === null) {
            $stmt = $this->pdo->prepare(
                'SELECT COALESCE(MAX(orden), -1) + 1 FROM oficina_enlaces
                  WHERE oficina_id = ? AND seccion_id IS NULL'
            );
            $stmt->execute([$oficinaId]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT COALESCE(MAX(orden), -1) + 1 FROM oficina_enlaces WHERE seccion_id = ?'
            );
            $stmt->execute([$seccionId]);
        }

        return (int) $stmt->fetchColumn();
    }

    // ── Escritura ─────────────────────────────────────────────────────

    /** Inserta un enlace. Devuelve el id nuevo. */
    public function crear(int $oficinaId, ?int $seccionId, array $e): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO oficina_enlaces
                (oficina_id, seccion_id, titulo, tipo, url, subcarpeta, nombre_archivo,
                 ext, tamano, fecha, orden, publicado, managed)
             VALUES
                (:oid, :secid, :titulo, :tipo, :url, :subcarpeta, :nombre_archivo,
                 :ext, :tamano, :fecha, :orden, :publicado, :managed)'
        );
        $stmt->execute([
            'oid'            => $oficinaId,
            'secid'          => $seccionId,
            'titulo'         => $e['titulo'],
            'tipo'           => $e['tipo'],
            'url'            => $e['url'],
            'subcarpeta'     => $e['subcarpeta'],
            'nombre_archivo' => $e['nombre_archivo'],
            'ext'            => $e['ext'],
            'tamano'         => $e['tamano'],
            'fecha'          => $e['fecha'],
            'orden'          => $e['orden'],
            'publicado'      => $e['publicado'],
            'managed'        => $e['managed'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** Actualiza los campos editables de un enlace. true si la fila existe. */
    public function actualizar(int $id, int $oficinaId, array $e): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE oficina_enlaces SET
                titulo = :titulo, url = :url, fecha = :fecha, orden = :orden, publicado = :publicado
              WHERE id = :id AND oficina_id = :oid'
        );
        $stmt->execute([
            'titulo'    => $e['titulo'],
            'url'       => $e['url'],
            'fecha'     => $e['fecha'],
            'orden'     => $e['orden'],
            'publicado' => $e['publicado'],
            'id'        => $id,
            'oid'       => $oficinaId,
        ]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        return $this->porId($id, $oficinaId) !== null;
    }

    /** Elimina un enlace. true si borró. */
    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM oficina_enlaces WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    // ── Mapeo ─────────────────────────────────────────────────────────

    /** Forma compacta para la página pública de detalle. */
    public function mapPublico(array $row): array
    {
        return [
            'id'     => (int) $row['id'],
            'titulo' => $row['titulo'],
            'url'    => $row['url'],
            'tipo'   => $row['tipo'],
            'ext'    => $row['ext'],
            'fecha'  => $row['fecha'],
        ];
    }

    /** Forma completa para administración. */
    public function map(array $row): array
    {
        return [
            'id'             => (int) $row['id'],
            'seccion_id'     => $row['seccion_id'] === null ? null : (int) $row['seccion_id'],
            'titulo'         => $row['titulo'],
            'tipo'           => $row['tipo'],
            'url'            => $row['url'],
            'subcarpeta'     => $row['subcarpeta'],
            'nombre_archivo' => $row['nombre_archivo'],
            'ext'            => $row['ext'],
            'tamano'         => (int) $row['tamano'],
            'fecha'          => $row['fecha'],
            'orden'          => (int) $row['orden'],
            'publicado'      => (int) $row['publicado'] === 1,
            'managed'        => (int) $row['managed'] === 1,
        ];
    }
}
