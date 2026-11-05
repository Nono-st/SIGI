<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/sesion.php';

function responder(int $codigo, array $datos): never
{
    http_response_code($codigo);
    echo json_encode($datos);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    if (!isset($_SESSION['usuario'])) {
        responder(401, [
            'error' => 'No hay una sesión activa.'
        ]);
    }

    responder(200, [
        'usuario' => $_SESSION['usuario']
    ]);
}

if ($metodo === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);

    if (!is_array($datos)) {
        responder(400, ['error' => 'Datos inválidos.']);
    }

    $usuario = trim($datos['usuario'] ?? '');
    $password = $datos['password'] ?? '';

    if ($usuario === '' || $password === '') {
        responder(400, [
            'error' => 'Completá usuario y contraseña.'
        ]);
    }

    try {
        $db = require __DIR__ . '/config/database.php';

        $consulta = $db->prepare("
            SELECT id, nombre, usuario, password, rol
            FROM usuarios
            WHERE usuario = :usuario
        ");

        $consulta->execute([':usuario' => $usuario]);

        $registro = $consulta->fetch();

        if (!$registro || !password_verify($password, $registro['password'])) {
            responder(401, [
                'error' => 'Usuario o contraseña incorrectos.'
            ]);
        }

        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id' => (int) $registro['id'],
            'nombre' => $registro['nombre'],
            'usuario' => $registro['usuario'],
            'rol' => $registro['rol']
        ];

        responder(200, [
            'mensaje' => 'Inicio de sesión correcto.',
            'usuario' => $_SESSION['usuario']
        ]);

    } catch (Throwable $e) {
        error_log($e->getMessage());

        responder(500, [
            'error' => 'No se pudo iniciar sesión.'
        ]);
    }
}

if ($metodo === 'DELETE') {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros['path'],
            $parametros['domain'],
            $parametros['secure'],
            $parametros['httponly']
        );
    }

    session_destroy();

    responder(200, [
        'mensaje' => 'Sesión cerrada correctamente.'
    ]);
}

header('Allow: GET, POST, DELETE');

responder(405, [
    'error' => 'Método no permitido.'
]);
