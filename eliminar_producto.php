<?php

require_once __DIR__ ."/database.php";

echo "=== ELIMINAR PRODUCTO ===\n";


echo "ingrese el ID del producto: ";
$id = trim(fgets(STDIN));

try {
	$sql = "SELECT * FROM productos WHERE id = :id";

	$consulta = $db->prepare($sql);

	$consulta->execute([
		":id" => $id
	]);


	$producto = $consulta->fetch(PDO::FETCH_ASSOC);

	if(!$producto) {
		echo "No existe un producto con ese ID.\n";
		exit;

	 }

	echo "\nProducto encontrado:\n";
	echo "Nombre: " . $producto["nombre"] . "\n";
	echo "Precio: $" . $producto["precio"]. "\n";
	echo "Stock: " . $producto["stock"]. "\n";
	echo "Stock minimo: " . $producto["stock_minimo"]. "\n";



	echo "\n¿Esta seguro de que desea eleminar este producoto? (s/n): ";
	$confirmacion = strtolower(trim(fgets(STDIN)));

	if($confirmacion != "s"){
		echo "Eleminacion cancelada.\n";
		exit;
	}

	$sql = "DELETE FROM productos WHERE id = :id ";

	$consulta = $db->prepare($sql);

	$consulta->execute([

		":id" =>$id
	]);

	echo "Producto eliminado correctamente.\n";


    } catch (PDOException $e){
		die("Eror al buscar producto: ". $e->getMessage() . "\n");
	}
