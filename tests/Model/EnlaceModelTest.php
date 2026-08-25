<?php

declare(strict_types=1);

namespace Tests\Model;

use App\Model\EnlaceModel;
use Tests\TestCase;

final class EnlaceModelTest extends TestCase
{
    /** @return array<string,mixed> */
    private function fila(array $over = []): array
    {
        return array_merge([
            'id' => 3, 'seccion_id' => 1, 'titulo' => 'Doc', 'tipo' => 'archivo',
            'url' => '/servicios/planeamiento/x.pdf', 'subcarpeta' => 'planeamiento',
            'nombre_archivo' => 'x.pdf', 'ext' => 'pdf', 'tamano' => 100, 'fecha' => null,
            'orden' => 0, 'publicado' => 1, 'managed' => 1,
        ], $over);
    }

    public function testPublicadosSueltosMapaPublico(): void
    {
        $pdo  = $this->pdo(prepare: [$this->stmt(['fetchAll' => [$this->fila(['seccion_id' => null])]])]);
        $rows = (new EnlaceModel($pdo))->publicadosSueltos(2);

        $this->assertSame('Doc', $rows[0]['titulo']);
        $this->assertArrayNotHasKey('managed', $rows[0]); // mapPublico es compacto
    }

    public function testPublicadosDeSeccion(): void
    {
        $pdo  = $this->pdo(prepare: [$this->stmt(['fetchAll' => [$this->fila()]])]);
        $rows = (new EnlaceModel($pdo))->publicadosDeSeccion(1);

        $this->assertSame('archivo', $rows[0]['tipo']);
    }

    public function testDeOficinaMapeaCompleto(): void
    {
        $pdo  = $this->pdo(prepare: [$this->stmt(['fetchAll' => [$this->fila(), $this->fila(['seccion_id' => null])]])]);
        $rows = (new EnlaceModel($pdo))->deOficina(2);

        $this->assertSame(1, $rows[0]['seccion_id']);
        $this->assertNull($rows[1]['seccion_id']);
        $this->assertTrue($rows[0]['managed']);
    }

    public function testPorIdDevuelveEnlaceONull(): void
    {
        $ok = new EnlaceModel($this->pdo(prepare: [$this->stmt(['fetch' => $this->fila()])]));
        $this->assertSame(3, $ok->porId(3, 2)['id']);

        $nf = new EnlaceModel($this->pdo(prepare: [$this->stmt(['fetch' => false])]));
        $this->assertNull($nf->porId(99, 2));
    }

    public function testArchivosManagedDeOficinaYSeccion(): void
    {
        $rows = [['subcarpeta' => 'planeamiento', 'nombre_archivo' => 'x.pdf']];

        $s1 = new EnlaceModel($this->pdo(prepare: [$this->stmt(['fetchAll' => $rows])]));
        $this->assertCount(1, $s1->archivosManagedDeOficina(2));

        $s2 = new EnlaceModel($this->pdo(prepare: [$this->stmt(['fetchAll' => $rows])]));
        $this->assertCount(1, $s2->archivosManagedDeSeccion(1));
    }

    public function testSiguienteOrdenSueltoYSeccion(): void
    {
        $suelto = new EnlaceModel($this->pdo(prepare: [$this->stmt(['fetchColumn' => '0'])]));
        $this->assertSame(0, $suelto->siguienteOrden(2, null));

        $seccion = new EnlaceModel($this->pdo(prepare: [$this->stmt(['fetchColumn' => '5'])]));
        $this->assertSame(5, $seccion->siguienteOrden(2, 1));
    }

    public function testCrearDevuelveId(): void
    {
        $stmt = $this->stmt();
        $stmt->expects($this->once())->method('execute');
        $pdo  = $this->pdo(prepare: [$stmt], lastInsertId: '50');

        $id = (new EnlaceModel($pdo))->crear(2, 1, [
            'titulo' => 't', 'tipo' => 'enlace', 'url' => 'https://x', 'subcarpeta' => null,
            'nombre_archivo' => null, 'ext' => null, 'tamano' => 0, 'fecha' => null,
            'orden' => 0, 'publicado' => 1, 'managed' => 0,
        ]);

        $this->assertSame(50, $id);
    }

    public function testActualizarTrueCuandoRowCountPositivo(): void
    {
        $pdo = $this->pdo(prepare: [$this->stmt(['rowCount' => 1])]);
        $this->assertTrue((new EnlaceModel($pdo))->actualizar(3, 2, $this->campos()));
    }

    public function testActualizarFallbackAPorId(): void
    {
        $pdo = $this->pdo(prepare: [
            $this->stmt(['rowCount' => 0]),
            $this->stmt(['fetch' => $this->fila()]),
        ]);
        $this->assertTrue((new EnlaceModel($pdo))->actualizar(3, 2, $this->campos()));
    }

    public function testEliminar(): void
    {
        $pdo = $this->pdo(prepare: [$this->stmt(['rowCount' => 1])]);
        $this->assertTrue((new EnlaceModel($pdo))->eliminar(3));
    }

    /** @return array<string,mixed> */
    private function campos(): array
    {
        return ['titulo' => 't', 'url' => '/x.pdf', 'fecha' => null, 'orden' => 0, 'publicado' => 1];
    }
}
