<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\OficinaController;
use App\Model\AutoridadModel;
use App\Model\EnlaceModel;
use App\Model\OficinaModel;
use App\Model\SeccionModel;
use Tests\TestCase;

final class OficinaControllerTest extends TestCase
{
    private function ctrl(
        ?OficinaModel $oficinas = null,
        ?AutoridadModel $autoridades = null,
        ?SeccionModel $secciones = null,
        ?EnlaceModel $enlaces = null,
        ?callable $relay = null
    ): OficinaController {
        return new OficinaController(
            $oficinas ?? $this->createMock(OficinaModel::class),
            $autoridades ?? $this->createMock(AutoridadModel::class),
            $secciones ?? $this->createMock(SeccionModel::class),
            $enlaces ?? $this->createMock(EnlaceModel::class),
            $relay ?? static fn (): bool => true,
        );
    }

    // ── Lectura pública ───────────────────────────────────────────────

    public function testIndexDevuelvePublicadas(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('publicados')->willReturn([['slug' => 'ti']]);

        $resp = $this->ctrl($oficinas)->index($this->request(), $this->response());

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertCount(1, $this->jsonBody($resp)['oficinas']);
    }

    public function testShow404CuandoNoExiste(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('publicadoPorSlug')->willReturn(null);

        $resp = $this->ctrl($oficinas)->show($this->request(), $this->response(), ['slug' => 'x']);

        $this->assertSame(404, $resp->getStatusCode());
    }

    public function testShowArmaDetalleCompleto(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('publicadoPorSlug')->willReturn($this->oficinaFila());

        $autoridades = $this->createMock(AutoridadModel::class);
        $autoridades->method('deOficina')->willReturn([['cargo' => 'Jefe', 'nombre' => 'Ana']]);

        $secciones = $this->createMock(SeccionModel::class);
        $secciones->method('deOficina')->willReturn([['id' => 7, 'slug' => 'sec', 'titulo' => 'Sec']]);

        $enlaces = $this->createMock(EnlaceModel::class);
        $enlaces->method('publicadosDeSeccion')->willReturn([['id' => 1, 'titulo' => 'PDF']]);
        $enlaces->method('publicadosSueltos')->willReturn([['id' => 2, 'titulo' => 'Suelto']]);

        $resp = $this->ctrl($oficinas, $autoridades, $secciones, $enlaces)
            ->show($this->request(), $this->response(), ['slug' => 'ti']);

        $data = $this->jsonBody($resp)['oficina'];
        $this->assertSame('ti', $data['slug']);
        $this->assertCount(1, $data['autoridades']);
        $this->assertCount(1, $data['secciones']);
        $this->assertCount(1, $data['secciones'][0]['enlaces']);
        $this->assertCount(1, $data['enlaces']);
    }

    // ── Admin: listado y reorden ──────────────────────────────────────

    public function testAdminIndexPaginado(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('todosPaginado')->willReturn([
            'items' => [['slug' => 'ti']], 'total' => 1, 'page' => 1, 'per_page' => 12, 'total_pages' => 1,
        ]);

        $resp = $this->ctrl($oficinas)->adminIndex($this->request(), $this->response());
        $body = $this->jsonBody($resp);

        $this->assertCount(1, $body['oficinas']);
        $this->assertSame(1, $body['meta']['total']);
    }

    public function testReorder422SinItems(): void
    {
        $resp = $this->ctrl()->reorder($this->request('PUT', ['items' => []]), $this->response());
        $this->assertSame(422, $resp->getStatusCode());
    }

    public function testReorder422SlugDuplicado(): void
    {
        $resp = $this->ctrl()->reorder($this->request('PUT', ['items' => [
            ['slug' => 'a', 'orden' => 0],
            ['slug' => 'a', 'orden' => 1],
        ]]), $this->response());
        $this->assertSame(422, $resp->getStatusCode());
    }

    public function testReorderOk(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('reordenar')->willReturn([true, []]);

        $resp = $this->ctrl($oficinas)->reorder($this->request('PUT', ['items' => [
            ['slug' => 'a', 'orden' => 0],
            ['slug' => 'b', 'orden' => 1],
        ]]), $this->response());

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertTrue($this->jsonBody($resp)['ok']);
    }

    public function testReorder404CuandoFaltantes(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('reordenar')->willReturn([false, ['z']]);

        $resp = $this->ctrl($oficinas)->reorder($this->request('PUT', ['items' => [
            ['slug' => 'z', 'orden' => 0],
        ]]), $this->response());

        $this->assertSame(404, $resp->getStatusCode());
    }

