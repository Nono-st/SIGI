<?php

header("Content-Type: application/json; charset=UTF-8");

try {
	//se importa la conexion a la base de datos.
	$db= require __DIR__ . "/config/database.php";

	// por ahora, este endpoint solamente pertmite consultas GET.
	if($_SERVER["REQUEST_METHOD"] !== "GET" ){
		http_response_code(405);

		echo json_encode([
			"error" => "Metodo no permitido."
		]);
		exit;
	}

	// Consultamos los productos y sus categorias.

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
	//Obtenemos todos los resultados.
	$productos = $consulta->fetchAll();

	//Devolvemos los productos en formato JSON.
	echo json_encode($productos);

     }catch (Throwable $e){
	error_log("ERROR en productos.php" . $e->getMessage());

	http_response_code(500);

	echo json_encode([
		"error" => "No se pudieron obtener los productos."
	]);
}
