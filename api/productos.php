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

    // CONSULTAR PRODUCTOS
    if ($metodo === "GET") {
        $consulta = $db->query("
            SELECT
                productos.id,
                productos.nombre,
                productos.precio,
                productos.stock,
                productos.stock_minimo,
                productos.categoria_id,
                categorias.nombre AS categoria
            FROM productos
            INNER JOIN categorias
                ON productos.categoria_id = categorias.id
            ORDER BY productos.id ASC
        ");

        echo json_encode($consulta->fetchAll());
        exit;
    }

    // REGISTRAR PRODUCTO
    if ($metodo === "POST") {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            http_response_code(400);
            echo json_encode(["error" => "Datos inválidos."]);
            exit;
        }

        $nombre = trim($datos["nombre"] ?? "");
        $precio = $datos["precio"] ?? null;
        $stock = 0;
        $stockMinimo = $datos["stock_minimo"] ?? null;
        $categoriaId = $datos["categoria_id"] ?? null;

        if (!validarProducto($nombre, $precio, $stock, $stockMinimo, $categoriaId)) {
            http_response_code(400);
            echo json_encode(["error" => "Revisá los valores ingresados."]);
            exit;
        }

        if (!existeCategoria($db, (int) $categoriaId)) {
            http_response_code(400);
            echo json_encode(["error" => "La categoría seleccionada no existe."]);
            exit;
        }

        $consulta = $db->prepare("
            INSERT INTO productos
                (nombre, precio, stock, stock_minimo, categoria_id)
            VALUES
                (:nombre, :precio, :stock, :stock_minimo, :categoria_id)
        ");

        $consulta->execute([
            ":nombre" => $nombre,
            ":precio" => (float) $precio,
            ":stock" => (int) $stock,
            ":stock_minimo" => (int) $stockMinimo,
            ":categoria_id" => (int) $categoriaId
        ]);

        $id = $db->lastInsertId();

        http_response_code(201);
        echo json_encode(obtenerProducto($db, $id));
        exit;
    }

    // MODIFICAR PRODUCTO
    if ($metodo === "PUT") {
        $id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "ID de producto inválido."]);
            exit;
        }

        $datos = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datos)) {
            http_response_code(400);
            echo json_encode(["error" => "Datos inválidos."]);
            exit;
        }

        $nombre = trim($datos["nombre"] ?? "");
        $precio = $datos["precio"] ?? null;
        $stockMinimo = $datos["stock_minimo"] ?? null;
        $categoriaId = $datos["categoria_id"] ?? null;

        $productoActual = obtenerProducto($db, $id);

        if (!$productoActual) {
            http_response_code(404);
            echo json_encode([
                "error" => "El producto no existe."]);
            exit;
}

$stock = (int) $productoActual["stock"];

        if (!validarProducto($nombre, $precio, $stock, $stockMinimo, $categoriaId)) {
            http_response_code(400);
            echo json_encode(["error" => "Revisá los valores ingresados."]);
            exit;
        }

        if (!existeCategoria($db, (int) $categoriaId)) {
            http_response_code(400);
            echo json_encode(["error" => "La categoría seleccionada no existe."]);
            exit;
        }

        $consulta = $db->prepare("
            UPDATE productos
            SET
                nombre = :nombre,
                precio = :precio,
                stock_minimo = :stock_minimo,
                categoria_id =
                :categoria_id
            WHERE id = :id
        ");

        $consulta->execute([
            ":nombre" => $nombre,
            ":precio" => (float) $precio,
            ":stock_minimo" => (int) $stockMinimo,
            ":categoria_id" => (int) $categoriaId,
            ":id" => $id
        ]);

        if ($consulta->rowCount() === 0 && !obtenerProducto($db, $id)) {
            http_response_code(404);
            echo json_encode(["error" => "El producto no existe."]);
            exit;
        }

        echo json_encode(obtenerProducto($db, $id));
        exit;
    }

    // ELIMINAR PRODUCTO
    if ($metodo === "DELETE") {
        $id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "ID de producto inválido."]);
            exit;
        }

        $producto = obtenerProducto($db, $id);

        if (!$producto) {
            http_response_code(404);
            echo json_encode(["error" => "El producto no existe."]);
            exit;
        }

        $consulta = $db->prepare("
            SELECT COUNT(*)
            FROM movimientos
            WHERE productos_id = :id
        ");

        $consulta->execute([":id" => $id]);

        if ((int) $consulta->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode([
                "error" => "No se puede eliminar este producto porque tiene movimientos registrados."
            ]);
            exit;
        }

        $consulta = $db->prepare("
            DELETE FROM productos
            WHERE id = :id
        ");

        $consulta->execute([":id" => $id]);

        echo json_encode([
            "mensaje" => "Producto eliminado correctamente.",
            "id" => $id
        ]);
        exit;
    }

    // MÉTODO NO PERMITIDO
    header("Allow: GET, POST, PUT, DELETE");
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido."]);

} catch (Throwable $e) {
    error_log("ERROR en productos.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "error" => "No se pudo procesar la solicitud."
    ]);
}


// VALIDAR DATOS DEL PRODUCTO
function validarProducto($nombre, $precio, $stock, $stockMinimo, $categoriaId): bool
{
    return (
        $nombre !== "" &&
        is_numeric($precio) &&
        is_numeric($stock) &&
        is_numeric($stockMinimo) &&
        filter_var($categoriaId, FILTER_VALIDATE_INT) !== false &&
        is_finite((float) $precio) &&
        is_finite((float) $stock) &&
        is_finite((float) $stockMinimo) &&
        (float) $precio >= 0 &&
        (float) $stock >= 0 &&
        (float) $stockMinimo >= 0 &&
        (int) $stock == (float) $stock &&
        (int) $stockMinimo == (float) $stockMinimo &&
        (int) $categoriaId > 0
    );
}


// COMPROBAR QUE EXISTA LA CATEGORÍA
function existeCategoria(PDO $db, int $id): bool
{
    $consulta = $db->prepare("
        SELECT id
        FROM categorias
        WHERE id = :id
    ");

    $consulta->execute([":id" => $id]);

    return (bool) $consulta->fetch();
}


// OBTENER UN PRODUCTO POR SU ID
function obtenerProducto(PDO $db, int $id): array|false
{
    $consulta = $db->prepare("
        SELECT
            productos.id,
            productos.nombre,
            productos.precio,
            productos.stock,
            productos.stock_minimo,
            productos.categoria_id,
            categorias.nombre AS categoria
        FROM productos
        INNER JOIN categorias
            ON productos.categoria_id = categorias.id
        WHERE productos.id = :id
    ");

    $consulta->execute([":id" => $id]);

    return $consulta->fetch();
}
