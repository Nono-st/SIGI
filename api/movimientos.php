<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/config/sesion.php";

// RESPONDER SOLICITUDES
function responder(int $codigo, array $datos): never
{
    http_response_code($codigo);
    echo json_encode($datos);
    exit;
}

// VERIFICAR SESIÓN
if (!isset($_SESSION["usuario"])) {
    responder(401, [
        "error" => "No hay una sesión activa."
    ]);
}

try {
    $db = require __DIR__ . "/config/database.php";
    $metodo = $_SERVER["REQUEST_METHOD"];

    // CONSULTAR MOVIMIENTOS
    if ($metodo === "GET") {

        $consulta = $db->query("
            SELECT
                movimientos.id,
                movimientos.productos_id,
                productos.nombre AS producto,
                movimientos.usuario_id,
                usuarios.nombre AS usuario,
                movimientos.tipo,
                movimientos.cantidad,
                movimientos.fecha
            FROM movimientos
            INNER JOIN productos
                ON movimientos.productos_id = productos.id
            INNER JOIN usuarios
                ON movimientos.usuario_id = usuarios.id
            ORDER BY movimientos.fecha DESC, movimientos.id DESC
        ");

        responder(200, [
            "movimientos" => $consulta->fetchAll()
        ]);
    }

    // REGISTRAR MOVIMIENTO
    if ($metodo === "POST") {

        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            responder(400, [
                "error" => "Datos inválidos."
            ]);
        }

        $productoId = filter_var(
            $datos["productos_id"] ?? null,
            FILTER_VALIDATE_INT
        );

        $tipo = $datos["tipo"] ?? "";
        $cantidad = filter_var(
            $datos["cantidad"] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $productoId === false ||
            $productoId === null ||
            $productoId <= 0 ||
            !in_array($tipo, ["entrada", "salida"], true) ||
            $cantidad === false ||
            $cantidad === null ||
            $cantidad <= 0
        ) {
            responder(400, [
                "error" => "Revisá los datos del movimiento."
            ]);
        }

        // BUSCAR PRODUCTO
        $consulta = $db->prepare("
            SELECT id, nombre, stock
            FROM productos
            WHERE id = :id
        ");

        $consulta->execute([
            ":id" => $productoId
        ]);

        $producto = $consulta->fetch();

        if (!$producto) {
            responder(404, [
                "error" => "El producto no existe."
            ]);
        }

        // COMPROBAR STOCK DISPONIBLE
        if (
            $tipo === "salida" &&
            (int) $producto["stock"] < $cantidad
        ) {
            responder(409, [
                "error" => "No hay suficiente stock disponible."
            ]);
        }

        // INICIAR TRANSACCIÓN
        $db->beginTransaction();

        if ($tipo === "entrada") {

            $consulta = $db->prepare("
                UPDATE productos
                SET stock = stock + :cantidad
                WHERE id = :id
            ");

        } else {

            $consulta = $db->prepare("
                UPDATE productos
                SET stock = stock - :cantidad
                WHERE id = :id
                AND stock >= :cantidad
            ");
        }

        $consulta->execute([
            ":cantidad" => $cantidad,
            ":id" => $productoId
        ]);

        if ($consulta->rowCount() === 0) {
            $db->rollBack();

            responder(409, [
                "error" => "No se pudo actualizar el stock disponible."
            ]);
        }

        // REGISTRAR MOVIMIENTO
        $consulta = $db->prepare("
            INSERT INTO movimientos
                (productos_id, usuario_id, tipo, cantidad)
            VALUES
                (:productos_id, :usuario_id, :tipo, :cantidad)
        ");

        $consulta->execute([
            ":productos_id" => $productoId,
            ":usuario_id" => (int) $_SESSION["usuario"]["id"],
            ":tipo" => $tipo,
            ":cantidad" => $cantidad
        ]);

        $movimientoId = $db->lastInsertId();

        // CONFIRMAR TRANSACCIÓN
        $db->commit();

        responder(201, [
            "mensaje" => "Movimiento registrado correctamente.",
            "id" => (int) $movimientoId
        ]);
    }

    // MÉTODO NO PERMITIDO
    header("Allow: GET, POST");

    responder(405, [
        "error" => "Método no permitido."
    ]);

} catch (Throwable $e) {

    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log("ERROR en movimientos.php: " . $e->getMessage());

    responder(500, [
        "error" => "No se pudo procesar la solicitud."
    ]);
}