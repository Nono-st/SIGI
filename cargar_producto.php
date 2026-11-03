
<?php

require_once __DIR__ . "/database.php";

echo "=== REGISTRAR PRODUCTO ===\n";


echo "Nombre del producto: ";
$nombre = trim(fgets(STDIN));

echo "Precio: ";
$precio = trim(fgets(STDIN));

echo "Cantidad inicial: ";
$stock = trim(fgets(STDIN));

echo "Stock minimo : ";
$stockMinimo = trim(fgets(STDIN));

echo "ID de categoria: ";
$categoriaId = trim(fgets(STDIN));

try{
	$sql  = "INSERT INTO productos
		(nombre, precio, stock, stock_minimo, categoria_id)
		VALUES (:nombre, :precio, :stock, :stock_minimo, :categoria_id)";

	$consulta = $db->prepare($sql);

	$consulta->execute([
		":nombre"=> $nombre,
		":precio"=> $precio,
		":stock"=> $stock,
		":stock_minimo"=>$stockMinimo,
		":categoria_id"=>$categoriaId
	]);
	echo "Producto registrado correctamente. \n";


    } catch (PDOException $e) {
	 die("Error al registar producto : " . $e->getMessage() . "\n" );
 }
