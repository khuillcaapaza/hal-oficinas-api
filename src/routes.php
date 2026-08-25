<?php

declare(strict_types=1);

use App\Controller\HealthController;
use App\Controller\SessionController;
use Slim\App;

/**
 * Rutas de autenticación y salud. El mapeo URL → controlador vive aquí;
 * la lógica está en App\Controller\* (arquitectura MVC).
 */
return function (App $app): void {
    $app->get('/health', [HealthController::class, 'index']);
    // /me: permite al frontend verificar la sesión (JWT emitido por hal-auth-api).
    $app->get('/me', [SessionController::class, 'me']);
};
