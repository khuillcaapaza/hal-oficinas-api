<?php

declare(strict_types=1);

namespace Tests\Model;

use App\Model\OficinaModel;
use Tests\TestCase;

final class OficinaModelTest extends TestCase
{
    /** @return array<string,mixed> */
    private function fila(array $over = []): array
    {
        return array_merge([
            'id' => 2, 'slug' => 'estadistica-ti', 'titulo' => 'Estadística y TI',
            'categoria' => 'Oficina', 'excerpt' => 'resumen', 'contenido' => '# Cuerpo',
            'contacto_telefono' => '084-1', 'contacto_email' => 'ti@hal.pe', 'contacto_horario' => 'L-V',
            'ubic_edificio' => 'Central', 'ubic_piso' => 'Piso 2', 'ubic_referencia' => 'ref',
            'ubic_map_url' => 'https://maps', 'orden' => 1, 'publicado' => 1,
        ], $over);
    }

    public function testPublicadosMapeaCards(): void
    {
        $pdo   = $this->pdo(query: [$this->stmt(['fetchAll' => [$this->fila()]])]);
        $cards = (new OficinaModel($pdo))->publicados();

        $this->assertCount(1, $cards);
        $this->assertSame('estadistica-ti', $cards[0]['slug']);
        $this->assertSame('Oficina', $cards[0]['categoria']);
        $this->assertArrayNotHasKey('contenido', $cards[0]); // mapCard es compacto
    }

    public function testTodosMapeaCompleto(): void
    {
        $pdo  = $this->pdo(query: [$this->stmt(['fetchAll' => [$this->fila()]])]);
        $rows = (new OficinaModel($pdo))->todos();

        $this->assertTrue($rows[0]['publicado']);
        $this->assertSame('ti@hal.pe', $rows[0]['contacto_email']);
        $this->assertSame('# Cuerpo', $rows[0]['contenido']);
    }

    public function testTodosPaginadoDevuelveItemsYMeta(): void
    {
        $pdo = $this->pdo(
            prepare: [$this->stmt(['fetchAll' => [$this->fila()]])],
            query: [$this->stmt(['fetchColumn' => 8])],
        );

        $res = (new OficinaModel($pdo))->todosPaginado(2, 6);

        $this->assertSame(8, $res['total']);
        $this->assertSame(2, $res['page']);
        $this->assertSame(6, $res['per_page']);
        $this->assertSame(2, $res['total_pages']);
        $this->assertCount(1, $res['items']);
    }

    public function testPorSlugDevuelveOficinaONull(): void
    {
        $ok = new OficinaModel($this->pdo(prepare: [$this->stmt(['fetch' => $this->fila()])]));
        $this->assertSame('estadistica-ti', $ok->porSlug('estadistica-ti')['slug']);

        $nf = new OficinaModel($this->pdo(prepare: [$this->stmt(['fetch' => false])]));
        $this->assertNull($nf->porSlug('nope'));
    }

    public function testPublicadoPorSlug(): void
    {
        $ok = new OficinaModel($this->pdo(prepare: [$this->stmt(['fetch' => $this->fila()])]));
        $this->assertSame(2, $ok->publicadoPorSlug('estadistica-ti')['id']);
    }

    public function testExisteSlugEIdPorSlug(): void
    {
        $existe = new OficinaModel($this->pdo(prepare: [$this->stmt(['fetchColumn' => 1])]));
        $this->assertTrue($existe->existeSlug('estadistica-ti'));

        $id = new OficinaModel($this->pdo(prepare: [$this->stmt(['fetchColumn' => '2'])]));
        $this->assertSame(2, $id->idPorSlug('estadistica-ti'));

        $nf = new OficinaModel($this->pdo(prepare: [$this->stmt(['fetchColumn' => false])]));
        $this->assertNull($nf->idPorSlug('nope'));
    }

    public function testSiguienteOrden(): void
    {
        $pdo = $this->pdo(query: [$this->stmt(['fetchColumn' => '3'])]);
        $this->assertSame(3, (new OficinaModel($pdo))->siguienteOrden());
    }

    public function testCrearDevuelveId(): void
    {
        $stmt = $this->stmt();
        $stmt->expects($this->once())->method('execute');
        $pdo  = $this->pdo(prepare: [$stmt], lastInsertId: '9');

        $id = (new OficinaModel($pdo))->crear($this->campos());
        $this->assertSame(9, $id);
    }

    public function testActualizarTrueCuandoRowCountPositivo(): void
    {
        $pdo = $this->pdo(prepare: [$this->stmt(['rowCount' => 1])]);
        $this->assertTrue((new OficinaModel($pdo))->actualizar('estadistica-ti', $this->campos()));
    }

    public function testActualizarFallbackAExisteSlug(): void
    {
        $pdo = $this->pdo(prepare: [
            $this->stmt(['rowCount' => 0]),
            $this->stmt(['fetchColumn' => 1]),
        ]);
        $this->assertTrue((new OficinaModel($pdo))->actualizar('estadistica-ti', $this->campos()));
    }

    public function testEliminar(): void
    {
        $pdo = $this->pdo(prepare: [$this->stmt(['rowCount' => 1])]);
        $this->assertTrue((new OficinaModel($pdo))->eliminar('estadistica-ti'));
    }

    public function testReordenarOkCuandoTodosExisten(): void
    {
        $pdo = $this->createMock(\PDO::class);
        $select = $this->stmt(['fetchAll' => ['a', 'b']]);
        $upd    = $this->stmt();
        $pdo->method('query')->willReturn($select);
        $pdo->method('prepare')->willReturn($upd);
        $pdo->method('beginTransaction')->willReturn(true);
        $pdo->method('commit')->willReturn(true);

        [$ok, $faltantes] = (new OficinaModel($pdo))->reordenar([
            ['slug' => 'a', 'orden' => 0],
            ['slug' => 'b', 'orden' => 1],
        ]);

        $this->assertTrue($ok);
        $this->assertSame([], $faltantes);
    }

    public function testReordenarDetectaFaltantes(): void
    {
        $pdo = $this->pdo(query: [$this->stmt(['fetchAll' => ['a']])]);

        [$ok, $faltantes] = (new OficinaModel($pdo))->reordenar([
            ['slug' => 'a', 'orden' => 0],
            ['slug' => 'z', 'orden' => 1],
        ]);

        $this->assertFalse($ok);
        $this->assertSame(['z'], $faltantes);
    }

    public function testReordenarVacioEsOk(): void
    {
        $pdo = $this->pdo();
        [$ok, $faltantes] = (new OficinaModel($pdo))->reordenar([]);
        $this->assertTrue($ok);
        $this->assertSame([], $faltantes);
    }

    /** @return array<string,mixed> */
    private function campos(): array
    {
        return [
            'slug' => 'estadistica-ti', 'titulo' => 'Estadística y TI', 'categoria' => 'Oficina',
            'excerpt' => 'r', 'contenido' => 'c', 'contacto_telefono' => '', 'contacto_email' => '',
            'contacto_horario' => '', 'ubic_edificio' => '', 'ubic_piso' => '', 'ubic_referencia' => '',
            'ubic_map_url' => '', 'orden' => 0, 'publicado' => 1,
        ];
    }
}
