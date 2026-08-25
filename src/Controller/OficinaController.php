<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\AutoridadModel;
use App\Model\EnlaceModel;
use App\Model\OficinaModel;
use App\Model\SeccionModel;
use App\Support\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * CRUD del portal de gestión de oficinas.
 *
 * Lectura pública (GET /oficinas, GET /oficinas/{slug}) y administración
 * protegida por JWT bajo /admin/oficinas. Cada oficina tiene datos informativos
 * (contacto, ubicación, autoridades, contenido) más secciones (botones de
 * acción) y enlaces (documentos/URLs). Los archivos los sube el panel
 * DIRECTAMENTE a hal-archivos-api; aquí solo se registra el metadato y, al
 * borrar un archivo "managed", se reenvía la orden de borrado físico.
 */
final class OficinaController extends Controller
{
    /** Extensiones permitidas para los enlaces tipo=archivo. */
    private const EXT_PERMITIDAS = ['pdf', 'jpg', 'jpeg', 'png'];

    private OficinaModel $oficinas;
    private AutoridadModel $autoridades;
    private SeccionModel $secciones;
    private EnlaceModel $enlaces;

    /** @var callable(string, string, string): bool */
    private $relayDelete;

    public function __construct(
        ?OficinaModel $oficinas = null,
        ?AutoridadModel $autoridades = null,
        ?SeccionModel $secciones = null,
        ?EnlaceModel $enlaces = null,
        ?callable $relayDelete = null
    ) {
        $this->oficinas    = $oficinas ?? new OficinaModel();
        $this->autoridades = $autoridades ?? new AutoridadModel();
        $this->secciones   = $secciones ?? new SeccionModel();
        $this->enlaces     = $enlaces ?? new EnlaceModel();
        $this->relayDelete = $relayDelete ?? [$this, 'relayBorradoHttp'];
    }

    // ── Lectura pública ───────────────────────────────────────────────

    /** GET /oficinas — fichas publicadas (forma compacta). */
    public function index(Request $request, Response $response): Response
    {
        return $this->json($response, ['oficinas' => $this->oficinas->publicados()]);
    }

    /** GET /oficinas/{slug} — una oficina publicada con autoridades, secciones y enlaces. */
    public function show(Request $request, Response $response, array $args): Response
    {
        $of = $this->oficinas->publicadoPorSlug((string) $args['slug']);
        if ($of === null) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        return $this->json($response, ['oficina' => $this->armarDetallePublico($of)]);
    }

    // ── Administración de oficinas (requiere JWT) ─────────────────────

    /** GET /admin/oficinas — todas (incluye no publicadas), paginado. */
    public function adminIndex(Request $request, Response $response): Response
    {
        $q       = $request->getQueryParams();
        $page    = (int) ($q['page'] ?? 1);
        $perPage = (int) ($q['per_page'] ?? 12);

        $resultado = $this->oficinas->todosPaginado($page, $perPage);

        return $this->json($response, [
            'oficinas' => $resultado['items'],
            'meta'     => [
                'total'       => $resultado['total'],
                'page'        => $resultado['page'],
                'per_page'    => $resultado['per_page'],
                'total_pages' => $resultado['total_pages'],
            ],
        ]);
    }

