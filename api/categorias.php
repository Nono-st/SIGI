<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/config/sesion.php";

function responder(int $codigo, array $datos): never
{
    http_response_code($codigo);
    echo json_encode($datos);
    exit;
}

// verificar que exista una sesion

if(!isset($_SESSION["usuario"])){
    responder(401, ["error" => " debes iniciar sesion"]);
}

try {
    $db = require __DIR__ . "/config/database.php";
    $metodo = $_SERVER["REQUEST_METHOD"];

    // CONSULTAR CATEGORIAS
    if ($metodo === "GET") {
        $consulta = $db->query("
            SELECT id, nombre, descripcion
            FROM categorias
            ORDER BY nombre ASC
        ");

        echo json_encode($consulta->fetchAll());
        exit;
    }

    // REGISTRAR CATEGORIA
    if ($metodo === "POST") {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            responder(400, ["error" => "Datos inválidos."]);
        }

        $nombre = trim($datos["nombre"] ?? "");
        $descripcion = trim($datos["descripcion"] ?? "");

        if ($nombre === "") {
            responder(400, ["error" => "El nombre es obligatorio."]);
        }

        $consulta = $db->prepare("
            INSERT INTO categorias (nombre, descripcion)
            VALUES (:nombre, :descripcion)
        ");

        $consulta->execute([
            ":nombre" => $nombre,
            ":descripcion" => $descripcion !== "" ? $descripcion : null
        ]);

        $id = (int) $db->lastInsertId();

        responder(201, obtenerCategoria($db, $id));
    }

    // MODIFICAR CATEGORIA
    if ($metodo === "PUT") {
        $id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {
            responder(400, ["error" => "ID de categoría inválido."]);
        }

        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            responder(400, ["error" => "Datos inválidos."]);
        }

        $nombre = trim($datos["nombre"] ?? "");
        $descripcion = trim($datos["descripcion"] ?? "");

        if ($nombre === "") {
            responder(400, ["error" => "El nombre es obligatorio."]);
        }

        if (!obtenerCategoria($db, $id)) {
            responder(404, ["error" => "La categoría no existe."]);
        }

        $consulta = $db->prepare("
            UPDATE categorias
            SET nombre = :nombre,
                descripcion = :descripcion
            WHERE id = :id
        ");

        $consulta->execute([
            ":nombre" => $nombre,
            ":descripcion" => $descripcion !== "" ? $descripcion : null,
            ":id" => $id
        ]);

        responder(200, obtenerCategoria($db, $id));
    }

    // ELIMINAR CATEGORIA
    if ($metodo === "DELETE") {
        $id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {
            responder(400, ["error" => "ID de categoría inválido."]);
        }

        if (!obtenerCategoria($db, $id)) {
            responder(404, ["error" => "La categoría no existe."]);
        }

        $consulta = $db->prepare("
            SELECT COUNT(*)
            FROM productos
            WHERE categoria_id = :id
        ");

        $consulta->execute([":id" => $id]);

        if ((int) $consulta->fetchColumn() > 0) {
            responder(409, [
                "error" => "No se puede eliminar esta categoría porque tiene productos asociados."
            ]);
        }

        $consulta = $db->prepare("
            DELETE FROM categorias
            WHERE id = :id
        ");

        $consulta->execute([":id" => $id]);

        responder(200, [
            "mensaje" => "Categoría eliminada correctamente.",
            "id" => $id
        ]);
    }

    header("Allow: GET, POST, PUT, DELETE");
    responder(405, ["error" => "Método no permitido."]);

} catch (PDOException $e) {
    error_log("ERROR en categorias.php: " . $e->getMessage());

    if ($e->getCode() === "23000" || str_contains($e->getMessage(), "UNIQUE constraint failed")) {
        responder(409, ["error" => "Ya existe una categoría con ese nombre."]);
    }

    responder(500, ["error" => "No se pudo procesar la solicitud."]);

} catch (Throwable $e) {
    error_log("ERROR en categorias.php: " . $e->getMessage());

    responder(500, ["error" => "No se pudo procesar la solicitud."]);
}


// OBTENER CATEGORIA POR ID
function obtenerCategoria(PDO $db, int $id): array|false
{
    $consulta = $db->prepare("
        SELECT id, nombre, descripcion
        FROM categorias
        WHERE id = :id
    ");

    $consulta->execute([":id" => $id]);

    return $consulta->fetch();
}