    public function testAdminShow404YOk(): void
    {
        $nf = $this->createMock(OficinaModel::class);
        $nf->method('porSlug')->willReturn(null);
        $this->assertSame(404, $this->ctrl($nf)->adminShow($this->request(), $this->response(), ['slug' => 'x'])->getStatusCode());

        $ok = $this->createMock(OficinaModel::class);
        $ok->method('porSlug')->willReturn($this->oficinaFila());
        $resp = $this->ctrl($ok)->adminShow($this->request(), $this->response(), ['slug' => 'ti']);
        $this->assertArrayHasKey('autoridades', $this->jsonBody($resp)['oficina']);
    }

    // ── Admin: crear / actualizar / borrar ────────────────────────────

    public function testStore422SinTitulo(): void
    {
        $resp = $this->ctrl()->store($this->request('POST', ['titulo' => '']), $this->response());
        $this->assertSame(422, $resp->getStatusCode());
    }

    public function testStore409SlugExistente(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('existeSlug')->willReturn(true);

        $resp = $this->ctrl($oficinas)->store($this->request('POST', ['titulo' => 'TI']), $this->response());
        $this->assertSame(409, $resp->getStatusCode());
    }

    public function testStoreOkReemplazaAutoridades(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('existeSlug')->willReturn(false);
        $oficinas->method('siguienteOrden')->willReturn(0);
        $oficinas->method('crear')->willReturn(5);

        $autoridades = $this->createMock(AutoridadModel::class);
        $autoridades->expects($this->once())->method('reemplazar')
            ->with(5, [['cargo' => 'Jefe', 'nombre' => 'Ana']]);

        $resp = $this->ctrl($oficinas, $autoridades)->store($this->request('POST', [
            'titulo'      => 'TI',
            'autoridades' => [['cargo' => 'Jefe', 'nombre' => 'Ana'], ['cargo' => '', 'nombre' => 'X']],
        ]), $this->response());

        $this->assertSame(201, $resp->getStatusCode());
    }

    public function testUpdate404YOk(): void
    {
        $nf = $this->createMock(OficinaModel::class);
        $nf->method('actualizar')->willReturn(false);
        $this->assertSame(404, $this->ctrl($nf)->update($this->request('PUT', ['titulo' => 'TI']), $this->response(), ['slug' => 'ti'])->getStatusCode());

        $ok = $this->createMock(OficinaModel::class);
        $ok->method('actualizar')->willReturn(true);
        $ok->method('idPorSlug')->willReturn(5);
        $autoridades = $this->createMock(AutoridadModel::class);
        $autoridades->expects($this->once())->method('reemplazar');
        $resp = $this->ctrl($ok, $autoridades)->update($this->request('PUT', ['titulo' => 'TI']), $this->response(), ['slug' => 'ti']);
        $this->assertSame(200, $resp->getStatusCode());
    }

    public function testDestroy404YOkConRelay(): void
    {
        $nf = $this->createMock(OficinaModel::class);
        $nf->method('idPorSlug')->willReturn(null);
        $this->assertSame(404, $this->ctrl($nf)->destroy($this->request('DELETE'), $this->response(), ['slug' => 'x'])->getStatusCode());

        $ok = $this->createMock(OficinaModel::class);
        $ok->method('idPorSlug')->willReturn(5);
        $ok->expects($this->once())->method('eliminar');
        $enlaces = $this->createMock(EnlaceModel::class);
        $enlaces->method('archivosManagedDeOficina')->willReturn([
            ['subcarpeta' => 'ti', 'nombre_archivo' => 'x.pdf'],
        ]);
        // relay falla → debe reportar advertencia
        $resp = $this->ctrl($ok, null, null, $enlaces, static fn (): bool => false)
            ->destroy($this->request('DELETE'), $this->response(), ['slug' => 'ti']);
        $body = $this->jsonBody($resp);
        $this->assertTrue($body['ok']);
        $this->assertArrayHasKey('no_borrados', $body);
    }

    // ── Secciones ─────────────────────────────────────────────────────

    public function testStoreSeccionOk(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('idPorSlug')->willReturn(5);
        $secciones = $this->createMock(SeccionModel::class);
        $secciones->method('siguienteOrden')->willReturn(0);
        $secciones->method('crear')->willReturn(7);

        $resp = $this->ctrl($oficinas, null, $secciones)
            ->storeSeccion($this->request('POST', ['titulo' => 'Documentos']), $this->response(), ['slug' => 'ti']);

        $this->assertSame(201, $resp->getStatusCode());
        $this->assertSame(7, $this->jsonBody($resp)['id']);
    }

