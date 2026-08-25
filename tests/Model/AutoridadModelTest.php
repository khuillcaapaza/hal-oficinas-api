<?php

declare(strict_types=1);

namespace Tests\Model;

use App\Model\AutoridadModel;
use PDO;
use Tests\TestCase;

final class AutoridadModelTest extends TestCase
{
    public function testDeOficinaMapea(): void
    {
        $fila = ['id' => 1, 'cargo' => 'Jefe', 'nombre' => 'Ana', 'orden' => 0];
        $pdo  = $this->pdo(prepare: [$this->stmt(['fetchAll' => [$fila]])]);

        $rows = (new AutoridadModel($pdo))->deOficina(2);

        $this->assertSame('Jefe', $rows[0]['cargo']);
        $this->assertSame('Ana', $rows[0]['nombre']);
    }

    public function testReemplazarBorraEInsertaEnTransaccion(): void
    {
        $pdo = $this->createMock(PDO::class);
        $del = $this->stmt();
        $ins = $this->stmt();
        $ins->expects($this->exactly(2))->method('execute');

        $prepareQueue = [$del, $ins];
        $pdo->method('prepare')->willReturnCallback(
            static function () use (&$prepareQueue) {
                return array_shift($prepareQueue);
            }
        );
        $pdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $pdo->expects($this->once())->method('commit')->willReturn(true);

        (new AutoridadModel($pdo))->reemplazar(2, [
            ['cargo' => 'Jefe', 'nombre' => 'Ana'],
            ['cargo' => 'Sub', 'nombre' => 'Beto'],
        ]);
    }

    public function testReemplazarHaceRollbackAnteError(): void
    {
        $pdo = $this->createMock(PDO::class);
        $del = $this->stmt();
        $del->method('execute')->willThrowException(new \RuntimeException('boom'));

        $pdo->method('prepare')->willReturn($del);
        $pdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $pdo->expects($this->once())->method('rollBack')->willReturn(true);

        $this->expectException(\RuntimeException::class);
        (new AutoridadModel($pdo))->reemplazar(2, [['cargo' => 'X', 'nombre' => 'Y']]);
    }
}