    /** PUT /admin/oficinas/reordenar — actualiza el orden de múltiples oficinas. */
    public function reorder(Request $request, Response $response): Response
    {
        $data  = (array) $request->getParsedBody();
        $items = $data['items'] ?? null;

        if (!is_array($items) || $items === []) {
            return $this->json($response, ['error' => 'Debes enviar una lista de items a reordenar.'], 422);
        }

        $normalizados  = [];
        $soloSlugs     = true;
        $slugsLegacy   = [];
        $refsVistas    = [];
        $ordenesVistas = [];
        foreach ($items as $it) {
            if (!is_array($it)) {
                return $this->json($response, ['error' => 'Formato de item inválido.'], 422);
            }

            $uuid = trim((string) ($it['uuid'] ?? ''));
            $slug = trim((string) ($it['slug'] ?? ''));
            $ref = $uuid !== '' ? $uuid : $slug;
            if ($ref === '') {
                return $this->json($response, ['error' => 'Cada item debe incluir uuid o slug.'], 422);
            }
            if ($uuid !== '') {
                $soloSlugs = false;
            }
            if (isset($refsVistas[$ref])) {
                return $this->json($response, ['error' => 'No se permiten referencias duplicadas en el reordenamiento.'], 422);
            }
            $refsVistas[$ref] = true;

            $orden = (int) ($it['orden'] ?? -1);
            if ($orden < 0) {
                return $this->json($response, ['error' => 'Cada item debe incluir un orden entero mayor o igual a 0.'], 422);
            }
            if (isset($ordenesVistas[$orden])) {
                return $this->json($response, ['error' => 'No se permiten valores de orden repetidos.'], 422);
            }
            $ordenesVistas[$orden] = true;

            $normalizados[] = ['ref' => $ref, 'orden' => $orden];
            if ($slug !== '') {
                $slugsLegacy[] = ['slug' => $slug, 'orden' => $orden];
            }
        }

        if ($soloSlugs && count($slugsLegacy) === count($normalizados)) {
            [$ok, $faltantes] = $this->oficinas->reordenar($slugsLegacy);
        } else {
            [$ok, $faltantes] = $this->oficinas->reordenarPorReferencia($normalizados);
        }
        if (!$ok) {
            return $this->json($response, [
                'error'     => 'No se pudo completar el reordenamiento.',
                'faltantes' => $faltantes,
            ], 404);
        }

        return $this->json($response, ['ok' => true]);
    }

    /** GET /admin/oficinas/{slug|uuid} — oficina completa con autoridades, secciones y enlaces. */
    public function adminShow(Request $request, Response $response, array $args): Response
    {
        $of = $this->resolverOficina((string) $args['slug']);
        if ($of === null) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        $of['autoridades'] = $this->autoridades->deOficina($of['id']);
        $of['secciones']   = $this->secciones->deOficina($of['id']);
        $of['enlaces']     = $this->enlaces->deOficina($of['id']);

        return $this->json($response, ['oficina' => $of]);
    }

    /** POST /admin/oficinas — crear oficina. */
    public function store(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        $uuid = $this->generarUuidV4();
        if (trim((string) ($body['slug'] ?? '')) === '') {
            $body['slug'] = 'oficina-' . substr(str_replace('-', '', $uuid), 0, 8);
        }

        [$campos, $error] = $this->validarOficina($body);
        if ($error !== null) {
            return $this->json($response, ['error' => $error], 422);
        }

        if ($this->oficinas->existeSlug($campos['slug'])) {
            return $this->json($response, ['error' => 'Ya existe una oficina con ese slug.'], 409);
        }

        $campos['uuid'] = $uuid;

        $autoridades = $campos['autoridades'];
        unset($campos['autoridades']);

        $id = $this->oficinas->crear($campos);
        $this->autoridades->reemplazar($id, $autoridades);

        return $this->json($response, ['ok' => true, 'uuid' => $campos['uuid'], 'slug' => $campos['slug']], 201);
    }