    public function testStoreSeccion404SinOficina(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('idPorSlug')->willReturn(null);
        $resp = $this->ctrl($oficinas)->storeSeccion($this->request('POST', ['titulo' => 'x']), $this->response(), ['slug' => 'x']);
        $this->assertSame(404, $resp->getStatusCode());
    }

    public function testUpdateSeccion404CuandoNoExiste(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('idPorSlug')->willReturn(5);
        $secciones = $this->createMock(SeccionModel::class);
        $secciones->method('porId')->willReturn(null);
        $resp = $this->ctrl($oficinas, null, $secciones)
            ->updateSeccion($this->request('PUT', ['titulo' => 'x']), $this->response(), ['slug' => 'ti', 'id' => '9']);
        $this->assertSame(404, $resp->getStatusCode());
    }

    public function testDestroySeccionOk(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('idPorSlug')->willReturn(5);
        $secciones = $this->createMock(SeccionModel::class);
        $secciones->method('porId')->willReturn(['id' => 7]);
        $secciones->expects($this->once())->method('eliminar');
        $enlaces = $this->createMock(EnlaceModel::class);
        $enlaces->method('archivosManagedDeSeccion')->willReturn([]);

        $resp = $this->ctrl($oficinas, null, $secciones, $enlaces)
            ->destroySeccion($this->request('DELETE'), $this->response(), ['slug' => 'ti', 'id' => '7']);
        $this->assertSame(200, $resp->getStatusCode());
    }

    // ── Enlaces ───────────────────────────────────────────────────────

    public function testStoreEnlaceTipoEnlaceOk(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('idPorSlug')->willReturn(5);
        $enlaces = $this->createMock(EnlaceModel::class);
        $enlaces->method('siguienteOrden')->willReturn(0);
        $enlaces->method('crear')->willReturn(11);

        $resp = $this->ctrl($oficinas, null, null, $enlaces)->storeEnlace(
            $this->request('POST', ['titulo' => 'Sitio', 'tipo' => 'enlace', 'url' => 'https://x']),
            $this->response(),
            ['slug' => 'ti']
        );

        $this->assertSame(201, $resp->getStatusCode());
    }

    public function testStoreEnlaceArchivo422SinNombre(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('idPorSlug')->willReturn(5);

        $resp = $this->ctrl($oficinas)->storeEnlace(
            $this->request('POST', ['titulo' => 'Doc', 'tipo' => 'archivo']),
            $this->response(),
            ['slug' => 'ti']
        );

        $this->assertSame(422, $resp->getStatusCode());
    }

    public function testUpdateEnlace422FechaInvalida(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('idPorSlug')->willReturn(5);
        $enlaces = $this->createMock(EnlaceModel::class);
        $enlaces->method('porId')->willReturn(['id' => 11, 'url' => '/x.pdf', 'orden' => 0, 'publicado' => true]);

        $resp = $this->ctrl($oficinas, null, null, $enlaces)->updateEnlace(
            $this->request('PUT', ['titulo' => 'Doc', 'fecha' => '2026-13-40']),
            $this->response(),
            ['slug' => 'ti', 'id' => '11']
        );

        $this->assertSame(422, $resp->getStatusCode());
    }

    public function testDestroyEnlaceArchivoManagedRelay(): void
    {
        $oficinas = $this->createMock(OficinaModel::class);
        $oficinas->method('idPorSlug')->willReturn(5);
        $enlaces = $this->createMock(EnlaceModel::class);
        $enlaces->method('porId')->willReturn([
            'id' => 11, 'tipo' => 'archivo', 'managed' => true,
            'subcarpeta' => 'ti', 'nombre_archivo' => 'x.pdf',
        ]);
        $enlaces->expects($this->once())->method('eliminar');

        $llamado = false;
        $resp = $this->ctrl($oficinas, null, null, $enlaces, function () use (&$llamado): bool {
            $llamado = true;
            return true;
        })->destroyEnlace($this->request('DELETE'), $this->response(), ['slug' => 'ti', 'id' => '11']);

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertTrue($llamado);
    }

    /** @return array<string,mixed> */
    private function oficinaFila(): array
    {
        return [
            'id' => 2, 'slug' => 'ti', 'titulo' => 'TI', 'categoria' => 'Oficina', 'excerpt' => '',
            'contenido' => '', 'contacto_telefono' => '', 'contacto_email' => '', 'contacto_horario' => '',
            'ubic_edificio' => '', 'ubic_piso' => '', 'ubic_referencia' => '', 'ubic_map_url' => '',
            'orden' => 0, 'publicado' => true,
        ];
    }
}
