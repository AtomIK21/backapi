<?php
// Habilitación de errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Parsear URI y segmentos
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$segments = array_values(array_filter(explode('/', trim($path, '/'))));

// ----------------------
// 1) LOGIN (POST /login)
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($segments[1]) && $segments[1] === 'login'
) {
    $datos = json_decode(file_get_contents('php://input'), true);
    $email = $datos['email'] ?? '';
    $pass  = $datos['llave_secreta'] ?? '';

    $cliente = ModeloClientes::buscarPorEmail('clientes', $email);
    if (!$cliente || !password_verify($pass, $cliente['llave_secreta'])) {
        http_response_code(401);
        echo json_encode(['status' => 401, 'detalle' => 'Credenciales inválidas']);
        exit;
    }

    // Guardar en sesión
    $_SESSION['cliente_id']   = $cliente['id'];
    $_SESSION['cliente_nombre'] = $cliente['nombre'];

    echo json_encode([
        'status'  => 200,
        'usuario' => [
            'id'     => $cliente['id'],
            'nombre' => $cliente['nombre']
        ]
    ]);
    exit;
}

// -------------------------------------------------
// 2) RUTA PÚBLICA: GET /clientes/cursos
// -------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET'
    && isset($segments[1]) && $segments[1] === 'clientes'
    && isset($segments[2]) && $segments[2] === 'cursos'
) {
    // Intentar sesión primero
    $cliente_id = $_SESSION['cliente_id'] ?? null;

    // Si no viene en sesión, tomar param cliente_id
    if (!$cliente_id && isset($_GET['cliente_id']) && is_numeric($_GET['cliente_id'])) {
        $cliente_id = (int) $_GET['cliente_id'];
    }

    if (!$cliente_id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de cliente no proporcionado']);
        exit;
    }

    // Llamar al controlador
    $ctrl = new ControladorClientes();
    $ctrl->cursosDelCliente($cliente_id);
    exit;
}

// -----------------------------------------------
// 3) RUTA PÚBLICA: POST /clientes  (registro)
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($segments[1]) && $segments[1] === 'clientes'
) {
    $datos = json_decode(file_get_contents('php://input'), true);
    $ctrl  = new ControladorClientes();
    $ctrl->create($datos);
    exit;
}

// -------------------------------------------------
// 4) PROTECCIÓN POR SESIÓN: todas las demás rutas
// -------------------------------------------------
if (empty($_SESSION['cliente_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 401, 'detalle' => 'No autenticado']);
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// -------------------------------------------------
// 5) GET /?pagina=1  (listado paginado de cursos)
// -------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET'
    && isset($_GET['pagina']) && is_numeric($_GET['pagina'])
) {
    $ctrl = new ControladorCursos();
    $ctrl->index((int) $_GET['pagina']);
    exit;
}

// -------------------------------------------------
// 6) CRUD /cursos
// -------------------------------------------------
// POST   /cursos
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($segments[1]) && $segments[1] === 'cursos'
) {
    $datos = json_decode(file_get_contents('php://input'), true);
    $ctrl  = new ControladorCursos();
    $ctrl->create($datos, $cliente_id);
    exit;
}

// PUT    /cursos/{id}
if ($_SERVER['REQUEST_METHOD'] === 'PUT'
    && isset($segments[1], $segments[2]) && $segments[1] === 'cursos'
) {
    $id    = (int) $segments[2];
    $datos = json_decode(file_get_contents('php://input'), true);
    $ctrl  = new ControladorCursos();
    $ctrl->update($id, $datos, $cliente_id);
    exit;
}

// DELETE /cursos/{id}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE'
    && isset($segments[1], $segments[2]) && $segments[1] === 'cursos'
) {
    $id   = (int) $segments[2];
    $ctrl = new ControladorCursos();
    $ctrl->delete($id, $cliente_id);
    exit;
}

// GET    /cursos/{id}
if ($_SERVER['REQUEST_METHOD'] === 'GET'
    && isset($segments[1], $segments[2]) && $segments[1] === 'cursos'
) {
    $id   = (int) $segments[2]; 
    $ctrl = new ControladorCursos();
    $ctrl->show($id);
    exit;
}

// -------------------------------------------------
// 7) CRUD /ordenes
// -------------------------------------------------
// GET    /ordenes
if ($_SERVER['REQUEST_METHOD'] === 'GET'
    && isset($segments[1]) && $segments[1] === 'ordenes'
    && !isset($segments[2])
) {
    $ctrl = new ControladorOrdenes();
    $ctrl->index($cliente_id);
    exit;
}

// GET    /ordenes/{id}
if ($_SERVER['REQUEST_METHOD'] === 'GET'
    && isset($segments[1], $segments[2]) && $segments[1] === 'ordenes'
) {
    $id   = (int) $segments[2];
    $ctrl = new ControladorOrdenes();
    $ctrl->show($id, $cliente_id);
    exit;
}

// POST   /ordenes
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($segments[1]) && $segments[1] === 'ordenes'
) {
    $datos = json_decode(file_get_contents('php://input'), true);
    $ctrl  = new ControladorOrdenes();
    $ctrl->create($datos, $cliente_id);
    exit;
}

// PUT    /ordenes/{id}
if ($_SERVER['REQUEST_METHOD'] === 'PUT'
    && isset($segments[1], $segments[2]) && $segments[1] === 'ordenes'
) {
    $id    = (int) $segments[2];
    $datos = json_decode(file_get_contents('php://input'), true);
    $ctrl  = new ControladorOrdenes();
    $ctrl->update($id, $datos, $cliente_id);
    exit;
}

// DELETE /ordenes/{id}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE'
    && isset($segments[1], $segments[2]) && $segments[1] === 'ordenes'
) {
    $id   = (int) $segments[2];
    $ctrl = new ControladorOrdenes();
    $ctrl->delete($id, $cliente_id);
    exit;
}

// Si no coincide ninguna ruta:
http_response_code(404);
echo json_encode(['status' => 404, 'detalle' => 'Ruta no encontrada']);
