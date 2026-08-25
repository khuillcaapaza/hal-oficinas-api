<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Identidad de la sesión. La autenticación (login, 2FA, emisión del JWT) la hace
 * el servicio central hal-auth; aquí solo se exponen los claims del token ya
 * validado por el middleware, para que el panel verifique la sesión.
 */
final class SessionController extends Controller
{
    /** GET /me — devuelve los claims del JWT del usuario autenticado. */
    public function me(Request $request, Response $response): Response
    {
        $claims = $request->getAttribute('token');

        if ($claims === null) {
            return $this->json($response, ['error' => 'No autorizado'], 401);
        }

        return $this->json($response, ['usuario' => $claims]);
    }
}
