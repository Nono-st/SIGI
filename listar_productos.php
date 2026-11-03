<?php

require_once __DIR__ . "/database.php";

try{
	$sql = "SELECT  productos.id,
			productos.nombre,
			productos.precio,
			productos.stock,
			productos.stock_minimo,
			categorias.nombre AS categoria
		FROM productos
		INNER JOIN categorias ON
		productos.categoria_id = categorias.id";

	$consulta = $db->query($sql);

	$productos = $consulta->fetchAll(PDO::FETCH_ASSOC);


	foreach ($productos as $producto ) {

		echo "ID: " . $producto["id"] . "\n";
		echo "Nombre: ". $producto["nombre"] . "\n";
		echo "Precio: $ " . $producto["precio"] . "\n";
		echo "Stock: " . $producto["stock"] . "\n";
		echo "Stock minimo: " . $producto ["stock_minimo"] . "\n";
		echo "Categoria: " . $producto ["categoria"] . "\n";
		echo "---------------------------------\n";
	}



   } catch (PDOException $e) {
		die("Error all listar productos: " . $e->getMessage() . "\n");

}
