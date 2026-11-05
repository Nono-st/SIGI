<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/sesion.php';

function responder(int $codigo, array $datos): never
{
    http_response_code($codigo);
    echo json_encode($datos);
    exit;
}

function obtenerDatos(): array
{
    $datos = json_decode(file_get_contents('php://input'), true);

    if (!is_array($datos)) {
        responder(400, ['error' => 'Los datos enviados no son válidos.']);
    }

    return $datos;
}

function validarRol(string $rol): bool
{
    return in_array($rol, ['administrador', 'empleado'], true);
}

// Verificar que exista una sesión.
if (!isset($_SESSION['usuario'])) {
    responder(401, ['error' => 'Debés iniciar sesión.']);
}

// Verificar permisos de administrador.
if ($_SESSION['usuario']['rol'] !== 'administrador') {
    responder(403, ['error' => 'No tenés permisos para gestionar usuarios.']);
}

$metodo = $_SERVER['REQUEST_METHOD'];

try {
    $db = require __DIR__ . '/config/database.php';

    // LISTAR USUARIOS
    if ($metodo === 'GET') {
        $consulta = $db->query("
            SELECT id, nombre, usuario, rol
            FROM usuarios
            ORDER BY nombre ASC
        ");

        responder(200, [
            'usuarios' => $consulta->fetchAll()
        ]);
    }

    // CREAR USUARIO
    if ($metodo === 'POST') {
        $datos = obtenerDatos();

        $nombre = trim($datos['nombre'] ?? '');
        $usuario = trim($datos['usuario'] ?? '');
        $password = $datos['password'] ?? '';
        $rol = trim($datos['rol'] ?? '');

        if ($nombre === '' || $usuario === '' || $password === '' || $rol === '') {
            responder(422, ['error' => 'Todos los campos son obligatorios.']);
        }

        if (strlen($password) < 8) {
            responder(422, ['error' => 'La contraseña debe tener al menos 8 caracteres.']);
        }

        if (!validarRol($rol)) {
            responder(422, ['error' => 'El rol debe ser administrador o empleado.']);
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $consulta = $db->prepare("
            INSERT INTO usuarios (nombre, usuario, password, rol)
            VALUES (:nombre, :usuario, :password, :rol)
        ");

        $consulta->execute([
            ':nombre' => $nombre,
            ':usuario' => $usuario,
            ':password' => $passwordHash,
            ':rol' => $rol
        ]);

        responder(201, [
            'mensaje' => 'Usuario creado correctamente.',
            'id' => (int) $db->lastInsertId()
        ]);
    }

    // MODIFICAR USUARIO
    if ($metodo === 'PUT') {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            responder(400, ['error' => 'El ID del usuario no es válido.']);
        }

        $datos = obtenerDatos();

        $consulta = $db->prepare("
            SELECT id, nombre, usuario, rol
            FROM usuarios
            WHERE id = :id
        ");
        $consulta->execute([':id' => $id]);

        $usuarioActual = $consulta->fetch();

        if (!$usuarioActual) {
            responder(404, ['error' => 'El usuario no existe.']);
        }

        $nombre = array_key_exists('nombre', $datos)
            ? trim($datos['nombre'])
            : $usuarioActual['nombre'];

        $nombreUsuario = array_key_exists('usuario', $datos)
            ? trim($datos['usuario'])
            : $usuarioActual['usuario'];

        $rol = array_key_exists('rol', $datos)
            ? trim($datos['rol'])
            : $usuarioActual['rol'];

        if ($nombre === '' || $nombreUsuario === '') {
            responder(422, ['error' => 'El nombre y el usuario no pueden estar vacíos.']);
        }

        if (!validarRol($rol)) {
            responder(422, ['error' => 'El rol debe ser administrador o empleado.']);
        }

        // Un usuario no puede cambiar su propio rol.
        if (
            (int) $id === (int) $_SESSION['usuario']['id']
            && $rol !== $usuarioActual['rol']
        ) {
            responder(403, ['error' => 'No podés modificar tu propio rol.']);
        }

        // No permitir eliminar el último administrador mediante un cambio de rol.
        if (
            $usuarioActual['rol'] === 'administrador'
            && $rol !== 'administrador'
        ) {
            $consulta = $db->query("
                SELECT COUNT(*) FROM usuarios
                WHERE rol = 'administrador'
            ");

            if ((int) $consulta->fetchColumn() <= 1) {
                responder(409, ['error' => 'No se puede quitar el rol al último administrador.']);
            }
        }

        $campos = [
            'nombre' => $nombre,
            'usuario' => $nombreUsuario,
            'rol' => $rol
        ];

        $password = $datos['password'] ?? null;

        if ($password !== null && $password !== '') {
            if (!is_string($password) || strlen($password) < 8) {
                responder(422, ['error' => 'La contraseña debe tener al menos 8 caracteres.']);
            }

            $campos['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $asignaciones = [];

        foreach ($campos as $campo => $valor) {
            $asignaciones[] = "$campo = :$campo";
        }

        $sql = "
            UPDATE usuarios
            SET " . implode(', ', $asignaciones) . "
            WHERE id = :id
        ";

        $consulta = $db->prepare($sql);

        $campos['id'] = $id;
        $consulta->execute($campos);

        responder(200, [
            'mensaje' => 'Usuario actualizado correctamente.'
        ]);
    }

    // ELIMINAR USUARIO
    if ($metodo === 'DELETE') {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            responder(400, ['error' => 'El ID del usuario no es válido.']);
        }

        // Impedir que el administrador elimine su propia cuenta.
        if ((int) $id === (int) $_SESSION['usuario']['id']) {
            responder(403, ['error' => 'No podés eliminar tu propia cuenta.']);
        }

        $db->beginTransaction();

        $consulta = $db->prepare("
            SELECT id, rol
            FROM usuarios
            WHERE id = :id
        ");
        $consulta->execute([':id' => $id]);

        $usuarioEliminar = $consulta->fetch();

        if (!$usuarioEliminar) {
            $db->rollBack();
            responder(404, ['error' => 'El usuario no existe.']);
        }

        if ($usuarioEliminar['rol'] === 'administrador') {
            $consulta = $db->query("
                SELECT COUNT(*) FROM usuarios
                WHERE rol = 'administrador'
            ");

            if ((int) $consulta->fetchColumn() <= 1) {
                $db->rollBack();

                responder(409, [
                    'error' => 'No se puede eliminar al último administrador.'
                ]);
            }
        }

        $consulta = $db->prepare("
            DELETE FROM usuarios
            WHERE id = :id
        ");
        $consulta->execute([':id' => $id]);

        $db->commit();

        responder(200, [
            'mensaje' => 'Usuario eliminado correctamente.'
        ]);
    }

    header('Allow: GET, POST, PUT, DELETE');

    responder(405, ['error' => 'Método no permitido.']);

} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    // SQLite: código 19 = violación de una restricción.
    if ($e->getCode() === '23000' || (int) $e->getCode() === 19) {
        responder(409, ['error' => 'Ya existe un usuario con ese nombre de usuario.']);
    }

    error_log($e->getMessage());

    responder(500, ['error' => 'Ocurrió un error al gestionar los usuarios.']);

} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log($e->getMessage());

    responder(500, ['error' => 'Ocurrió un error interno.']);
}
