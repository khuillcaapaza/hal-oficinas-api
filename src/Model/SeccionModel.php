<?php

declare(strict_types=1);

namespace App\Model;

use App\Support\Database;
use PDO;

/**
 * Acceso a datos de las secciones de una oficina (tabla oficina_secciones):
 * los "botones de acción" al pie de la ficha. Todas las consultas son preparadas.
 */
class SeccionModel
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    /** Secciones de una oficina, en orden. */
    public function deOficina(int $oficinaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM oficina_secciones WHERE oficina_id = ? ORDER BY orden ASC, id ASC'
        );
        $stmt->execute([$oficinaId]);

        return array_map([$this, 'map'], $stmt->fetchAll());
    }

    /** Una sección por id dentro de una oficina, o null. */
    public function porId(int $id, int $oficinaId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM oficina_secciones WHERE id = ? AND oficina_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $oficinaId]);
        $row = $stmt->fetch();

        return $row === false ? null : $this->map($row);
    }

    /** Siguiente número de orden disponible para una oficina. */
    public function siguienteOrden(int $oficinaId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(MAX(orden), -1) + 1 FROM oficina_secciones WHERE oficina_id = ?'
        );
        $stmt->execute([$oficinaId]);

        return (int) $stmt->fetchColumn();
    }

    /** Inserta una sección. Devuelve el id nuevo. */
    public function crear(int $oficinaId, array $s): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO oficina_secciones
                (oficina_id, slug, titulo, descripcion, icono, url_externa, orden)
             VALUES (:oid, :slug, :titulo, :descripcion, :icono, :url_externa, :orden)'
        );
        $stmt->execute([
            'oid'         => $oficinaId,
            'slug'        => $s['slug'],
            'titulo'      => $s['titulo'],
            'descripcion' => $s['descripcion'],
            'icono'       => $s['icono'],
            'url_externa' => $s['url_externa'],
            'orden'       => $s['orden'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** Actualiza una sección por id/oficina. true si afectó/existe fila. */
    public function actualizar(int $id, int $oficinaId, array $s): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE oficina_secciones SET
                slug = :slug, titulo = :titulo, descripcion = :descripcion,
                icono = :icono, url_externa = :url_externa, orden = :orden
              WHERE id = :id AND oficina_id = :oid'
        );
        $stmt->execute([
            'slug'        => $s['slug'],
            'titulo'      => $s['titulo'],
            'descripcion' => $s['descripcion'],
            'icono'       => $s['icono'],
            'url_externa' => $s['url_externa'],
            'orden'       => $s['orden'],
            'id'          => $id,
            'oid'         => $oficinaId,
        ]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        return $this->porId($id, $oficinaId) !== null;
    }

    /** Elimina una sección (CASCADE borra sus enlaces). true si borró. */
    public function eliminar(int $id, int $oficinaId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM oficina_secciones WHERE id = ? AND oficina_id = ?'
        );
        $stmt->execute([$id, $oficinaId]);

        return $stmt->rowCount() > 0;
    }

    private function map(array $row): array
    {
        return [
            'id'          => (int) $row['id'],
            'slug'        => $row['slug'],
            'titulo'      => $row['titulo'],
            'descripcion' => $row['descripcion'],
            'icono'       => $row['icono'],
            'url_externa' => $row['url_externa'],
            'orden'       => (int) $row['orden'],
        ];
    }
}
