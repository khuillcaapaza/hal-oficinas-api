<?php

declare(strict_types=1);

use App\Controller\OficinaController;
use Slim\App;

/**
 * Rutas del portal de gestión de oficinas.
 *
 * Lectura pública (GET /oficinas, GET /oficinas/{slug}) y CRUD protegido por
 * JWT bajo /admin/oficinas. La lógica vive en App\Controller\OficinaController.
 */
return function (App $app): void {
    // Lectura pública.
    $app->get('/oficinas', [OficinaController::class, 'index']);
    $app->get('/oficinas/{slug}', [OficinaController::class, 'show']);

    // Administración de oficinas (requiere JWT).
    $app->get('/admin/oficinas', [OficinaController::class, 'adminIndex']);
    $app->put('/admin/oficinas/reordenar', [OficinaController::class, 'reorder']);
    $app->get('/admin/oficinas/{slug}', [OficinaController::class, 'adminShow']);
    $app->post('/admin/oficinas', [OficinaController::class, 'store']);
    $app->put('/admin/oficinas/{slug}', [OficinaController::class, 'update']);
    $app->delete('/admin/oficinas/{slug}', [OficinaController::class, 'destroy']);

    // Secciones (botones de acción) de una oficina.
    $app->post('/admin/oficinas/{slug}/secciones', [OficinaController::class, 'storeSeccion']);
    $app->put('/admin/oficinas/{slug}/secciones/{id:[0-9]+}', [OficinaController::class, 'updateSeccion']);
    $app->delete('/admin/oficinas/{slug}/secciones/{id:[0-9]+}', [OficinaController::class, 'destroySeccion']);

    // Enlaces (documentos o URLs) de una oficina o sección.
    $app->post('/admin/oficinas/{slug}/enlaces', [OficinaController::class, 'storeEnlace']);
    $app->put('/admin/oficinas/{slug}/enlaces/{id:[0-9]+}', [OficinaController::class, 'updateEnlace']);
    $app->delete('/admin/oficinas/{slug}/enlaces/{id:[0-9]+}', [OficinaController::class, 'destroyEnlace']);
};
