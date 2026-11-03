<?php

require_once __DIR__ . "/database.php";

echo "=== MODIFICAR PRODUCTO === \n";

echo "Ingresse el id del producto: ";
$id = trim(fgets(STDIN));

try { 

	$sql = "SELECT * FROM productos WHERE id = :id";

	$consultas = $db->prepare($sql);

	$consultas->execute([
		":id" => $id
	]);

	$producto = $consultas->fetch(PDO::FETCH_ASSOC);

	if(!$producto) {
		echo"No existe un producto con ese ID\n";
		exit;
	}

	echo"\nProducto encontrado: \n";
	echo "Nombre: " . $producto["nombre"] . "\n";
	echo "Precio: " . $producto ["precio"] . "\n";
	echo "Stock: " . $producto ["stock"] . "\n";
	echo "Stock minimo: " . $producto["stock_minimo"] . "\n";

	echo "\n=== INGRESAR NUEVOS DATOS ===\n";

	echo "Nuevo nombre: ";
	$nuevoNombre = trim(fgets(STDIN));

	echo "Nuevo precio: ";
	$nuevoPrecio = trim(fgets(STDIN));

	echo "Nuevo stock: ";
	$nuevoStock = trim(fgets(STDIN));

	echo "Nuevo stock minimo: ";
	$nuevoStockMinimo = trim(fgets(STDIN));


	$sql = "UPDATE productos
		SET nombre = :nombre,
		    precio = :precio,
		    stock = :stock,
		    stock_minimo = :stock_minimo
		WHERE id= :id";


	$consulta = $db->prepare($sql);

	$consulta->execute([
		":nombre" => $nuevoNombre,
		":precio" => $nuevoPrecio,
		":stock" => $nuevoStock,
		":stock_minimo" => $nuevoStockMinimo,
		":id" => $id
	]);

	echo "\nProducto modificado correctamente.\n";

    } catch (PDDOException $e) {

		die("Error al buscar producto: " . $e->getmessage(). "\n");
	}
