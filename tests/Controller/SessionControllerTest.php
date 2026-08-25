<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\SessionController;
use Tests\TestCase;

final class SessionControllerTest extends TestCase
{
    public function testMeDevuelveLosClaimsDelToken(): void
    {
        $claims = ['sub' => 1, 'usuario' => 'admin', 'rol' => 'admin', 'modulos' => ['servicios']];
        $request = $this->request('GET', null, [], [], ['token' => $claims]);

        $resp = (new SessionController())->me($request, $this->response());

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertSame($claims, $this->jsonBody($resp)['usuario']);
    }

    public function testMeSinTokenDevuelve401(): void
    {
        $resp = (new SessionController())->me($this->request(), $this->response());

        $this->assertSame(401, $resp->getStatusCode());
    }
}