    /** PUT /admin/oficinas/{slug|uuid} — actualizar oficina. */
    public function update(Request $request, Response $response, array $args): Response
    {
        $ref = (string) $args['slug'];
        $of  = $this->resolverOficina($ref);

        // Compatibilidad: si aún llega slug y no se pudo resolver por referencia,
        // conserva el flujo histórico por slug.
        if ($of === null && !$this->esUuid($ref)) {
            $body = (array) $request->getParsedBody();
            $body['slug'] = $ref;

            [$campos, $error] = $this->validarOficina($body);
            if ($error !== null) {
                return $this->json($response, ['error' => $error], 422);
            }

            $autoridades = $campos['autoridades'];
            unset($campos['autoridades']);

            if (!$this->oficinas->actualizar($ref, $campos)) {
                return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
            }

            $id = $this->oficinas->idPorSlug($ref);
            if ($id !== null) {
                $this->autoridades->reemplazar($id, $autoridades);
            }

            return $this->json($response, ['ok' => true, 'slug' => $ref]);
        }

        if ($of === null) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        $body = (array) $request->getParsedBody();
        if (trim((string) ($body['slug'] ?? '')) === '') {
            $body['slug'] = $of['slug'];
        }

        [$campos, $error] = $this->validarOficina($body);
        if ($error !== null) {
            return $this->json($response, ['error' => $error], 422);
        }

        if ($campos['slug'] !== $of['slug'] && $this->oficinas->existeSlug($campos['slug'])) {
            return $this->json($response, ['error' => 'Ya existe una oficina con ese slug.'], 409);
        }

        $autoridades = $campos['autoridades'];
        unset($campos['autoridades']);

        if (!$this->oficinas->actualizarPorId((int) $of['id'], $campos)) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        $this->autoridades->reemplazar((int) $of['id'], $autoridades);

        return $this->json($response, ['ok' => true, 'uuid' => $of['uuid'], 'slug' => $campos['slug']]);
    }

    /** DELETE /admin/oficinas/{slug|uuid} — eliminar oficina + autoridades + secciones + enlaces. */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        $ref = (string) $args['slug'];
        $id  = $this->resolverOficinaId($ref);
        if ($id === null) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        $auth       = $request->getHeaderLine('Authorization');
        $noBorrados = $this->relayArchivos($this->enlaces->archivosManagedDeOficina($id), $auth);

        if ($this->esUuid($ref)) {
            $this->oficinas->eliminarPorId($id);
        } else {
            $this->oficinas->eliminar($ref);
        }

