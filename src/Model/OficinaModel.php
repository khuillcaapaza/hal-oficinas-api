<?php

declare(strict_types=1);

namespace App\Model;

use App\Support\Database;
use PDO;

/**
 * Acceso a datos de las oficinas (tabla oficinas): la ficha informativa de cada
 * oficina del hospital. Todas las consultas son preparadas.
 */
class OficinaModel
{
    private PDO $pdo;
    private ?bool $uuidDisponible = null;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    // ── Lectura ───────────────────────────────────────────────────────

    /** Oficinas publicadas, en orden de presentación (forma compacta). */
    public function publicados(): array
    {
        $rows = $this->pdo->query(
            'SELECT * FROM oficinas WHERE publicado = 1 ORDER BY orden ASC, id ASC'
        )->fetchAll();

        return array_map([$this, 'mapCard'], $rows);
    }

    /** Todas las oficinas (incluye no publicadas) para administración. */
    public function todos(): array
    {
        $rows = $this->pdo->query(
            'SELECT * FROM oficinas ORDER BY orden ASC, id ASC'
        )->fetchAll();

        return array_map([$this, 'map'], $rows);
    }

    /**
     * Oficinas paginadas para administración (incluye no publicadas).
     * Devuelve ['items', 'total', 'page', 'per_page', 'total_pages'].
     *
     * @return array{items:list<array<string,mixed>>,total:int,page:int,per_page:int,total_pages:int}
     */
    public function todosPaginado(int $page, int $perPage): array
    {
        $perPage = max(1, min(100, $perPage));
        $total   = (int) $this->pdo->query('SELECT COUNT(*) FROM oficinas')->fetchColumn();
        $totalPaginas = max(1, (int) ceil($total / $perPage));
        $page    = max(1, min($totalPaginas, $page));
        $offset  = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            'SELECT * FROM oficinas ORDER BY orden ASC, id ASC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return [
            'items'       => array_map([$this, 'map'], $rows),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPaginas,
        ];
    }

    /** Una oficina por slug (publicada o no), o null. */
    public function porSlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM oficinas WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row === false ? null : $this->map($row);
    }

    /** Una oficina por uuid (publicada o no), o null. */
    public function porUuid(string $uuid): ?array
    {
        if (!$this->usaUuid()) {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM oficinas WHERE uuid = ? LIMIT 1');
        $stmt->execute([$uuid]);
        $row = $stmt->fetch();

        return $row === false ? null : $this->map($row);
    }

    /** Una oficina por referencia (uuid o slug), o null. */
    public function porReferencia(string $ref): ?array
    {
        if ($this->esUuid($ref)) {
            $byUuid = $this->porUuid($ref);
            if ($byUuid !== null) {
                return $byUuid;
            }
        }

        return $this->porSlug($ref);
    }

    /** Una oficina publicada por slug, o null. */
    public function publicadoPorSlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM oficinas WHERE slug = ? AND publicado = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row === false ? null : $this->map($row);
    }

    public function existeSlug(string $slug): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM oficinas WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);

        return $stmt->fetchColumn() !== false;
    }

    public function existeUuid(string $uuid): bool
    {
        if (!$this->usaUuid()) {
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT 1 FROM oficinas WHERE uuid = ? LIMIT 1');
        $stmt->execute([$uuid]);

        return $stmt->fetchColumn() !== false;
    }

    public function idPorSlug(string $slug): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM oficinas WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    public function idPorUuid(string $uuid): ?int
    {
        if (!$this->usaUuid()) {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT id FROM oficinas WHERE uuid = ? LIMIT 1');
        $stmt->execute([$uuid]);
        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    /** Obtiene id por referencia (uuid o slug). */
    public function idPorReferencia(string $ref): ?int
    {
        if ($this->esUuid($ref)) {
            $id = $this->idPorUuid($ref);
            if ($id !== null) {
                return $id;
            }
        }

        return $this->idPorSlug($ref);
    }

    /** Siguiente número de orden disponible entre las oficinas. */
    public function siguienteOrden(): int
    {
        $val = $this->pdo->query('SELECT COALESCE(MAX(orden), -1) + 1 FROM oficinas')->fetchColumn();

        return (int) $val;
    }

    // ── Escritura ─────────────────────────────────────────────────────

    /** Inserta una oficina. Devuelve el id nuevo. */
    public function crear(array $o): int
    {
        if (array_key_exists('uuid', $o) && (string) $o['uuid'] !== '') {
            $stmt = $this->pdo->prepare(
                'INSERT INTO oficinas
                    (uuid, slug, titulo, categoria, excerpt, contenido,
                     contacto_telefono, contacto_email, contacto_horario,
                     ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url,
                     orden, publicado)
                 VALUES
                    (:uuid, :slug, :titulo, :categoria, :excerpt, :contenido,
                     :contacto_telefono, :contacto_email, :contacto_horario,
                     :ubic_edificio, :ubic_piso, :ubic_referencia, :ubic_map_url,
                     :orden, :publicado)'
            );
            $stmt->execute($o);
        } else {
            $stmt = $this->pdo->prepare(
                'INSERT INTO oficinas
                    (slug, titulo, categoria, excerpt, contenido,
                     contacto_telefono, contacto_email, contacto_horario,
                     ubic_edificio, ubic_piso, ubic_referencia, ubic_map_url,
                     orden, publicado)
                 VALUES
                    (:slug, :titulo, :categoria, :excerpt, :contenido,
                     :contacto_telefono, :contacto_email, :contacto_horario,
                     :ubic_edificio, :ubic_piso, :ubic_referencia, :ubic_map_url,
                     :orden, :publicado)'
            );
            $stmt->execute($o);
        }

        return (int) $this->pdo->lastInsertId();
    }

    /** Actualiza una oficina por slug. true si existe (haya o no cambios). */
    public function actualizar(string $slug, array $o): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE oficinas SET
                titulo = :titulo, categoria = :categoria, excerpt = :excerpt, contenido = :contenido,
                contacto_telefono = :contacto_telefono, contacto_email = :contacto_email,
                contacto_horario = :contacto_horario,
                ubic_edificio = :ubic_edificio, ubic_piso = :ubic_piso,
                ubic_referencia = :ubic_referencia, ubic_map_url = :ubic_map_url,
                orden = :orden, publicado = :publicado
              WHERE slug = :slug_actual'
        );
        $stmt->execute([
            'titulo'            => $o['titulo'],
            'categoria'         => $o['categoria'],
            'excerpt'           => $o['excerpt'],
            'contenido'         => $o['contenido'],
            'contacto_telefono' => $o['contacto_telefono'],
            'contacto_email'    => $o['contacto_email'],
            'contacto_horario'  => $o['contacto_horario'],
            'ubic_edificio'     => $o['ubic_edificio'],
            'ubic_piso'         => $o['ubic_piso'],
            'ubic_referencia'   => $o['ubic_referencia'],
            'ubic_map_url'      => $o['ubic_map_url'],
            'orden'             => $o['orden'],
            'publicado'         => $o['publicado'],
            'slug_actual'       => $slug,
        ]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        return $this->existeSlug($slug);
    }

    /** Actualiza una oficina por id. true si existe (haya o no cambios). */
    public function actualizarPorId(int $id, array $o): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE oficinas SET
                slug = :slug,
                titulo = :titulo, categoria = :categoria, excerpt = :excerpt, contenido = :contenido,
                contacto_telefono = :contacto_telefono, contacto_email = :contacto_email,
                contacto_horario = :contacto_horario,
                ubic_edificio = :ubic_edificio, ubic_piso = :ubic_piso,
                ubic_referencia = :ubic_referencia, ubic_map_url = :ubic_map_url,
                orden = :orden, publicado = :publicado
              WHERE id = :id'
        );
        $stmt->execute([
            'slug'              => $o['slug'],
            'titulo'            => $o['titulo'],
            'categoria'         => $o['categoria'],
            'excerpt'           => $o['excerpt'],
            'contenido'         => $o['contenido'],
            'contacto_telefono' => $o['contacto_telefono'],
            'contacto_email'    => $o['contacto_email'],
            'contacto_horario'  => $o['contacto_horario'],
            'ubic_edificio'     => $o['ubic_edificio'],
            'ubic_piso'         => $o['ubic_piso'],
            'ubic_referencia'   => $o['ubic_referencia'],
            'ubic_map_url'      => $o['ubic_map_url'],
            'orden'             => $o['orden'],
            'publicado'         => $o['publicado'],
            'id'                => $id,
        ]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        return $this->porId($id) !== null;
    }

    /** Elimina una oficina (CASCADE borra autoridades, secciones y enlaces). true si borró. */
    public function eliminar(string $slug): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM oficinas WHERE slug = ?');
        $stmt->execute([$slug]);

        return $stmt->rowCount() > 0;
    }

    /** Elimina una oficina por id. */
    public function eliminarPorId(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM oficinas WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Reordena múltiples oficinas en una sola transacción.
     *
     * @param list<array{slug:string,orden:int}> $items
     * @return array{0:bool,1:list<string>} [ok, slugsNoEncontrados]
     */
    public function reordenar(array $items): array
    {
        if ($items === []) {
            return [true, []];
        }

        $stmt = $this->pdo->query('SELECT slug FROM oficinas');
        $existentes = array_flip(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []));

        $faltantes = [];
        foreach ($items as $it) {
            if (!isset($existentes[$it['slug']])) {
                $faltantes[] = $it['slug'];
            }
        }
        if ($faltantes !== []) {
            return [false, $faltantes];
        }

        $upd = $this->pdo->prepare('UPDATE oficinas SET orden = :orden WHERE slug = :slug');

        $this->pdo->beginTransaction();
        try {
            foreach ($items as $it) {
                $upd->execute([
                    'orden' => $it['orden'],
                    'slug'  => $it['slug'],
                ]);
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return [true, []];
    }

    /**
     * Reordena múltiples oficinas por referencia (uuid o slug) en transacción.
     *
     * @param list<array{ref:string,orden:int}> $items
     * @return array{0:bool,1:list<string>} [ok, refsNoEncontradas]
     */
    public function reordenarPorReferencia(array $items): array
    {
        if ($items === []) {
            return [true, []];
        }

        $faltantes = [];
        $normalizados = [];
        foreach ($items as $it) {
            $id = $this->idPorReferencia($it['ref']);
            if ($id === null) {
                $faltantes[] = $it['ref'];
                continue;
            }
            $normalizados[] = ['id' => $id, 'orden' => $it['orden']];
        }

        if ($faltantes !== []) {
            return [false, $faltantes];
        }

        $upd = $this->pdo->prepare('UPDATE oficinas SET orden = :orden WHERE id = :id');

        $this->pdo->beginTransaction();
        try {
            foreach ($normalizados as $it) {
                $upd->execute([
                    'orden' => $it['orden'],
                    'id'    => $it['id'],
                ]);
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return [true, []];
    }

    // ── Mapeo ─────────────────────────────────────────────────────────

    /** Forma compacta para el listado de oficinas. */
    public function mapCard(array $row): array
    {
        $uuid = $this->uuidParaFila($row);

        return [
            'uuid'      => $uuid,
            'slug'      => $row['slug'],
            'titulo'    => $row['titulo'],
            'categoria' => $row['categoria'],
            'excerpt'   => $row['excerpt'],
            'orden'     => (int) $row['orden'],
        ];
    }

    /** Forma completa (admin / detalle). */
    public function map(array $row): array
    {
        $uuid = $this->uuidParaFila($row);

        return [
            'id'                => (int) $row['id'],
            'uuid'              => $uuid,
            'slug'              => $row['slug'],
            'titulo'            => $row['titulo'],
            'categoria'         => $row['categoria'],
            'excerpt'           => $row['excerpt'],
            'contenido'         => $row['contenido'],
            'contacto_telefono' => $row['contacto_telefono'],
            'contacto_email'    => $row['contacto_email'],
            'contacto_horario'  => $row['contacto_horario'],
            'ubic_edificio'     => $row['ubic_edificio'],
            'ubic_piso'         => $row['ubic_piso'],
            'ubic_referencia'   => $row['ubic_referencia'],
            'ubic_map_url'      => $row['ubic_map_url'],
            'orden'             => (int) $row['orden'],
            'publicado'         => (int) $row['publicado'] === 1,
        ];
    }

    /** Oficina por id, o null. */
    private function porId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM oficinas WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $this->map($row);
    }

    private function esUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }

    private function usaUuid(): bool
    {
        if ($this->uuidDisponible !== null) {
            return $this->uuidDisponible;
        }

        $stmt = $this->pdo->query("SHOW COLUMNS FROM oficinas LIKE 'uuid'");
        $this->uuidDisponible = $stmt !== false && $stmt->fetch() !== false;

        return $this->uuidDisponible;
    }

    /** Genera y persiste un UUID para registros creados antes de la migración. */
    private function uuidParaFila(array $row): string
    {
        $uuid = trim((string) ($row['uuid'] ?? ''));
        if ($uuid !== '' || !array_key_exists('uuid', $row) || !isset($row['id'])) {
            return $uuid;
        }

        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));

        $stmt = $this->pdo->prepare("UPDATE oficinas SET uuid = :uuid WHERE id = :id AND (uuid IS NULL OR uuid = '')");
        $stmt->execute(['uuid' => $uuid, 'id' => (int) $row['id']]);

        return $uuid;
    }
}
