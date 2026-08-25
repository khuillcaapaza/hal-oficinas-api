<?php

declare(strict_types=1);

namespace App\Model;

use App\Support\Database;
use PDO;

/**
 * Acceso a datos de las autoridades de una oficina (tabla oficina_autoridades).
 *
 * Las autoridades son datos informativos de la ficha, por lo que se gestionan
 * como una lista que se reemplaza por completo al guardar la oficina.
 */
class AutoridadModel
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    /** Autoridades de una oficina, en orden. */
    public function deOficina(int $oficinaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM oficina_autoridades WHERE oficina_id = ? ORDER BY orden ASC, id ASC'
        );
        $stmt->execute([$oficinaId]);

        return array_map([$this, 'map'], $stmt->fetchAll());
    }

    /**
     * Reemplaza por completo la lista de autoridades de una oficina.
     *
     * @param list<array{cargo:string,nombre:string}> $autoridades
     */
    public function reemplazar(int $oficinaId, array $autoridades): void
    {
        $this->pdo->beginTransaction();
        try {
            $del = $this->pdo->prepare('DELETE FROM oficina_autoridades WHERE oficina_id = ?');
            $del->execute([$oficinaId]);

            $ins = $this->pdo->prepare(
                'INSERT INTO oficina_autoridades (oficina_id, cargo, nombre, orden)
                 VALUES (:oid, :cargo, :nombre, :orden)'
            );
            $orden = 0;
            foreach ($autoridades as $a) {
                $ins->execute([
                    'oid'    => $oficinaId,
                    'cargo'  => $a['cargo'],
                    'nombre' => $a['nombre'],
                    'orden'  => $orden++,
                ]);
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function map(array $row): array
    {
        return [
            'id'     => (int) $row['id'],
            'cargo'  => $row['cargo'],
            'nombre' => $row['nombre'],
            'orden'  => (int) $row['orden'],
        ];
    }
}