        return $this->json($response, $this->conAdvertencia($noBorrados));
    }

    // ── Administración de secciones ───────────────────────────────────

    /** POST /admin/oficinas/{slug|uuid}/secciones — crear sección. */
    public function storeSeccion(Request $request, Response $response, array $args): Response
    {
        $id = $this->resolverOficinaId((string) $args['slug']);
        if ($id === null) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        $raw = (array) $request->getParsedBody();
        $uuid = $this->generarUuidV4();
        if (trim((string) ($raw['slug'] ?? '')) === '') {
            $raw['slug'] = 'seccion-' . substr(str_replace('-', '', $uuid), 0, 8);
        }

        [$campos, $error] = $this->validarSeccion($raw);
        if ($error !== null) {
            return $this->json($response, ['error' => $error], 422);
        }

        $campos['uuid'] = $uuid;
        $campos['orden'] = $this->secciones->siguienteOrden($id);
        $nuevoId         = $this->secciones->crear($id, $campos);

        return $this->json($response, ['ok' => true, 'id' => $nuevoId, 'uuid' => $uuid], 201);
    }

    /** PUT /admin/oficinas/{slug}/secciones/{id} — actualizar sección. */
    public function updateSeccion(Request $request, Response $response, array $args): Response
    {
        $id = $this->resolverOficinaId((string) $args['slug']);
        if ($id === null) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        $seccionId = (int) $args['id'];
        $actual    = $this->secciones->porId($seccionId, $id);
        if ($actual === null) {
            return $this->json($response, ['error' => 'Sección no encontrada'], 404);
        }

        [$campos, $error] = $this->validarSeccion((array) $request->getParsedBody());
        if ($error !== null) {
            return $this->json($response, ['error' => $error], 422);
        }
        $campos['orden'] = (int) ($request->getParsedBody()['orden'] ?? $actual['orden']);

        $this->secciones->actualizar($seccionId, $id, $campos);

        return $this->json($response, ['ok' => true, 'id' => $seccionId]);
    }

    /** DELETE /admin/oficinas/{slug}/secciones/{id} — eliminar sección + sus enlaces. */
    public function destroySeccion(Request $request, Response $response, array $args): Response
    {
        $id = $this->resolverOficinaId((string) $args['slug']);
        if ($id === null) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        $seccionId = (int) $args['id'];
        if ($this->secciones->porId($seccionId, $id) === null) {
            return $this->json($response, ['error' => 'Sección no encontrada'], 404);
        }

        $auth       = $request->getHeaderLine('Authorization');
        $noBorrados = $this->relayArchivos($this->enlaces->archivosManagedDeSeccion($seccionId), $auth);

        $this->secciones->eliminar($seccionId, $id);

        return $this->json($response, $this->conAdvertencia($noBorrados));
    }

    // ── Administración de enlaces ─────────────────────────────────────

    /** POST /admin/oficinas/{slug}/enlaces — registrar un enlace (opcional seccion_id). */
    public function storeEnlace(Request $request, Response $response, array $args): Response
    {
        $id = $this->resolverOficinaId((string) $args['slug']);
        if ($id === null) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        $data      = (array) $request->getParsedBody();
        $seccionId = $this->resolverSeccionId($data, $id);
        if ($seccionId === false) {
            return $this->json($response, ['error' => 'Sección no encontrada'], 404);
        }

        [$campos, $error] = $this->validarEnlace($data);
        if ($error !== null) {
            return $this->json($response, ['error' => $error], 422);
        }

        $campos['orden'] = $this->enlaces->siguienteOrden($id, $seccionId);
        $nuevoId         = $this->enlaces->crear($id, $seccionId, $campos);

        return $this->json($response, ['ok' => true, 'id' => $nuevoId], 201);
    }

    /** PUT /admin/oficinas/{slug}/enlaces/{id} — actualizar título/fecha/orden/publicado. */
    public function updateEnlace(Request $request, Response $response, array $args): Response
    {
        $id = $this->resolverOficinaId((string) $args['slug']);
        if ($id === null) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        $enlaceId = (int) $args['id'];
        $actual   = $this->enlaces->porId($enlaceId, $id);
        if ($actual === null) {
            return $this->json($response, ['error' => 'Enlace no encontrado'], 404);
        }

        $data   = (array) $request->getParsedBody();
        $titulo = trim((string) ($data['titulo'] ?? ''));
        if ($titulo === '') {
            return $this->json($response, ['error' => 'El título es obligatorio.'], 422);
        }

        $fecha = trim((string) ($data['fecha'] ?? ''));
        if ($fecha !== '' && !$this->fechaValida($fecha)) {
            return $this->json($response, ['error' => 'La fecha debe tener el formato AAAA-MM-DD.'], 422);
        }

        $this->enlaces->actualizar($enlaceId, $id, [
            'titulo'    => mb_substr($titulo, 0, 255),
            'url'       => $actual['url'],
            'fecha'     => $fecha === '' ? null : $fecha,
            'orden'     => (int) ($data['orden'] ?? $actual['orden']),
            'publicado' => filter_var($data['publicado'] ?? $actual['publicado'], FILTER_VALIDATE_BOOL) ? 1 : 0,
        ]);

        return $this->json($response, ['ok' => true, 'id' => $enlaceId]);
    }

    /** DELETE /admin/oficinas/{slug}/enlaces/{id} — borrar enlace (+ relay si archivo managed). */
    public function destroyEnlace(Request $request, Response $response, array $args): Response
    {
        $id = $this->resolverOficinaId((string) $args['slug']);
        if ($id === null) {
            return $this->json($response, ['error' => 'Oficina no encontrada'], 404);
        }

        $enlaceId = (int) $args['id'];
        $enlace   = $this->enlaces->porId($enlaceId, $id);
        if ($enlace === null) {
            return $this->json($response, ['error' => 'Enlace no encontrado'], 404);
        }

        $borradoOk = true;
        if ($enlace['tipo'] === 'archivo' && $enlace['managed']
            && $enlace['subcarpeta'] !== null && $enlace['nombre_archivo'] !== null) {
            $auth      = $request->getHeaderLine('Authorization');
            $borradoOk = ($this->relayDelete)((string) $enlace['subcarpeta'], (string) $enlace['nombre_archivo'], $auth);
        }

        $this->enlaces->eliminar($enlaceId);

        $payload = ['ok' => true];
        if (!$borradoOk) {
            $payload['advertencia'] = 'El metadato se eliminó, pero el archivo físico no pudo borrarse.';
        }

        return $this->json($response, $payload);
    }

    // ── Ensamblado de la respuesta pública ────────────────────────────

    private function armarDetallePublico(array $of): array
    {
        $secciones = $this->secciones->deOficina($of['id']);
        foreach ($secciones as &$sec) {
            $sec['enlaces'] = $this->enlaces->publicadosDeSeccion($sec['id']);
        }
        unset($sec);

        return [
            'uuid'              => (string) ($of['uuid'] ?? ''),
            'slug'              => $of['slug'],
            'titulo'            => $of['titulo'],
            'categoria'         => $of['categoria'],
            'excerpt'           => $of['excerpt'],
            'contenido'         => $of['contenido'],
            'contacto_telefono' => $of['contacto_telefono'],
            'contacto_email'    => $of['contacto_email'],
            'contacto_horario'  => $of['contacto_horario'],
            'ubic_edificio'     => $of['ubic_edificio'],
            'ubic_piso'         => $of['ubic_piso'],
            'ubic_referencia'   => $of['ubic_referencia'],
            'ubic_map_url'      => $of['ubic_map_url'],
            'autoridades'       => $this->autoridades->deOficina($of['id']),
            'secciones'         => $secciones,
            'enlaces'           => $this->enlaces->publicadosSueltos($of['id']),
        ];
    }

    // ── Validación / normalización ────────────────────────────────────

    /** Valida el cuerpo de creación/edición de oficina. Devuelve [campos, error]. */
    private function validarOficina(array $data): array
    {
        $titulo = trim((string) ($data['titulo'] ?? ''));
        if ($titulo === '') {
            return [null, 'El título es obligatorio.'];
        }

        $slug = trim((string) ($data['slug'] ?? ''));
        $slug = $slug !== '' ? $this->slugify($slug) : '';
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return [null, 'El slug resultante no es válido.'];
        }

        [$autoridades, $errAut] = $this->parseAutoridades($data['autoridades'] ?? []);
        if ($errAut !== null) {
            return [null, $errAut];
        }

        return [[
            'slug'              => mb_substr($slug, 0, 160),
            'titulo'            => mb_substr($titulo, 0, 200),
            'categoria'         => mb_substr(trim((string) ($data['categoria'] ?? 'Oficina')), 0, 120) ?: 'Oficina',
            'excerpt'           => mb_substr(trim((string) ($data['excerpt'] ?? '')), 0, 500),
            'contenido'         => (string) ($data['contenido'] ?? ''),
            'contacto_telefono' => mb_substr(trim((string) ($data['contacto_telefono'] ?? '')), 0, 60),
            'contacto_email'    => mb_substr(trim((string) ($data['contacto_email'] ?? '')), 0, 160),
            'contacto_horario'  => mb_substr(trim((string) ($data['contacto_horario'] ?? '')), 0, 200),
            'ubic_edificio'     => mb_substr(trim((string) ($data['ubic_edificio'] ?? '')), 0, 200),
            'ubic_piso'         => mb_substr(trim((string) ($data['ubic_piso'] ?? '')), 0, 120),
            'ubic_referencia'   => mb_substr(trim((string) ($data['ubic_referencia'] ?? '')), 0, 300),
            'ubic_map_url'      => mb_substr(trim((string) ($data['ubic_map_url'] ?? '')), 0, 500),
            'orden'             => (int) ($data['orden'] ?? $this->oficinas->siguienteOrden()),
            'publicado'         => filter_var($data['publicado'] ?? true, FILTER_VALIDATE_BOOL) ? 1 : 0,
            'autoridades'       => $autoridades,
        ], null];
    }

    /**
     * Normaliza la lista de autoridades del cuerpo. Ignora entradas sin cargo.
     *
     * @return array{0:list<array{cargo:string,nombre:string}>,1:?string}
     */
    private function parseAutoridades(mixed $raw): array
    {
        if ($raw === '' || $raw === null) {
            return [[], null];
        }
        if (!is_array($raw)) {
            return [[], 'Las autoridades deben ser una lista.'];
        }

        $out = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                return [[], 'Formato de autoridad inválido.'];
            }
            $cargo = trim((string) ($item['cargo'] ?? ''));
            if ($cargo === '') {
                continue;
            }
            $out[] = [
                'cargo'  => mb_substr($cargo, 0, 200),
                'nombre' => mb_substr(trim((string) ($item['nombre'] ?? '')), 0, 200),
            ];
        }

        return [$out, null];
    }

    /** Valida el cuerpo de una sección. Devuelve [campos, error]. */
    private function validarSeccion(array $data): array
    {
        $titulo = trim((string) ($data['titulo'] ?? ''));
        if ($titulo === '') {
            return [null, 'El título de la sección es obligatorio.'];
        }

        $slug = trim((string) ($data['slug'] ?? ''));
        $slug = $slug !== '' ? $this->slugify($slug) : '';
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return [null, 'El slug de la sección no es válido.'];
        }

        $urlExterna = trim((string) ($data['url_externa'] ?? ''));

        return [[
            'slug'        => mb_substr($slug, 0, 160),
            'titulo'      => mb_substr($titulo, 0, 200),
            'descripcion' => mb_substr(trim((string) ($data['descripcion'] ?? '')), 0, 500),
            'icono'       => mb_substr(trim((string) ($data['icono'] ?? 'documents')), 0, 40),
            'url_externa' => $urlExterna === '' ? null : mb_substr($urlExterna, 0, 500),
            'orden'       => 0,
        ], null];
    }

    /** Valida el cuerpo de un enlace ya subido (archivo) o externo (enlace). */
    private function validarEnlace(array $data): array
    {
        $titulo = trim((string) ($data['titulo'] ?? ''));
        if ($titulo === '') {
            return [null, 'El título del enlace es obligatorio.'];
        }

        $tipo = trim((string) ($data['tipo'] ?? 'archivo'));
        if (!in_array($tipo, ['archivo', 'enlace'], true)) {
            return [null, 'El tipo debe ser "archivo" o "enlace".'];
        }

        $fecha = trim((string) ($data['fecha'] ?? ''));
        if ($fecha !== '' && !$this->fechaValida($fecha)) {
            return [null, 'La fecha debe tener el formato AAAA-MM-DD.'];
        }

        $publicado = filter_var($data['publicado'] ?? true, FILTER_VALIDATE_BOOL) ? 1 : 0;

        if ($tipo === 'enlace') {
            $url = trim((string) ($data['url'] ?? ''));
            if ($url === '') {
                return [null, 'La URL del enlace es obligatoria.'];
            }

            return [[
                'titulo'         => mb_substr($titulo, 0, 255),
                'tipo'           => 'enlace',
                'url'            => mb_substr($url, 0, 500),
                'subcarpeta'     => null,
                'nombre_archivo' => null,
                'ext'            => null,
                'tamano'         => 0,
                'fecha'          => $fecha === '' ? null : $fecha,
                'publicado'      => $publicado,
                'managed'        => 0,
            ], null];
        }

        // tipo = archivo
        $nombre = basename(str_replace('\\', '/', (string) ($data['nombre_archivo'] ?? $data['nombre'] ?? '')));
        if ($nombre === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $nombre)) {
            return [null, 'El nombre de archivo no es válido.'];
        }

        $subcarpeta = $this->slugify((string) ($data['subcarpeta'] ?? ''));
        if ($subcarpeta === '') {
            return [null, 'La subcarpeta del archivo no es válida.'];
        }

        $ext = strtolower((string) ($data['ext'] ?? pathinfo($nombre, PATHINFO_EXTENSION)));
        if (!in_array($ext, self::EXT_PERMITIDAS, true)) {
            return [null, 'Extensión no permitida. Solo: ' . implode(', ', self::EXT_PERMITIDAS)];
        }

        $url = trim((string) ($data['url'] ?? ''));
        if ($url === '') {
            return [null, 'La URL pública del archivo es obligatoria.'];
        }

        return [[
            'titulo'         => mb_substr($titulo, 0, 255),
            'tipo'           => 'archivo',
            'url'            => mb_substr($url, 0, 500),
            'subcarpeta'     => mb_substr($subcarpeta, 0, 160),
            'nombre_archivo' => $nombre,
            'ext'            => $ext,
            'tamano'         => max(0, (int) ($data['tamano'] ?? 0)),
            'fecha'          => $fecha === '' ? null : $fecha,
            'publicado'      => $publicado,
            'managed'        => filter_var($data['managed'] ?? true, FILTER_VALIDATE_BOOL) ? 1 : 0,
        ], null];
    }

    /**
     * Resuelve el seccion_id opcional del cuerpo: null si no viene, el id validado
     * si pertenece a la oficina, o false si viene pero no existe.
     *
     * @return int|null|false
     */
    private function resolverSeccionId(array $data, int $oficinaId)
    {
        $raw = $data['seccion_id'] ?? null;
        if ($raw === null || $raw === '' || (int) $raw === 0) {
            return null;
        }
        $seccionId = (int) $raw;

        return $this->secciones->porId($seccionId, $oficinaId) === null ? false : $seccionId;
    }

    private function fechaValida(string $fecha): bool
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $m)) {
            return false;
        }

        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }

    private function resolverOficina(string $ref): ?array
    {
        return $this->oficinas->porReferencia($ref) ?? $this->oficinas->porSlug($ref);
    }

    private function resolverOficinaId(string $ref): ?int
    {
        $id = $this->oficinas->idPorReferencia($ref);
        if ($id !== null && $id > 0) {
            return $id;
        }

        return $this->oficinas->idPorSlug($ref);
    }

    private function esUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }

    /** UUID v4 para usar identificadores internos estables. */
    private function generarUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /** Convierte un texto a slug (minúsculas, sin tildes, separado por guiones). */
    private function slugify(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = strtr($s, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'ñ' => 'n', 'ç' => 'c',
        ]);
        $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';

        return trim($s, '-');
    }

    /**
     * Reenvía el borrado físico de una lista de archivos managed.
     * Devuelve los nombres que no pudieron borrarse.
     *
     * @param list<array{subcarpeta:string,nombre_archivo:string}> $archivos
     * @return list<string>
     */
    private function relayArchivos(array $archivos, string $auth): array
    {
        $noBorrados = [];
        foreach ($archivos as $a) {
            $sub    = (string) ($a['subcarpeta'] ?? '');
            $nombre = (string) ($a['nombre_archivo'] ?? '');
            if ($sub === '' || $nombre === '') {
                continue;
            }
            if (!($this->relayDelete)($sub, $nombre, $auth)) {
                $noBorrados[] = $nombre;
            }
        }

        return $noBorrados;
    }

    /** @param list<string> $noBorrados */
    private function conAdvertencia(array $noBorrados): array
    {
        $payload = ['ok' => true];
        if ($noBorrados !== []) {
            $payload['advertencia'] = 'Algunos archivos físicos no pudieron eliminarse.';
            $payload['no_borrados'] = $noBorrados;
        }

        return $payload;
    }

    /** Implementación HTTP real del relay (curl). Aislada para inyectarse en tests. */
    private function relayBorradoHttp(string $subcarpeta, string $nombre, string $auth): bool
    {
        $base = rtrim((string) ($_ENV['FILES_API_BASE_URL'] ?? ''), '/');
        if ($base === '' || !function_exists('curl_init')) {
            return false;
        }

        // @codeCoverageIgnoreStart
        $headers = ['Content-Type: application/json'];
        if ($auth !== '') {
            $headers[] = 'Authorization: ' . $auth;
        }

        $ch = curl_init($base . '/oficinas/delete');
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_POSTFIELDS     => json_encode(['slug' => $subcarpeta, 'nombre' => $nombre]),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $code >= 200 && $code < 300;
        // @codeCoverageIgnoreEnd
    }
}
