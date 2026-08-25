<?php

declare(strict_types=1);

namespace Tests\Model;

use App\Model\SeccionModel;
use Tests\TestCase;

final class SeccionModelTest extends TestCase
{
    /** @return array<string,mixed> */
    private function fila(array $over = []): array
    {
        return array_merge([
            'id' => 5, 'slug' => 'planeamiento', 'titulo' => 'Planeamiento',
            'descripcion' => 'd', 'icono' => 'documents', 'url_externa' => null, 'orden' => 0,
        ], $over);
    }

    public function testDeOficinaMapea(): void
    {
        $pdo  = $this->pdo(prepare: [$this->stmt(['fetchAll' => [$this->fila()]])]);
        $rows = (new SeccionModel($pdo))->deOficina(2);

        $this->assertSame('planeamiento', $rows[0]['slug']);
        $this->assertNull($rows[0]['url_externa']);
    }

    public function testPorIdDevuelveSeccionONull(): void
    {
        $ok = new SeccionModel($this->pdo(prepare: [$this->stmt(['fetch' => $this->fila()])]));
        $this->assertSame(5, $ok->porId(5, 2)['id']);

        $nf = new SeccionModel($this->pdo(prepare: [$this->stmt(['fetch' => false])]));
        $this->assertNull($nf->porId(99, 2));
    }

    public function testSiguienteOrden(): void
    {
        $pdo = $this->pdo(prepare: [$this->stmt(['fetchColumn' => '4'])]);
        $this->assertSame(4, (new SeccionModel($pdo))->siguienteOrden(2));
    }

    public function testCrearDevuelveId(): void
    {
        $stmt = $this->stmt();
        $stmt->expects($this->once())->method('execute');
        $pdo  = $this->pdo(prepare: [$stmt], lastInsertId: '20');

        $id = (new SeccionModel($pdo))->crear(2, [
            'slug' => 's', 'titulo' => 't', 'descripcion' => '', 'icono' => 'documents',
            'url_externa' => null, 'orden' => 0,
        ]);

        $this->assertSame(20, $id);
    }

    public function testActualizarTrueCuandoRowCountPositivo(): void
    {
        $pdo = $this->pdo(prepare: [$this->stmt(['rowCount' => 1])]);
        $this->assertTrue((new SeccionModel($pdo))->actualizar(5, 2, $this->campos()));
    }

    public function testActualizarFallbackAPorId(): void
    {
        $pdo = $this->pdo(prepare: [
            $this->stmt(['rowCount' => 0]),
            $this->stmt(['fetch' => $this->fila()]),
        ]);
        $this->assertTrue((new SeccionModel($pdo))->actualizar(5, 2, $this->campos()));
    }

    public function testEliminar(): void
    {
        $pdo = $this->pdo(prepare: [$this->stmt(['rowCount' => 1])]);
        $this->assertTrue((new SeccionModel($pdo))->eliminar(5, 2));
    }

    /** @return array<string,mixed> */
    private function campos(): array
    {
        return [
            'slug' => 's', 'titulo' => 't', 'descripcion' => '', 'icono' => 'documents',
            'url_externa' => null, 'orden' => 0,
        ];
    }
}
